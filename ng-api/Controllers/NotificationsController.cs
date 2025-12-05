using System.Text.Json;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class NotificationsController(AppDbContext dbContext) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> GetMany([FromQuery] int? limit, [FromQuery] string? status)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.Notifications.AsNoTracking();
        if (status != null) q = q.Where(n => n.Status == status);
        q = q
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteOne(int id)
    {
        var deleted = await dbContext.Notifications
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }

    [HttpPatch("{id:int}")]
    public async Task<IActionResult> PatchOne(uint id, [FromBody] JsonElement body)
    {
        var dbModel = await dbContext.Notifications.FindAsync(id);
        if (dbModel == null) return NotFound();

        dbModel.Status = body.GetProperty("status").GetString();
        await dbContext.SaveChangesAsync();

        return NoContent();
    }

    [HttpPatch]
    public async Task<IActionResult> PatchMany([FromBody] JsonElement body)
    {
        var ids = body.GetProperty("ids")
            .EnumerateArray()
            .Select(e => e.GetUInt32())
            .ToList();

        var status = body.GetProperty("status").GetString();

        await dbContext.Notifications
            .Where(n => ids.Contains(n.Id))
            .ExecuteUpdateAsync(upd => upd
                .SetProperty(n => n.Status, status));

        return NoContent();
    }
}
