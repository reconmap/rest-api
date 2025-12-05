using System.Net;
using System.Text.Json;
using api_v2.Common;
using api_v2.Common.Extensions;
using api_v2.Domain.AuditActions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class SecretsController(AppDbContext dbContext, ILogger<SecretsController> logger)
    : AppController(dbContext)
{
    private readonly ILogger _logger = logger;

    [HttpPost]
    public async Task<IActionResult> CreateOne(JsonElement json)
    {
        var encryptor = new DataEncryptor();
        var product = new Secret();
        product.OwnerUid = HttpContext.GetCurrentUser().Id;
        var (iv, tag, cypher) = encryptor.Encrypt(json.GetProperty("value").GetString(),
            json.GetProperty("password").GetString());
        product.Iv = iv;
        product.Name = json.GetProperty("name").GetString();
        product.Tag = tag;
        product.Value = cypher;
        product.Type = json.GetProperty("type").GetString();
        product.Note = json.GetProperty("note").GetString();
        product.ProjectId = json.GetProperty("projectId").GetUInt32();
        dbContext.Secrets.Add(product);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetSecret), new { id = product.Id }, product);
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetSecret(uint id)
    {
        var document = await dbContext.Secrets.FindAsync(id);
        if (document == null) return NotFound();

        return Ok(document);
    }

    [HttpGet]
    public async Task<IActionResult> GetMany([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.Secrets.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpPost("{id:int}/decrypt")]
    public async Task<IActionResult>? GetOne(uint id, JsonElement json)
    {
        var password = json.GetProperty("password").GetString();
        var secret = await dbContext.Secrets.FindAsync(id);
        var encryptor = new DataEncryptor();

        var value = encryptor.Decrypt(secret.Value, secret.Iv, password, secret.Tag);
        if (value == null) return Content(HttpStatusCode.Forbidden.ToString(), "wrong password");
        return Ok(new
        {
            secret.Name,
            secret.Note,
            secret.Type,
            value
        });
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteOne(int id)
    {
        var deleted = await dbContext.Secrets
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        AuditAction(AuditActions.Deleted, "Secret", new { id });

        return NoContent();
    }
}
