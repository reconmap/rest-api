using api_v2.Common.Extensions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/commands/{commandId:int}/usages")]
[ApiController]
public class CommandUsagesController(AppDbContext dbContext) : ControllerBase
{
    [HttpPost]
    public async Task<IActionResult> Create([FromBody] CommandUsage usage)
    {
        if (!ModelState.IsValid)
            return BadRequest(ModelState);

        usage.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.CommandUsages.Add(usage);
        await dbContext.SaveChangesAsync();

        return Created();
    }

    [HttpGet]
    public async Task<IActionResult> GetMany(int commandId, [FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.CommandUsages.AsNoTracking()
            .Where(u => u.CommandId == commandId)
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Delete(int commandId, int id)
    {
        var deleted = await dbContext.CommandUsages
            .Where(n => n.CommandId == commandId && n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }
}
