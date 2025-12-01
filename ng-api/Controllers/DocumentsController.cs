using api_v2.Common.Extensions;
using api_v2.Domain.AuditActions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class DocumentsController(AppDbContext dbContext)
    : AppController(dbContext)
{
    [HttpPost]
    public async Task<IActionResult> Create(Document product)
    {
        product.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.Documents.Add(product);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetOne), new { id = product.Id }, product);
    }

    [Authorize(Roles = "administrator")]
    [HttpGet]
    public async Task<IActionResult> GetMany()
    {
        return Ok(await dbContext.Documents.Include(d => d.CreatedBy).ToListAsync());
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetOne(uint id)
    {
        var document = await dbContext.Documents.Include(d => d.CreatedBy).FirstOrDefaultAsync(d => d.Id == id);
        if (document == null) return NotFound();

        return Ok(document);
    }

    [HttpPut("{id:int}")]
    public async Task<IActionResult> UpdateOne(int id, Document product)
    {
        var dbModel = await dbContext.Documents.FindAsync(id);
        if (dbModel == null) return NotFound();

        dbModel.Title = product.Title;
        dbModel.Content = product.Content;
        dbModel.Visibility = product.Visibility;
        await dbContext.SaveChangesAsync();
        return Ok(dbModel);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteDocument(int id)
    {
        var deleted = await dbContext.Documents
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        AuditAction(AuditActions.Deleted, "Document", new { id });

        return NoContent();
    }
}
