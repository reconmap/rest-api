using api_v2.Domain.AuditActions;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/vulnerabilities/categories")]
[ApiController]
public class VulnerabilitiesCategoriesController(
    AppDbContext dbContext,
    ILogger<VulnerabilitiesCategoriesController> logger)
    : AppController(dbContext)
{
    private readonly ILogger _logger = logger;

    [HttpGet]
    public async Task<IActionResult> GetAll([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.VulnerabilityCategories.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Delete(int id)
    {
        var deleted = await dbContext.VulnerabilityCategories
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        AuditAction(AuditActions.Deleted, "Vulnerability Category", new { id });

        return NoContent();
    }
}
