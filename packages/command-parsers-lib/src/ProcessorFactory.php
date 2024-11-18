<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use GlobIterator;

class ProcessorFactory
{
    public function createFromOutputParserName(string $outputParserName): ?AbstractOutputProcessor
    {
        $className = 'Reconmap\\CommandOutputParsers\\' . ucfirst($outputParserName) . 'OutputProcessor';

        if (class_exists($className)) {
            return new $className();
        }

        return null;
    }

    public function getAll(): array
    {
        $fileIterator = new GlobIterator(__DIR__ . '/*OutputProcessor.php');

        $list = [];
        foreach ($fileIterator as $file) {
            $fileName = $file->getFileName();
            $commandName = str_replace('OutputProcessor.php', '', $fileName);
            $commandCode = strtolower($commandName);
            $list[] = ['name' => $commandName, 'code' => $commandCode];
        }
        return $list;
    }
}
