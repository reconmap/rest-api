<?php declare(strict_types=1);

namespace Reconmap\Models;

class ReportConfiguration
{
    public int $id;
    public int $project_id;
    public bool $include_toc = true;
    public bool $include_revisions_table = true;
    public bool $include_team_bios = true;
    public bool $include_findings_overview = true;
    public string $include_cover = 'default';
    public string $include_header = 'default';
    public string $include_footer = 'default';
    public ?string $custom_cover;
    public ?string $custom_header;
    public ?string $custom_footer;
}
