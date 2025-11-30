using api_v2.Common.Extensions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class CommandsController(AppDbContext dbContext) : ControllerBase
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
