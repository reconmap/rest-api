using System.Text.Json;
using api_v2.Common.Extensions;
using api_v2.Domain.AuditActions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class TasksController(AppDbContext dbContext) : AppController(dbContext)
{
    [HttpPost]
    public async Task<IActionResult> Create(ProjectTask task)
    {
        task.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.Tasks.Add(task);

        AuditAction(AuditActions.Created, "Task", new { id = task.Id });
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetOne), new { id = task.Id }, task);
    }

    [HttpPut("{id:int}")]
    public async Task<IActionResult> UpdateOne(uint id, ProjectTask requestModel)
    {
        var dbModel = await dbContext.Tasks.FindAsync(id);
        if (dbModel == null) return NotFound();

        dbContext.Entry(dbModel).CurrentValues.SetValues(requestModel);
        dbContext.Entry(dbModel).Property(x => x.Id).IsModified = false;

        await dbContext.SaveChangesAsync();
        return Ok(dbModel);
    }

    [HttpGet]
    public async Task<IActionResult> GetMany([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.Tasks.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetOne(uint id)
    {
        var existing = await dbContext.Tasks
            .Include(t => t.CreatedBy)
            .Include(t => t.AssignedTo)
            .FirstOrDefaultAsync(t => t.Id == id);
        if (existing == null) return NotFound();

        return Ok(existing);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteOne(int id)
    {
        var deleted = await dbContext.Tasks
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }

    [HttpPatch]
    public async Task<IActionResult> PatchMany(
        [FromHeader(Name = "Bulk-Operation")] string operation,
        [FromBody] JsonElement body)
    {
        var ids = body.GetProperty("ids")
            .EnumerateArray()
            .Select(e => e.GetUInt32())
            .ToList();

        switch (operation)
        {
            case "UPDATE":
                var status = body.GetProperty("newStatus").GetString();

                await dbContext.Tasks
                    .Where(t => ids.Contains(t.Id))
                    .ExecuteUpdateAsync(u => u.SetProperty(
                        t => t.Status,
                        t => status
                    ));

                break;

            case "DELETE":
                await dbContext.Tasks
                    .Where(t => ids.Contains(t.Id))
                    .ExecuteDeleteAsync();

                break;

            default:
                return BadRequest(new { message = "Unknown bulk operation." });
        }

        return Accepted();
    }
}
