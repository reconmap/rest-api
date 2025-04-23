<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting\Renderers;

use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\TemplateProcessor;
use Psr\Log\LoggerInterface;
use Reconmap\Models\Attachment;
use Reconmap\Models\Report;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Utils\ArrayUtils;

class WordReportRenderer
{
    public function __construct(private readonly LoggerInterface      $logger,
                                private readonly AttachmentFilePath   $attachmentFilePathService,
                                private readonly AttachmentRepository $attachmentRepository,)
    {
        Settings::setOutputEscapingEnabled(true);
    }

    /**
     * @throws CreateTemporaryFileException
     * @throws CopyFileException
     * @throws CommonMarkException
     */
    public function render(array $project, Report $report, array $reportTemplateAttachment, array $vars): int
    {
        $templateFilePath = $this->attachmentFilePathService->generateFilePathFromAttachment($reportTemplateAttachment);

        $template = new TemplateProcessor($templateFilePath);
        $template->setUpdateFields();

        $template->setValue('date', $vars['date']);
        foreach (ArrayUtils::flatten($vars['project'], 'project.') as $key => $value) {
            $template->setValue($key, $value);
        }
        foreach (ArrayUtils::flatten($vars['client'], 'client.') as $key => $value) {
            $template->setValue($key, $value);
        }
        foreach (ArrayUtils::flatten($vars['org'], 'org.') as $key => $value) {
            $template->setValue($key, $value);
        }

        $attachments = $vars['project']['attachments'] ?? [];
        if (!empty($attachments)) {
            $template->cloneBlock('attachments', count($attachments), true, true);
            foreach ($attachments as $index => $attach) {
                $template->setImageValue('attachment.image#' . ($index + 1), $attach);
            }
        }

        try {
            $template->cloneRow('user.full_name', count($vars['users']));
            foreach ($vars['users'] as $index => $user) {
                $template->setValue('user.full_name#' . ($index + 1), $user['full_name']);
                $template->setValue('user.short_bio#' . ($index + 1), $user['short_bio']);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->warning("Error in user section: [$msg]");
        }

        try {
            if (isset($vars["logos"]["org_logo"])) {
                $template->setImageValue('org.logo', $vars["logos"]["org_logo"]);
            }
            if (isset($vars["logos"]["org_small_logo"])) {
                $template->setImageValue('org.small_logo', $vars["logos"]["org_small_logo"]);
            }
            if (isset($vars["logos"]["client_logo"])) {
                $template->setImageValue('client.logo', $vars["logos"]["client_logo"]);
            }
            if (isset($vars["logos"]["client_small_logo"])) {
                $template->setImageValue('client.small_logo', $vars["logos"]["client_small_logo"]);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->warning("Error in logo section: [$msg]");
        }

        if (!empty($vars['vault'])) {
            try {
                $template->cloneRow('vault.name', count($vars['vault']));
                foreach ($vars['vault'] as $index => $item) {
                    $indexPlusOne = $index + 1;
                    $template->setValue('vault.name#' . $indexPlusOne, $item['name']);
                    $template->setValue('vault.note#' . $indexPlusOne, $item['note']);
                    $template->setValue('vault.type#' . $indexPlusOne, $item['type']);
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $this->logger->warning("Error in vault section: [$msg]");
            }
        }

        try {
            $template->cloneRow('target.name', count($vars['targets']));
            foreach ($vars['targets'] as $index => $target) {
                $indexPlusOne = $index + 1;
                $template->setValue('target.name#' . $indexPlusOne, $target['name']);
                $template->setValue('target.kind#' . $indexPlusOne, $target['kind']);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->warning("Error in target section: [$msg]");
        }

        foreach ($vars['findingsOverview'] as $stat) {
            $template->setValue('findings.count.' . $stat['severity'], $stat['count']);
        }

        $markdownParser = new GithubFlavoredMarkdownConverter();
        $word = new PhpWord();

        try {
            $template->cloneBlock('vulnerabilities', count($vars['vulnerabilities']), true, true);
            foreach ($vars['vulnerabilities'] as $index => $vulnerability) {
                $template->setValue('vulnerability.name#' . ($index + 1), $vulnerability['summary']);

                if (!is_null($vulnerability['description'])) {
                    $description = $markdownParser->convert($vulnerability['description']);

                    $tempTable = $word->addSection()->addTable();
                    $cell = $tempTable->addRow()->addCell();
                    Html::addHtml($cell, $description);

                    $template->setComplexBlock('vulnerability.description#' . ($index + 1), $tempTable);
                }

                if (!is_null($vulnerability['remediation'])) {
                    $remediation = $markdownParser->convert($vulnerability['remediation']);

                    $tempTable = $word->addSection()->addTable();
                    $cell = $tempTable->addRow()->addCell();
                    Html::addHtml($cell, $remediation);

                    $template->setComplexBlock('vulnerability.remediation#' . ($index + 1), $tempTable);
                }

                $attachments = $vulnerability['attachments'];
                $template->cloneBlock('vulnerability.attachments#' . ($index + 1), count($attachments), true, true);
                foreach ($attachments as $i => $attach) {
                    $name = 'vulnerability.attachment.image#' . ($index + 1) . "#" . ($i + 1);
                    $template->setImageValue($name, $attach);
                }

                if (!is_null($vulnerability['proof_of_concept'])) {
                    $proofOfConcept = $markdownParser->convert($vulnerability['proof_of_concept']);
                    $dom = new \DomDocument();
                    $dom->loadHTML(strval($proofOfConcept));
                    $xpath = new \DOMXpath($dom);

                    $tempTable = $word->addSection()->addTable();

                    $elements = $xpath->evaluate('//body/*');
                    foreach ($elements as $node) {
                        $cell = $tempTable->addRow()->addCell();
                        if ($node->tagName === 'pre') {
                            $nodeDiv = $dom->createElement("p", $node->nodeValue);
                            $nodeDiv->setAttribute('style', 'font-family: Courier; font-size: 10px;');
                            Html::addHtml($cell, nl2br($node->ownerDocument->saveXML($nodeDiv)));
                        } else {
                            Html::addHtml($cell, $node->ownerDocument->saveXML($node));
                        }
                    }

                    $template->setComplexBlock('vulnerability.proof_of_concept#' . ($index + 1), $tempTable);
                }

                $template->setValue('vulnerability.category_name#' . ($index + 1), $vulnerability['category_name']);
                $template->setValue('vulnerability.cvss_score#' . ($index + 1), $vulnerability['cvss_score']);
                $template->setValue('vulnerability.owasp_vector#' . ($index + 1), $vulnerability['owasp_vector']);
                $template->setValue('vulnerability.owasp_overall#' . ($index + 1), $vulnerability['owasp_overall']);
                $template->setValue('vulnerability.owasp_likelihood#' . ($index + 1), $vulnerability['owasp_likehood']);
                $template->setValue('vulnerability.owasp_impact#' . ($index + 1), $vulnerability['owasp_impact']);
                $template->setValue('vulnerability.severity#' . ($index + 1), $vulnerability['risk']);
                $template->setValue('vulnerability.impact#' . ($index + 1), $vulnerability['impact']);
                $template->setValue('vulnerability.references#' . ($index + 1), $vulnerability['external_refs']);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->warning("Error in vulnerabilities section: [$msg]");
        }

        if (!empty($vars['contacts'])) {
            try {
                $template->cloneBlock('contacts', count($vars['contacts']), true, true);
                foreach ($vars['contacts'] as $index => $vulnerability) {
                    $template->setValue('contact.kind#' . ($index + 1), $vulnerability['kind']);
                    $template->setValue('contact.name#' . ($index + 1), $vulnerability['name']);
                    $template->setValue('contact.phone#' . ($index + 1), $vulnerability['phone']);
                    $template->setValue('contact.email#' . ($index + 1), $vulnerability['email']);
                    $template->setValue('contact.role#' . ($index + 1), $vulnerability['role']);
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $this->logger->warning("Error in contacts section: [$msg]");
            }
        }

        if (!empty($vars['parentCategories'])) {
            try {
                $template->cloneBlock('category.group', count($vars['parentCategories']), true, true);
                foreach ($vars['parentCategories'] as $index => $category) {
                    $template->setValue('category.group#' . ($index + 1), $category['name']);
                    $template->setValue('category.severity#' . ($index + 1), $category['owasp_overall']);
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $this->logger->warning("Error in parent categories section: [$msg]");
            }
        }

        if (!empty($vars['categories'])) {
            try {
                $template->cloneBlock('category.name', count($vars['categories']), true, true);
                foreach ($vars['categories'] as $index => $category) {
                    $template->setValue('category.name#' . ($index + 1), $category['name']);
                    $template->setValue('category.status#' . ($index + 1), $category['status']);
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $this->logger->warning("Error in categories section: [$msg]");
            }
        }

        try {
            $template->cloneRow('revisionHistoryDateTime', count($vars['reports']));
            foreach ($vars['reports'] as $index => $reportRevision) {
                $indexPlusOne = $index + 1;
                $template->setValue('revisionHistoryDateTime#' . $indexPlusOne, $reportRevision['insert_ts']);
                $template->setValue('revisionHistoryVersionName#' . $indexPlusOne, $reportRevision['version_name']);
                $template->setValue('revisionHistoryVersionDescription#' . $indexPlusOne, $reportRevision['version_description']);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->warning("Error in reports section: [$msg");
        }

        $fileName = uniqid(gethostname());
        $basePath = $this->attachmentFilePathService->generateBasePath();
        $filePath = $basePath . $fileName;

        $template->saveAs($filePath);

        $projectName = str_replace(' ', '_', strtolower($project['name']));
        $clientFileName = "reconmap-$projectName-v{$report->versionName}.docx";

        $attachment = new Attachment();
        $attachment->parent_type = 'report';
        $attachment->parent_id = $report->id;;
        $attachment->submitter_uid = $report->generatedByUid;

        $attachment->file_name = $fileName;
        $attachment->file_mimetype = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $attachment->file_hash = hash_file('md5', $filePath);
        $attachment->file_size = filesize($filePath);
        $attachment->client_file_name = $clientFileName;

        return $this->attachmentRepository->insert($attachment);
    }
}
