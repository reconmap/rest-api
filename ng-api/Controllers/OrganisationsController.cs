using api_v2.Common.Extensions;
using api_v2.Domain.AuditActions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class OrganisationsController(AppDbContext dbContext, ILogger<OrganisationsController> logger)
    : AppController(dbContext)
{
    private readonly ILogger _logger = logger;

    [HttpPost]
    public async Task<IActionResult> Create([FromForm] Organisation entity)
    {
        entity.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.Organisations.Add(entity);

        AuditAction(AuditActions.Created, "Organisation", new { id = entity.Id });
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetOne), new { id = entity.Id }, entity);
    }

    [HttpGet]
    public async Task<IActionResult> GetAll([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.Organisations.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetOne(uint id)
    {
        var existing = await dbContext.Organisations
            .Include(o => o.CreatedBy)
            .FirstOrDefaultAsync(o => o.Id == id);
        if (existing == null) return NotFound();

        return Ok(existing);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteOrganisation(int id)
    {
        var deleted = await dbContext.Organisations
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }

    [HttpPost("{id:int}/contacts")]
    public async Task<IActionResult> CreateContact(Contact entity)
    {
        dbContext.Contacts.Add(entity);

        AuditAction(AuditActions.Created, "Contact", new { id = entity.Id });
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetOne), new { id = entity.Id }, entity);
    }

    [HttpGet("{id:int}/contacts")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetContacts(uint id)
    {
        var q = dbContext.Contacts.AsNoTracking()
            .OrderByDescending(a => a.Name);

        var page = await q.Take(100).ToListAsync();
        return Ok(page);
    }

    [HttpDelete("{organisationId:int}/contacts/{id:int}")]
    public async Task<IActionResult> DeleteContact(int id)
    {
        var deleted = await dbContext.Contacts
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }
}
