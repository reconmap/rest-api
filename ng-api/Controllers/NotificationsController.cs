using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class NotificationsController(AppDbContext dbContext) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> GetAll([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.Notifications.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Delete(int id)
    {
        var deleted = await dbContext.Notifications
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }
}
