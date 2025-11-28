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
public class DocumentsController(AppDbContext dbContext, ILogger<DocumentsController> logger)
    : AppController(dbContext)
{
    private readonly ILogger _logger = logger;

    [Authorize(Roles = "administrator")]
    [HttpGet]
    public async Task<IActionResult> GetAll()
    {
        return Ok(await dbContext.Documents.Include(d=>d.CreatedBy).ToListAsync());
    }

    [HttpPost]
    public async Task<IActionResult> CreateDocument(Document product)
    {
        product.CreatedByUid = HttpContext.GetCurrentUser()!.Id; 
        dbContext.Documents.Add(product);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetDocument), new { id = product.Id }, product);
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetDocument(uint id)
    {
        var document = await dbContext.Documents.Include(d => d.CreatedBy).FirstOrDefaultAsync(d => d.Id == id);
        if (document == null) return NotFound();

        return Ok(document);
    }

    [HttpPut("{id:int}")]
    public async Task<IActionResult> UpdateDocument(int id, Document product)
    {
        var existing = await dbContext.Documents.FindAsync(id);
        if (existing == null) return NotFound();

        existing.Title = product.Title;
        await dbContext.SaveChangesAsync();
        return Ok(existing);
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