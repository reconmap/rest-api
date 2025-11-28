using api_v2.Common.Extensions;
using api_v2.Domain.AuditActions;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using FS.Keycloak.RestApiClient.Api;
using FS.Keycloak.RestApiClient.Authentication.ClientFactory;
using FS.Keycloak.RestApiClient.Authentication.Flow;
using FS.Keycloak.RestApiClient.ClientFactory;
using FS.Keycloak.RestApiClient.Model;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Controllers;

[Route("api/[controller]")]
[ApiController]
public class UsersController(AppDbContext dbContext) : AppController(dbContext)
{
    [HttpPost]
    public async Task<IActionResult> Create(User user)
    {
        var creds = new ClientCredentialsFlow()
        {
            ClientId = "api-client",
            ClientSecret = "I0jppD5zSIXuBAql31zrXfe5OAa0nvyE",
            KeycloakUrl = "http://localhost:8080",
            Realm = "reconmap",
        };

        using var httpClient = AuthenticationHttpClientFactory.Create(creds);
        using var usersApi = ApiClientFactory.Create<UsersApi>(httpClient);
        await usersApi.PostUsersAsync("reconmap", new UserRepresentation()
        {
            FirstName = user.FirstName,
            LastName = user.LastName,
            Email = user.Email,
            Enabled = true,
            Username = user.Username,
            Credentials =
            [
                new()
                {
                    Type = "password",
                    Temporary = false,
                    Value = "admin123"
                }
            ],
            Groups = ["administrator-group"]
        });

        var realm = "reconmap";
        var createdUser = await usersApi
            .GetUsersAsync(realm, username: user.Username, exact: true);

        user.SubjectId = createdUser[0].Id;

        dbContext.Users.Add(user);

        AuditAction(AuditActions.Created, "User", new { id = user.Id });
        await dbContext.SaveChangesAsync();

        return CreatedAtAction(nameof(Get), new { id = user.Id }, user);
    }

    [HttpGet]
    public async Task<IActionResult> GetAll()
    {
        return Ok(await dbContext.Users.ToListAsync());
    }

    [HttpGet("{id:int}")]
    [ProducesResponseType(StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> Get(uint id)
    {
        var existing = await dbContext.Users.FindAsync(id);
        if (existing == null) return NotFound();

        return Ok(existing);
    }

    [HttpGet("{id:int}/activity")]
    public async Task<IActionResult> GetAll(uint id, [FromQuery] int? limit)
    {
        const int maxLimit = 500;
        var take = Math.Min(limit ?? 100, maxLimit);

        var q = dbContext.AuditEntries.AsNoTracking()
            .Where(e => e.CreatedByUid == id)
            .OrderByDescending(a => a.CreatedAt);

        var page = await q.Take(take).ToListAsync();
        return Ok(page);
    }

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Delete(int id)
    {
        var deleted = await dbContext.Users
            .Where(n => n.Id == id)
            .ExecuteDeleteAsync();

        if (deleted == 0) return NotFound();

        return NoContent();
    }
}