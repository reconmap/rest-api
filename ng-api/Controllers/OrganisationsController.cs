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

    [HttpPut("{id:int}")]
    public async Task<IActionResult> UpdateOne(uint id, Organisation requestModel)
    {
        var dbModel = await dbContext.Organisations.FindAsync(id);
        if (dbModel == null) return NotFound();

        dbContext.Entry(dbModel).State = EntityState.Modified;
        dbModel.Name = requestModel.Name;
        dbModel.Address = requestModel.Address;
        dbModel.Kind = requestModel.Kind;
        dbModel.Url = requestModel.Url;
        await dbContext.SaveChangesAsync();
        return Ok(dbModel);
    }

    [HttpGet]
    public async Task<IActionResult> GetMany([FromQuery] int? limit, [FromQuery] string? kind)
    {
        var q = dbContext.Organisations.AsNoTracking();
        if (kind != null)
            q = q.Where(n => n.Kind == kind);
        q = q.OrderByDescending(a => a.CreatedAt);

        var organisations = await q.ToListAsync();
        return Ok(organisations);
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
    public async Task<IActionResult> CreateContact(uint id, Contact entity)
    {
        entity.OrganisationId = id;

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
            .Where(n => n.OrganisationId == id)
            .OrderByDescending(a => a.Name);

        var contacts = await q.ToListAsync();

        return Ok(contacts);
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
