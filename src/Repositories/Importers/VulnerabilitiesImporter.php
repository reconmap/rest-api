<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use Reconmap\Models\Vulnerability;
use Reconmap\Repositories\VulnerabilityRepository;

class VulnerabilitiesImporter implements Importable
{
    public function __construct(private VulnerabilityRepository $repository)
    {
    }

    public function import(int $userId, array $vulnerabilities): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        foreach ($vulnerabilities as $jsonDoc) {
            try {
                $vulnerability = new Vulnerability();
                $vulnerability->creator_uid = $userId;
                $vulnerability->category_id = intval($jsonDoc->category_id);
                $vulnerability->is_template = false;
                $vulnerability->summary = $jsonDoc->summary;
                $vulnerability->description = $jsonDoc->description;
                $vulnerability->proof_of_concept = $jsonDoc->proof_of_concept ?? '-';
                $vulnerability->impact = $jsonDoc->impact ?? '-';
                $vulnerability->remediation = $jsonDoc->remediation ?? null;
                $vulnerability->risk = $jsonDoc->risk;
                $vulnerability->cvss_score = $jsonDoc->cvss_score;
                $vulnerability->cvss_vector = $jsonDoc->cvss_vector;
                $vulnerability->tags = $jsonDoc->tags ?? null;
                $vulnerability->owasp_vector = $jsonDoc->owasp_vector;
                $vulnerability->owasp_overall = $jsonDoc->owasp_overall;
                $vulnerability->owasp_impact = $jsonDoc->owasp_impact ? floatval($jsonDoc->owasp_impact) : null;
                $vulnerability->owasp_likehood = $jsonDoc->owasp_likehood ? floatval($jsonDoc->owasp_likehood) : null;

                $this->repository->insert($vulnerability);

                $response['count']++;
            } catch (\Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        }

        return $response;
    }
}
