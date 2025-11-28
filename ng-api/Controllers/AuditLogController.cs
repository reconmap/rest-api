using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

public class AuditLogDailySummary
{
    public DateTime LogDate { get; set; }
    public int Total { get; set; }
}

[Route("api/[controller]")]
[ApiController]
public class AuditLogController(AppDbContext dbContext) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> GetAll([FromQuery(Name = "page")] int p = 0)
    {
        const int maxLimit = 20;
        var start = maxLimit * p;

        var total = await dbContext.AuditEntries.CountAsync();
        var totalPages = total == 0 ? 0 : Math.Ceiling((decimal)(total / maxLimit));

        var q = dbContext.AuditEntries.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Skip(start).Take(maxLimit).ToListAsync();

        Response.Headers.AccessControlExposeHeaders = "X-Page-Count";
        Response.Headers["X-Page-Count"] = totalPages.ToString();

        return Ok(page);
    }

    [HttpGet]
    [Route("stats")]
    public async Task<IActionResult> GetStats()
    {
        var results = await dbContext.AuditEntries
            .AsNoTracking()
            .GroupBy(x => x.CreatedAt.Value.Date)
            .Select(g => new AuditLogDailySummary
            {
                LogDate = g.Key,
                Total = g.Count()
            })
            .OrderBy(x => x.LogDate)
            .ToListAsync();

        return Ok(results);
    }
}