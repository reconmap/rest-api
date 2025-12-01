using System.Net;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using api_v2.Common.Extensions;
using api_v2.Domain.AuditActions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

public sealed class DataEncryptor
{
    private const int KeySizeBytes = 32; // 256-bit
    private const int IvSizeBytes = 12; // Recommended GCM nonce size
    private const int TagSizeBytes = 16; // 128-bit authentication tag

    public (byte[] Iv, byte[] Tag, byte[] CipherText) Encrypt(string plainText, string password)
    {
        var key = DeriveKey(password);
        var iv = RandomNumberGenerator.GetBytes(IvSizeBytes);

        var plainBytes = Encoding.UTF8.GetBytes(plainText);
        var cipherBytes = new byte[plainBytes.Length];
        var tag = new byte[TagSizeBytes];

        using var aes = new AesGcm(key);
        aes.Encrypt(iv, plainBytes, cipherBytes, tag);

        return (iv, tag, cipherBytes);
    }

    public string? Decrypt(byte[] cipherText, byte[] iv, string password, byte[] tag)
    {
        var key = DeriveKey(password);

        var plainBytes = new byte[cipherText.Length];

        try
        {
            using var aes = new AesGcm(key);
            aes.Decrypt(iv, cipherText, tag, plainBytes);
            return Encoding.UTF8.GetString(plainBytes);
        }
        catch (CryptographicException)
        {
            return null; // Authentication failed
        }
    }

    private static byte[] DeriveKey(string password)
    {
        // Equivalent to hash('sha256', $password, true)
        return SHA256.HashData(Encoding.UTF8.GetBytes(password));
    }
}

[Route("api/[controller]")]
[ApiController]
public class SecretsController(AppDbContext dbContext, ILogger<SecretsController> logger)
    : AppController(dbContext)
{
    private readonly ILogger _logger = logger;

    [HttpPost]
    public async Task<IActionResult> CreateSecret(JsonElement json)
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
    public async Task<IActionResult> DeleteSecret(int id)
    {
        var deleted = await dbContext.Secrets
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        AuditAction(AuditActions.Deleted, "Secret", new { id });

        return NoContent();
    }
}
