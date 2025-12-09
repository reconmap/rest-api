using System.Security.Claims;
using System.Text.Json;
using System.Text.Json.Serialization;
using Microsoft.AspNetCore.Authentication;

namespace api_v2.Infrastructure.Authentication;

public class RoleClaimsTransformation : IClaimsTransformation
{
    public Task<ClaimsPrincipal> TransformAsync(ClaimsPrincipal principal)
    {
        var identity = principal.Identity as ClaimsIdentity;
        var realmAccessClaim = identity?.FindFirst("resource_access");
        if (realmAccessClaim != null)
        {
            var options = new JsonSerializerOptions
                { PropertyNameCaseInsensitive = true }; // Ignore case when deserializing JSON

            // Deserialize the realm_access JSON to extract the roles
            var realmAccess = JsonSerializer.Deserialize<RealmAccess>(realmAccessClaim.Value, options);

            if (realmAccess?.WebClient?.Roles != null)
                foreach (var role in realmAccess.WebClient.Roles)
                    // Add each role as a Claim of type ClaimTypes.Role
                    identity.AddClaim(new Claim(ClaimTypes.Role, role));
        }

        return Task.FromResult(principal);
    }

    public class Foo
    {
        public List<string>? Roles { get; set; }
    }

    public class RealmAccess
    {
        [JsonPropertyName("web-client")] public Foo WebClient { get; set; }
    } // one user can be assigned multiple roles
}
