using System.Text.Json;
using api_v2.Common;
using api_v2.Common.Extensions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using StackExchange.Redis;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class ProjectsController(AppDbContext dbContext, IConnectionMultiplexer redis) : ControllerBase
{
    [HttpPost]
    public async Task<IActionResult> Create([FromBody] Project project)
    {
        if (!ModelState.IsValid)
            return BadRequest(ModelState);

        project.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.Projects.Add(project);
        await dbContext.SaveChangesAsync();

        var projectUser = new ProjectMember { ProjectId = project.Id, UserId = project.CreatedByUid };
        dbContext.ProjectMembers.Add(projectUser);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetOne), new { id = project.Id }, project);
    }

    [HttpPut("{id:int}")]
    public async Task<IActionResult> UpdateOne(uint id, Project project)
    {
        var dbModel = await dbContext.Projects.FindAsync(id);
        if (dbModel == null) return NotFound();

        dbContext.Entry(dbModel).CurrentValues.SetValues(project);
        dbContext.Entry(dbModel).Property(x => x.Id).IsModified = false;
        await dbContext.SaveChangesAsync();
        return Ok(dbModel);
    }

    [HttpPatch("{id:int}")]
    public async Task<IActionResult> PatchOne(uint id, [FromBody] JsonElement json)
    {
        await dbContext.Projects
            .Where(n => n.Id == id)
            .ExecuteUpdateAsync(upd => upd
                .SetProperty(n => n.Archived, json.GetProperty("archived").GetBoolean()));

        return NoContent();
    }

    [HttpGet]
    public async Task<IActionResult> GetMany(
        [FromQuery] string? status,
        [FromQuery] string? keywords,
        [FromQuery] uint? clientId)
    {
        if (!string.IsNullOrEmpty(keywords))
        {
            var setName = $"recent-searches-user${HttpContext.GetCurrentUser()!.Id}";
            var db = redis.GetDatabase();
            db.SortedSetAdd(setName, keywords, new DateTimeOffset(DateTime.UtcNow).ToUnixTimeSeconds());
        }

        var q = dbContext.Projects
            .Include(p => p.Client)
            .Include(p => p.Category)
            .AsNoTracking();
        if (clientId != null)
            q = q.Where(p => p.ClientId == clientId);
        if (!string.IsNullOrEmpty(status))
            q = q.Where(p => p.Archived == (status == "archived"));
        q = q.OrderByDescending(a => a.CreatedAt);

        var totalCount = await q.CountAsync();

        var pagination = new PaginationRequestHandler(HttpContext.Request.Query, totalCount);
        var resultsPerPage = pagination.GetResultsPerPage();
        var pageCount = pagination.CalculatePageCount();

        var results = await q
            .Skip(pagination.CalculateOffset())
            .Take(resultsPerPage)
            .ToListAsync();

        return Ok(new
        {
            pageCount,
            totalCount,
            data = results
        });
    }

    [HttpGet("categories")]
    public async Task<IActionResult> GetProjectCategories()
    {
        var q = dbContext.ProjectCategories.AsNoTracking()
            .OrderBy(a => a.Name);

        var page = await q.ToListAsync();
        return Ok(page);
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetOne(uint id)
    {
        var secret = await dbContext.Projects
            .Include(p => p.CreatedBy)
            .Include(p => p.Category)
            .Include(p => p.Client)
            .Where(p => p.Id == id)
            .FirstOrDefaultAsync();
        if (secret == null) return NotFound();

        return Ok(secret);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteOne(int id)
    {
        var deleted = await dbContext.Projects
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }

    [HttpGet("{projectId:int}/secrets")]
    public async Task<IActionResult> GetSecrets(int projectId)
    {
        var secrets = await dbContext.Secrets
            .AsNoTracking()
            .Where(s => s.ProjectId == projectId)
            .ToListAsync();

        return Ok(secrets);
    }
}
