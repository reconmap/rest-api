using api_v2.Common.Extensions;
using api_v2.Domain.AuditActions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

public class RiskCountDto
{
    public string Risk { get; set; } = string.Empty;
    public int Total { get; set; }
}

[Route("api/[controller]")]
[ApiController]
public class VulnerabilitiesController(AppDbContext dbContext, ILogger<VulnerabilitiesController> logger)
    : AppController(dbContext)
{
    private readonly ILogger _logger = logger;

    [HttpPost]
    public async Task<IActionResult> CreateTask(Vulnerability vulnerability)
    {
        vulnerability.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.Vulnerabilities.Add(vulnerability);

        AuditAction(AuditActions.Created, "Vulnerability", new { id = vulnerability.Id });
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetOne), new { id = vulnerability.Id }, vulnerability);
    }

    [HttpPut("{id:int}")]
    public async Task<IActionResult> UpdateOne(uint id, Vulnerability vulnerability)
    {
        var dbModel = await dbContext.Vulnerabilities.FindAsync(id);
        if (dbModel == null) return NotFound();

        dbContext.Entry(dbModel).CurrentValues.SetValues(vulnerability);
        dbContext.Entry(dbModel).Property(x => x.Id).IsModified = false;
        await dbContext.SaveChangesAsync();
        return Ok(dbModel);
    }

    [HttpGet]
    public async Task<IActionResult> GetMany([FromQuery] int? projectId, [FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.Vulnerabilities.AsNoTracking();
        if (projectId.HasValue)
            q = q.Where(v => v.ProjectId == projectId);
        q = q.OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetOne(uint id)
    {
        var existing = await dbContext.Vulnerabilities
            .Include(v => v.Project)
            .Include(v => v.Category)
            .Include(v => v.CreatedBy)
            .Include(v => v.Asset)
            .Where(v => v.Id == id)
            .FirstOrDefaultAsync();
        if (existing == null) return NotFound();

        return Ok(existing);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteVulnerability(int id)
    {
        var deleted = await dbContext.Vulnerabilities
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }

    [HttpGet]
    [Route("stats")]
    public async Task<IActionResult> GetStats([FromQuery] int? projectId = null, [FromQuery] string? groupBy = null)
    {
        if (groupBy != null) return Ok(await FindCountByRiskAsync(projectId));

        return Ok(await FindCountByCategoryAsync(projectId));
    }

    private async Task<List<RiskCountDto>> FindCountByRiskAsync(int? projectId = null)
    {
        var query = dbContext.Vulnerabilities.AsQueryable();

        if (projectId.HasValue)
            query = query.Where(v => v.ProjectId == projectId.Value);

        return await query
            .GroupBy(v => v.Risk)
            .Select(g => new RiskCountDto
            {
                Risk = g.Key,
                Total = g.Count()
            })
            .OrderByDescending(x => x.Total)
            .ToListAsync();
    }

    private async Task<List<CategoryCountDto>> FindCountByCategoryAsync(int? projectId = null)
    {
        var query = dbContext.Vulnerabilities
            .Include(v => v.Category)
            .Where(v => v.Category.ParentId == null)
            .AsQueryable();

        if (projectId.HasValue)
            query = query.Where(v => v.ProjectId == projectId.Value);

        return await query
            .GroupBy(v => v.Category.Name)
            .Select(g => new CategoryCountDto
            {
                CategoryName = g.Key,
                Total = g.Count()
            })
            .OrderByDescending(x => x.Total)
            .ToListAsync();
    }
}

public class CategoryCountDto
{
    public string CategoryName { get; set; } = string.Empty;
    public int Total { get; set; }
}
