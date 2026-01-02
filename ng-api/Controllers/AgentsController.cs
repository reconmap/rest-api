using api_v2.Infrastructure.Persistence;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class AgentsController(AppDbContext dbContext, ILogger<AgentsController> logger)
    : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> GetMany()
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
        var agent = await dbContext.Agents.FindAsync(id);
        if (agent == null) return NotFound();

        agent.LastPingAt = DateTime.UtcNow;
        dbContext.Update(agent);
        await dbContext.SaveChangesAsync();

        return Accepted();
    }

    [HttpPatch("{id:int}/boot")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> BootAgent(uint id, [FromBody] Dictionary<string, object> body)
    {
        var agent = await dbContext.Agents.FindAsync(id);
        if (agent == null) return NotFound();

        agent.LastBootAt = DateTime.UtcNow;
        agent.LastPingAt = DateTime.UtcNow;
        agent.Version = body["version"]?.ToString();
        agent.Hostname = body["hostname"]?.ToString();
        agent.Arch = body["arch"]?.ToString();
        agent.Cpu = body["cpu"]?.ToString();
        agent.Memory = body["memory"]?.ToString();
        agent.Os = body["os"]?.ToString();
        agent.Ip = body["ip"]?.ToString();
        agent.ListenAddr = body["listen_addr"]?.ToString();

        dbContext.Update(agent);
        await dbContext.SaveChangesAsync();

        return Accepted();
    }
}
