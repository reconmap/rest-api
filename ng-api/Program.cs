using System.Text.Json;
using System.Text.Json.Serialization;
using api_v2.Application.Services;
using api_v2.Extensions;
using api_v2.Infrastructure.Authentication;
using api_v2.Infrastructure.Persistence;
using api_v2.Infrastructure.Redis;
using api_v2.Infrastructure.WebSockets;
using Microsoft.AspNetCore.Authentication;

var builder = WebApplication.CreateBuilder(args);

var services = builder.Services;
builder.Logging.ClearProviders().AddConsole();
services.AddSingleton<IHttpContextAccessor, HttpContextAccessor>();
services.AddTransient<IClaimsTransformation, RoleClaimsTransformation>();
services.AddSingleton<WebSocketConnectionManager>();
services.AddScoped<CurrentDbUser>();
services.AddScoped<ICurrentDbUser>(sp => sp.GetRequiredService<CurrentDbUser>());
services.AddScoped<SystemUsageService>();

services.AddRedisServices(builder.Configuration);
services.AddJwtAuthentication(builder.Configuration);
services.AddDatabase(builder.Configuration);
services.AddSwaggerDocumentation();
services.AddCorsPolicies(builder.Configuration);
services.AddRouting(options => options.LowercaseUrls = true);

services.AddControllers()
    .AddJsonOptions(opt =>
    {
        opt.JsonSerializerOptions.Converters.Add(
            new JsonStringEnumConverter(JsonNamingPolicy.CamelCase)
        );
    });

services.AddAuthorization();

var app = builder.Build();

/*
var redis = builder.Services.BuildServiceProvider()
    .GetRequiredService<IConnectionMultiplexer>();
builder.Services.AddDataProtection()
    .PersistKeysToStackExchangeRedis(
        redis,
        "DataProtection-Keys");
        */
app.UseDefaultFiles();
app.UseStaticFiles();
app.UseRouting();
app.UseCors(CorsExtensions.CustomCorsPolicy);

if (app.Environment.IsDevelopment())
{
    app.MapOpenApi();
    app.UseSwaggerUI(options => { options.SwaggerEndpoint("/openapi/v1.json", "v1"); });
}

app.UseAuthentication();
app.UseAuthorization();
app.UseMiddleware<DbUserResolverMiddleware>();
app.UseCustomWebSockets();
//app.UseHttpsRedirection();

app.MapControllers();

app.Run();
