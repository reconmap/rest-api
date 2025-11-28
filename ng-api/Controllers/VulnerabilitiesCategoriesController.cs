using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/vulnerabilities/categories")]
[ApiController]
public class VulnerabilitiesCategoriesController(
    AppDbContext dbContext,
    ILogger<VulnerabilitiesCategoriesController> logger)
    : ControllerBase
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
}