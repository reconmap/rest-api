using System.Security.Cryptography;
using System.Text.Json;
using api_v2.Common.Extensions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using StackExchange.Redis;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class CommandsController(AppDbContext dbContext, IConnectionMultiplexer conn, IConfiguration config)
    : ControllerBase
{
    [HttpPost]
    public async Task<IActionResult> CreateCommand([FromBody] Command command)
    {
        if (!ModelState.IsValid)
            return BadRequest(ModelState);

        command.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.Commands.Add(command);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetCommand), new { id = command.Id }, command);
    }

    [HttpGet]
    public async Task<IActionResult> GetCommands([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.Commands.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpGet("{id}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetCommand(int id)
    {
        var command = await dbContext.Commands.Include(c => c.CreatedBy).FirstOrDefaultAsync(c => c.Id == id);
        if (command == null) return NotFound();

        return Ok(command);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteCommand(int id)
    {
        var deleted = await dbContext.Commands
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }

    [HttpGet("schedules")]
    public async Task<IActionResult> GetCommandSchedules([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.CommandSchedules.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpPost("{commandId:int}/schedules")]
    public async Task<IActionResult> CreateCommandSchedules(uint commandId, [FromBody] CommandSchedule command)
    {
        if (!ModelState.IsValid)
            return BadRequest(ModelState);

        command.CommandId = commandId;
        command.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.CommandSchedules.Add(command);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetCommand), new { id = command.Id }, command);
    }

    [HttpPost("outputs")]
    public async Task<IActionResult> UploadOutput()
    {
        // Parsed body
        var form = await Request.ReadFormAsync();
        var commandUsageId = int.Parse(form["commandUsageId"]);

        // Uploaded file
        var resultFile = form.Files["resultFile"];

        // Data lookups
        var usage = await dbContext.CommandUsages.FindAsync((uint)commandUsageId);
        var command = await dbContext.Commands.FindAsync((int)usage.CommandId);

        // User Id from request context
        var userId = (int)HttpContext.GetCurrentUser()!.Id;

        ///////////////
        var relativePath = config["AttachmentSettings:SavePath"];
        var pathToSave = Path.Combine(relativePath, "data", "attachments");
        if (!Directory.Exists(pathToSave))
            Directory.CreateDirectory(pathToSave);

        var uniqueName = Guid.NewGuid().ToString("N") + Path.GetExtension(resultFile.FileName);
        var fullPath = Path.Combine(pathToSave, uniqueName);
        await using FileStream stream = new(fullPath, FileMode.Create);
        await resultFile.CopyToAsync(stream);

        var attachment = new Attachment
        {
            CreatedByUid = HttpContext.GetCurrentUser()!.Id,
            ParentType = "command",
            ParentId = (uint)command.Id,
            ClientFileName = resultFile.FileName,
            FileName = Path.GetFileName(fullPath),
            FileSize = (uint)resultFile.Length,
            FileMimeType = resultFile.ContentType
        };
        using (var md5 = MD5.Create())
        {
            await using (var stream2 = System.IO.File.OpenRead(fullPath))
            {
                var hash = await md5.ComputeHashAsync(stream2);
                attachment.FileHash = Convert.ToHexStringLower(hash);
            }
        }

        await dbContext.Attachments.AddAsync(attachment);
        await dbContext.SaveChangesAsync();
        /// /////////////

        // Optional project ID
        int? projectId = null;
        if (form.TryGetValue("projectId", out var projectIdValue) &&
            int.TryParse(projectIdValue, out var parsedProjectId))
            projectId = parsedProjectId;

        if (projectId.HasValue)
        {
            var payload = new
            {
                commandUsageId,
                projectId = projectId.Value,
                userId,
                filePath = uniqueName
            };

            var pushed = await conn.GetDatabase().ListLeftPushAsync(
                "tasks:queue",
                JsonSerializer.Serialize(payload)
            );
        }

        return new JsonResult(new { success = true });
    }

    [HttpGet("{commandId:int}/schedules")]
    public async Task<IActionResult> GetSchedules([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.CommandSchedules.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpDelete("{commandId:int}/schedules/{id:int}")]
    public async Task<IActionResult> DeleteSchedule(int commandId, int id)
    {
        var deleted = await dbContext.CommandSchedules
            .Where(n => n.CommandId == commandId && n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }
}
