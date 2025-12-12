using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class AgentsController(AppDbContext dbContext, ILogger<AgentsController> logger)
    : ControllerBase
{
    private readonly ILogger _logger = logger;

    [HttpGet]
    public async Task<IActionResult> GetMany([FromQuery] int? limit)
    {
        var q = dbContext.Agents.AsNoTracking()
            .OrderByDescending(a => a.LastPingAt);

        var agents = await q.ToListAsync();
        return Ok(agents);
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> GetOne(uint id)
    {
        var agent = await dbContext.Agents.FindAsync(id);
        if (agent == null) return NotFound();

        return Ok(agent);
    }

    [HttpPatch("{id:int}/ping")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> PatchAgent(uint id)
    {
        var existing = await dbContext.Agents.FindAsync(id);
        if (existing == null) return NotFound();

        existing.LastPingAt = DateTime.UtcNow;
        dbContext.Update(existing);
        await dbContext.SaveChangesAsync();

        return Accepted();
    }

    [HttpPatch("{id:int}/boot")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> BootAgent(uint id, [FromBody] Dictionary<string, object> body)
    {
        var existing = await dbContext.Agents.FindAsync(id);
        if (existing == null) return NotFound();

        existing.LastBootAt = DateTime.UtcNow;
        existing.LastPingAt = DateTime.UtcNow;
        existing.Version = body["version"]?.ToString();
        existing.Hostname = body["hostname"]?.ToString();
        existing.Arch = body["arch"]?.ToString();
        existing.Cpu = body["cpu"]?.ToString();
        existing.Memory = body["memory"]?.ToString();
        existing.Os = body["os"]?.ToString();
        existing.Ip = body["ip"]?.ToString();
        existing.ListenAddr = body["listen_addr"]?.ToString();

        dbContext.Update(existing);
        await dbContext.SaveChangesAsync();

        return Accepted();
    }
}
