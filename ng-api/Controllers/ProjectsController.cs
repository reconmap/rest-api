using System.Text.Json;
using api_v2.Common.Extensions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class ProjectsController(AppDbContext dbContext) : ControllerBase
{
    [HttpPost]
    public async Task<IActionResult> CreateCommand([FromBody] Project command)
    {
        if (!ModelState.IsValid)
            return BadRequest(ModelState);

        command.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.Projects.Add(command);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetProject), new { id = command.Id }, command);
    }

    [HttpGet]
    public async Task<IActionResult> GetProjects([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

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
    public async Task<IActionResult> GetProject(uint id)
    {
        var existing = await dbContext.Projects.FindAsync(id);
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
    public async Task<IActionResult> AddProjectMember(uint projectId,  [FromBody] JsonElement body)
    {
        if (!body.TryGetProperty("userId", out var value))
            return BadRequest("Missing userId");

        uint userId;

        try {
            userId = value.GetUInt32();
        } catch {
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
                    Id = pu.Id,
                    UserId = u.Id,
                    FullName = u.FullName,
                    Email = u.Email,
                    Role = u.Role
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
