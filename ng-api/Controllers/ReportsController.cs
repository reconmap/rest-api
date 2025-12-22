using System.Security.Cryptography;
using api_v2.Application.Services;
using api_v2.Common.Extensions;
using api_v2.Domain.AuditActions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using DocumentFormat.OpenXml.Packaging;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Scriban;

namespace api_v2.Controllers;

public class ReportRequestDto
{
    public uint ProjectId { get; set; }
    public uint ReportTemplateId { get; set; }
    public string VersionName { get; set; }
    public string VersionDescription { get; set; }
}

[Route("api/[controller]")]
[ApiController]
public class ReportsController(AppDbContext dbContext, ILogger<ReportsController> logger)
    : AppController(dbContext)
{
    private readonly AttachmentFilePath _attachmentFilePath = new();
    private readonly ILogger _logger = logger;

    [HttpPost]
    public async Task<IActionResult> Create(ReportRequestDto reportRequest)
    {
        var project = await dbContext.Projects.FindAsync(reportRequest.ProjectId);
        if (project == null) return NotFound();

        var templateAttachment = await dbContext.Attachments
            .Where(a => a.ParentId == reportRequest.ReportTemplateId && a.ParentType == "report")
            .FirstOrDefaultAsync();

        var templateExtension = Path.GetExtension(templateAttachment.FileName);
        var templateFilePath = _attachmentFilePath.GenerateFilePath(templateAttachment.FileName);

        var report = new Report
        {
            CreatedByUid = HttpContext.GetCurrentUser()!.Id,
            ProjectId = reportRequest.ProjectId,
            VersionName = reportRequest.VersionName,
            VersionDescription = reportRequest.VersionDescription,
            IsTemplate = false
        };
        dbContext.Reports.Add(report);

        await dbContext.SaveChangesAsync();


        var clientFileName = $"reconmap-{project.Name}-v{report.VersionName}" + templateExtension;

        var reportFileName = _attachmentFilePath.GenerateFileName(templateExtension);
        var reportFilePath = _attachmentFilePath.GenerateFilePath(reportFileName);


        if (templateExtension == ".docx")
        {
            using var wordDocument =
                WordprocessingDocument.Open(templateFilePath, false);
            // Assign a reference to the existing document body.
            var body = wordDocument.MainDocumentPart.Document.Body;
            logger.LogInformation(body.InnerText);
            wordDocument.Clone(reportFilePath);
        }
        else
        {
            var templateContent = await System.IO.File.ReadAllTextAsync(templateFilePath);

            var template = Template.Parse(templateContent);
            if (template.HasErrors)
            {
                foreach (var error in template.Messages)
                    logger.LogWarning("{Message} {Start} {End}", error.Message, error.Span.Start, error.Span.End);

                return BadRequest();
            }

            var renderedTemplate = await template.RenderAsync(new { project });
            await System.IO.File.WriteAllTextAsync(reportFilePath, renderedTemplate);
        }

        var attachment = new Attachment();
        attachment.ParentType = "report";
        attachment.ParentId = report.Id;
        attachment.CreatedByUid = report.CreatedByUid;
        attachment.FileName = reportFileName;
        attachment.FileMimeType = templateAttachment.FileMimeType;
        attachment.ClientFileName = clientFileName;

        using (var md5 = MD5.Create())
        {
            await using (var stream2 = System.IO.File.OpenRead(reportFilePath))
            {
                var hash = await md5.ComputeHashAsync(stream2);
                attachment.FileHash = Convert.ToHexStringLower(hash);
            }
        }

        attachment.FileSize = (uint)new FileInfo(reportFilePath).Length;

        dbContext.Attachments.Add(attachment);
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(GetMany), new { id = report.Id }, report);
    }

    [HttpGet]
    public async Task<IActionResult> GetMany([FromQuery] int? limit)
    {
        var results = await GetReportsInternal(limit, false);
        return Ok(results);
    }

    [HttpGet("{id:int}/preview")]
    [AllowAnonymous]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> PreviewReport(uint id)
    {
        var existing = await dbContext.Projects.FindAsync(id);
        if (existing == null) return NotFound();

        var client = await dbContext.Organisations.FindAsync(existing.ClientId);

        var fileName = "../data/attachments/default-report-template.html";
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
        var results = await GetReportsInternal(limit, true);
        return Ok(results);
    }

    private async Task<List<object>> GetReportsInternal(int? limit, bool isTemplate)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var query =
            from r in dbContext.Reports
            join a in dbContext.Attachments
                on new { r.Id, Type = "report" }
                equals new { Id = a.ParentId, Type = a.ParentType }
            where r.IsTemplate == isTemplate
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

        return await query
            .Take(take)
            .ToListAsync<object>();
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> DeleteOne(uint id)
    {
        var deleted = await dbContext.Reports
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        AuditAction(AuditActions.Deleted, "Report", new { id });

        return NoContent();
    }
}
