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

        return CreatedAtAction(nameof(Get), new { id = task.Id }, task);
    }

    [HttpGet]
    public async Task<IActionResult> GetAll([FromQuery] int? limit)
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
    public async Task<IActionResult> Get(uint id)
    {
        var existing = await dbContext.Tasks.FindAsync(id);
        if (existing == null) return NotFound();

        return Ok(existing);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteTask(int id)
    {
        var deleted = await dbContext.Tasks
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }
}