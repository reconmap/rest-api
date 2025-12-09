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
        //{http://schemas.xmlsoap.org/ws/2005/05/identity/claims/nameidentifier: fec17265-a0ae-4d5a-9e20-63487fc21b67
        var sub = context.User.FindFirst("http://schemas.xmlsoap.org/ws/2005/05/identity/claims/nameidentifier")?.Value;

        if (!string.IsNullOrEmpty(sub))
        {
            var user = await db.Users
                .AsNoTracking()
                .FirstOrDefaultAsync(u => u.SubjectId == sub);
            if (user == null) logger.LogWarning("No subject found in db for user {sub}", sub);
            current.User = user;
            context.Items["DbUser"] = current.User;
        }
        else
        {
            logger.LogWarning("Invalid subject provided {sub}", sub);
        }

        await next(context);
    }
}
