using System.Text.Json;
using api_v2.Controllers;
using api_v2.Domain.Entities;
using api_v2.Infrastructure.Persistence;
using StackExchange.Redis;

namespace api_v2.Application.Services;

public class CommandResultProcessor(
    ILogger<CommandResultProcessor> logger,
    IServiceScopeFactory _scopeFactory) : BackgroundService
{
    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        using var scope = _scopeFactory.CreateScope();
        var redisConnection = scope.ServiceProvider.GetRequiredService<IConnectionMultiplexer>();
        var db = scope.ServiceProvider.GetRequiredService<AppDbContext>();
        var redis = redisConnection.GetDatabase();
        while (!stoppingToken.IsCancellationRequested)
        {
            await Task.Delay(500);
            var item = await redis.ListRightPopAsync("tasks:queue");
            if (item.HasValue)
            {
                logger.LogInformation("Task queue popped");
                var obj = JsonSerializer.Deserialize<CommandProcessorJob>(
                    item.ToString(),
                    new JsonSerializerOptions
                    {
                        PropertyNameCaseInsensitive = true
                    }
                );
                var commandUsage = await db.CommandUsages.FindAsync(obj.CommandUsageId);
                var processor = ProcessorIntegrationDiscovery.Create(commandUsage.OutputParser);
                var result = processor.Process(obj);
                var numHosts = result.assets.Count;
                if (numHosts > 0)
                {
                    foreach (var asset in result.assets)
                    {
                        asset.ProjectId = obj.ProjectId;
                        db.Assets.Add(asset);
                    }

                    try
                    {
                        await db.SaveChangesAsync(stoppingToken);
                    }
                    catch (Exception dbUpdateException)
                    {
                        logger.LogError(dbUpdateException, "An error occurred while processing command results");
                        db.ChangeTracker.Clear();
                    }

                    var notification = new Notification
                    {
                        ToUserId = obj.UserId,
                        Title = "New assets found",
                        Content =
                            $"A total of '{numHosts}' new assets have been found by the '{commandUsage.OutputParser}' command",
                        Status = "unread"
                    };
                    db.Notifications.Add(notification);
                    await db.SaveChangesAsync(stoppingToken);
                }

                var numFindings = result.findings.Count;
                if (numFindings > 0)
                {
                    foreach (var finding in result.findings)
                    {
                        finding.ProjectId = obj.ProjectId;
                        if (finding.Risk == null) finding.Risk = "medium";
                        finding.CreatedByUid = obj.UserId;
                        db.Vulnerabilities.Add(finding);
                    }

                    try
                    {
                        await db.SaveChangesAsync(stoppingToken);
                    }
                    catch (Exception dbUpdateException)
                    {
                        logger.LogError(dbUpdateException, "An error occurred while processing command results");
                        db.ChangeTracker.Clear();
                    }

                    var notification = new Notification
                    {
                        ToUserId = obj.UserId,
                        Title = "New findings found",
                        Content =
                            $"A total of '{numFindings}' new findings have been found by the '{commandUsage.OutputParser}' command",
                        Status = "unread"
                    };
                    db.Notifications.Add(notification);
                    await db.SaveChangesAsync(stoppingToken);
                }

                if (numHosts > 0 || numFindings > 0)
                    await redis.ListLeftPushAsync("notifications:queue",
                        JsonSerializer.Serialize(new { type = "message" }));
            }
        }
    }
}
