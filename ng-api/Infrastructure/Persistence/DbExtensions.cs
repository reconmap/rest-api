using Microsoft.EntityFrameworkCore;

namespace api_v2.Infrastructure.Persistence;

public static class DbExtensions
{
    public static IServiceCollection AddDatabase(this IServiceCollection services, IConfiguration config)
    {
        var cs = config.GetConnectionString("MySqlConnection");
        services.AddDbContext<AppDbContext>(options =>
            options.UseMySql(cs, ServerVersion.AutoDetect(cs)));
        return services;
    }
}