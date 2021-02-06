<?php declare(strict_types=1);

namespace Reconmap\Models;

class ReportConfiguration
{
    public int $id;
    public int $project_id;
    public string $optional_sections;
    public ?string $custom_cover;
    public ?string $custom_header;
    public ?string $custom_footer;

    private ?object $optionalSections = null;

    public function showToc(): bool
    {
        if ($this->optionalSections === null) {
            $this->optionalSections = json_decode($this->optional_sections);
        }

        return (bool)($this->optionalSections->toc);
    }

    public function showHeader(): bool
    {
        if ($this->optionalSections === null) {
            $this->optionalSections = json_decode($this->optional_sections);
        }

        return (bool)($this->optionalSections->header);
    }

    public function showFooter(): bool
    {
        if ($this->optionalSections === null) {
            $this->optionalSections = json_decode($this->optional_sections);
        }

        return (bool)($this->optionalSections->footer);
    }
}
