using System.Security.Claims;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using Microsoft.EntityFrameworkCore;

namespace api_v2.Infrastructure.Authentication;

public interface ICurrentDbUser
{
    User? User { get; }
}

public class CurrentDbUser : ICurrentDbUser
{
    public User? User { get; internal set; }
}

public class DbUserResolverMiddleware(RequestDelegate next, ILogger<DbUserResolverMiddleware> logger)
{
    public async Task InvokeAsync(
        HttpContext context,
        AppDbContext db,
        CurrentDbUser current)
    {
        var subjectId = context.User.FindFirstValue(ClaimTypes.NameIdentifier);

        if (!string.IsNullOrWhiteSpace(subjectId))
        {
            var user = await db.Users
                .AsNoTracking()
                .FirstOrDefaultAsync(u => u.SubjectId == subjectId);
            if (user == null) logger.LogWarning("No subject found in db for user {SubjectId}", subjectId);
            current.User = user;
            context.Items["DbUser"] = current.User;
        }
        else
        {
            logger.LogWarning("Invalid subject provided {SubjectId}", subjectId);
        }

        await next(context);
    }
}
