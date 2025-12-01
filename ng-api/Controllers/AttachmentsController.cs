using System.Security.Cryptography;
using api_v2.Common.Extensions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class AttachmentsController(AppDbContext dbContext, ILogger<AttachmentsController> logger)
    : ControllerBase
{
    private readonly ILogger _logger = logger;

    [HttpGet]
    public async Task<IActionResult> GetAll([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.Attachments.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> Get(uint id)
    {
        var existing = await dbContext.Attachments.FindAsync(id);
        if (existing == null) return NotFound();

        var pathToSave = Path.Combine(Directory.GetParent(Directory.GetCurrentDirectory())?.FullName, "data",
            "attachments",
            existing.FileName);

        var stream = System.IO.File.OpenRead(pathToSave);

        Response.Headers.AccessControlExposeHeaders = "Content-Disposition";

        return File(
            stream,
            existing.FileMimeType,
            existing.ClientFileName,
            true // allows efficient large-file downloads
        );
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteAttachment(uint id)
    {
        var attachment = await dbContext.Attachments.FindAsync(id);
        if (attachment == null) return NotFound();

        var path = Path.Combine(Directory.GetParent(Directory.GetCurrentDirectory())?.Name, "data", "attachments",
            attachment.FileName);
        System.IO.File.Delete(path);

        dbContext.Attachments.Remove(attachment);
        await dbContext.SaveChangesAsync();

        return NoContent();
    }

    [HttpPost]
    [DisableRequestSizeLimit]
    [RequestFormLimits(MultipartBodyLengthLimit = int.MaxValue, ValueLengthLimit = int.MaxValue)]
    public async Task<IActionResult> CreateAttachments([FromForm] uint parentId, [FromForm] string parentType)
    {
        if (!Request.Form.Files.Any())
            return BadRequest();

        foreach (var file in Request.Form.Files)
        {
            var pathToSave = Path.Combine(Directory.GetCurrentDirectory(), "data", "attachments");
            if (!Directory.Exists(pathToSave))
                Directory.CreateDirectory(pathToSave);

            var uniqueName = Guid.NewGuid().ToString("N") + Path.GetExtension(file.FileName);
            var fullPath = Path.Combine(pathToSave, uniqueName);
            await using FileStream stream = new(fullPath, FileMode.Create);
            await file.CopyToAsync(stream);

            var attachment = new Attachment
            {
                CreatedByUid = HttpContext.GetCurrentUser()!.Id,
                ParentType = parentType,
                ParentId = parentId,
                ClientFileName = file.FileName,
                FileName = Path.GetFileName(fullPath),
                FileSize = (uint)file.Length,
                FileMimeType = file.ContentType
            };
            using (var md5 = MD5.Create())
            {
                await using (var stream2 = System.IO.File.OpenRead(fullPath))
                {
                    var hash = await md5.ComputeHashAsync(stream2);
                    attachment.FileHash = Convert.ToHexStringLower(hash);
                }
            }

            await dbContext.Attachments.AddAsync(attachment);
        }

        await dbContext.SaveChangesAsync();

        return Ok();
    }
}
