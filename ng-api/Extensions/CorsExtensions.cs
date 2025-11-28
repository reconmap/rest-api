using Microsoft.Extensions.DependencyInjection;

namespace api_v2.Extensions;

public static class CorsExtensions
{
    public static IServiceCollection AddCustomCors(this IServiceCollection services)
    {
        services.AddCors(p =>
            p.AddPolicy("corsapp", b =>
            {
                b.WithOrigins("http://localhost:5500")
                    .AllowAnyMethod()
                    .AllowAnyHeader()
                    .AllowCredentials();
            }));

        return services;
    }
}