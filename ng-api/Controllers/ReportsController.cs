using api_v2.Common.Extensions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Scriban;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class ReportsController(AppDbContext dbContext, ILogger<ReportsController> logger)
    : ControllerBase
{
    private readonly ILogger _logger = logger;

    [HttpPost]
    public async Task<IActionResult> Create(Report report)
    {
        report.CreatedByUid = HttpContext.GetCurrentUser()!.Id;
        dbContext.Reports.Add(report);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetAll), new { id = report.Id }, report);
    }

    [HttpGet]
    public async Task<IActionResult> GetAll([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.Reports.AsNoTracking()
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpGet("{id:int}/preview")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> PreviewReport(uint id)
    {
        var existing = await dbContext.Projects.FindAsync(id);
        if (existing == null) return NotFound();

        var client = await dbContext.Organisations.FindAsync(existing.ClientId);

        var fileName = "data/attachments/default-report-template.html";
        var data = await System.IO.File.ReadAllTextAsync(fileName);

        var tpl = Template.Parse(data);
        var res = await tpl.RenderAsync(new { project = existing, client });

        var result = new ContentResult
        {
            Content = res,
            ContentType = "text/html; charset=utf-8"
        };
        return result;
    }


    [HttpGet("templates")]
    public async Task<IActionResult> GetTemplates([FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var result = from r in dbContext.Reports
            join a in dbContext.Attachments
                on new { r.Id, Type = "report" }
                equals new { Id = a.ParentId, Type = a.ParentType }
            where r.IsTemplate
            orderby r.CreatedAt descending
            select new
            {
                // report fields
                r.Id,
                r.ProjectId,
                r.CreatedByUid,
                r.CreatedAt,
                r.IsTemplate,
                r.VersionName,
                r.VersionDescription,

                // attachment fields
                AttachmentId = a.Id,
                AttachmentInsertTs = a.CreatedAt,
                a.ClientFileName,
                a.FileName,
                a.FileSize,
                a.FileMimeType
            };

        var page = await result.Take(take).ToListAsync();
        return Ok(page);
    }
}
