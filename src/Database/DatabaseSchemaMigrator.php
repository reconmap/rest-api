<?php declare(strict_types=1);

namespace Reconmap\Database;

use Monolog\Logger;
use Reconmap\AppVersion;
use Reconmap\Services\ApplicationConfig;

class DatabaseSchemaMigrator
{
    public function __construct(private readonly \mysqli $db,
                                private readonly ApplicationConfig $config,
                                private readonly Logger $logger)
    {
    }

    public function run(): void
    {
        $fromVersion = $this->getLatestDatabaseMigrationVersion();
        $this->logger->debug("Current database version is $fromVersion");

        $toVersion = AppVersion::Current;
        if ($fromVersion >= $toVersion) {
            $this->logger->debug("Database schema is up to date with $toVersion");
            return;
        }

        $this->logger->info("Migrating database from $fromVersion to $toVersion");

        $migrationFiles = glob($this->config->getAppDir() . '/database/migrations/migration-*.sql');
        foreach ($migrationFiles as $file) {
            sscanf(basename($file), 'migration-%d-%d.sql', $fromStepVersion, $toStepVersion);

            if ($toStepVersion <= $fromVersion) {
                $this->logger->debug("Ignoring lower version $fromStepVersion");
                continue;
            }

            if ($toStepVersion > $toVersion) {
                $this->logger->debug("Ignoring higher version $toStepVersion");
                continue;
            }

            $this->logger->debug("Migrating to version $toStepVersion");

            $sql = trim(file_get_contents($file));
            if (empty($sql)) {
                $this->logger->warning("Migration file $file is empty");
                continue;
            }

            try {
                if (false !== $this->db->multi_query($sql)) {
                    do {
                        if ($result = $this->db->store_result()) {
                            if ($this->db->errno) {
                                $this->logger->warning('Step query error: ' . $this->db->error);
                            }
                            $result->close();
                        }
                    } while ($this->db->next_result());
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }

            $stmt = $this->db->prepare("INSERT INTO database_migration (from_version, to_version) VALUES (?, ?)");
            $stmt->bind_param('ii', $fromVersion, $toStepVersion);
            if (false === $stmt->execute()) {
                $this->logger->warning("Unable to record migration from $fromVersion to $toStepVersion");
                return;
            }
            $stmt->close();

            $fromVersion = $toStepVersion;
        }
    }

    private function getLatestDatabaseMigrationVersion(): int
    {
        try {
            $result = $this->db->query('SELECT MAX(to_version) FROM database_migration');
            $maxToVersion = $result->fetch_column(0);
            $result->close();
            return null === $maxToVersion ? 0 : $maxToVersion;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return 0;
        }
    }
}
