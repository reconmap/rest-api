<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting;

use PhpOffice\PhpWord\TemplateProcessor;

class TemplateProcessFactory
{
    public function createFromFileName(string $fileName): TemplateProcessor
    {
        return new TemplateProcessor($fileName);
    }
}
