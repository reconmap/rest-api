using System.Text.Json;
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

        var projectUser = new ProjectMember() { ProjectId = project.Id, UserId = project.CreatedByUid };
        dbContext.ProjectMembers.Add(projectUser);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetOne), new { id = project.Id }, project);
    }

    [HttpGet]
    public async Task<IActionResult> GetMany([FromQuery] int? limit, [FromQuery] string? keywords)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        if (!string.IsNullOrEmpty(keywords))
        {
            var setName = $"recent-searches-user${HttpContext.GetCurrentUser()!.Id}";
            var db = redis.GetDatabase();
            db.SortedSetAdd(setName, keywords, new DateTimeOffset(DateTime.UtcNow).ToUnixTimeSeconds());
        }

        var q = dbContext.Projects.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
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
        var existing = await dbContext.Projects.Include(p => p.CreatedBy).Where(p => p.Id == id)
            .FirstOrDefaultAsync();
        if (existing == null) return NotFound();

        return Ok(existing);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteProject(int id)
    {
        var deleted = await dbContext.Projects
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }

    [HttpPost("{projectId:int}/members")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> AddProjectMember(uint projectId, [FromBody] JsonElement body)
    {
        if (!body.TryGetProperty("userId", out var value))
            return BadRequest("Missing userId");

        uint userId;

        try
        {
            userId = value.GetUInt32();
        }
        catch
        {
            return BadRequest("Invalid userId");
        }

        var projectMember = new ProjectMember
        {
            ProjectId = projectId,
            UserId = userId
        };
        dbContext.ProjectMembers.Add(projectMember);
        await dbContext.SaveChangesAsync();

        return Accepted();
    }

    [HttpGet("{projectId:int}/members")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetProjectMembers(uint projectId)
    {
        var users = await dbContext.ProjectMembers
            .Where(pu => pu.ProjectId == projectId)
            .Join(
                dbContext.Users,
                pu => pu.UserId,
                u => u.Id,
                (pu, u) => new
                {
                    pu.Id,
                    UserId = u.Id,
                    u.FullName,
                    u.Email,
                    u.Role
                }
            )
            .ToListAsync();
        return Ok(users);
    }

    [HttpDelete("{projectId:int}/members/{id:int}")]
    public async Task<IActionResult> DeleteProjectMember(int id)
    {
        var deleted = await dbContext.ProjectMembers
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }
}
