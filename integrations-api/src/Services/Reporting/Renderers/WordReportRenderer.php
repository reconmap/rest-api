<?php

declare(strict_types=1);

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

readonly class WordReportRenderer
{
    public function __construct(
        private LoggerInterface      $logger,
        private AttachmentFilePath   $attachmentFilePathService,
        private AttachmentRepository $attachmentRepository
    ) {
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
        $template->setValue('lastRevisionName', $vars['lastRevisionName']);


        foreach (ArrayUtils::flatten($vars['project'], 'project.') as $key => $value) {
            $template->setValue($key, $value);
        }

        try {
            if (isset($vars["serviceProvider"]["logo"])) {
                $template->setImageValue('serviceProvider.logo', $vars["serviceProvider"]["logo"]);
                unset($vars["serviceProvider"]["logo"]);
            }
            if (isset($vars["serviceProvider"]["smallLogo"])) {
                $template->setImageValue('serviceProvider.smallLogo', $vars["serviceProvider"]["smallLogo"]);
                unset($vars["serviceProvider"]["smallLogo"]);
            }
            if (isset($vars["client"]["logo"])) {
                $template->setImageValue('client.logo', $vars["client"]["logo"]);
                unset($vars["client"]["logo"]);
            }
            if (isset($vars["client"]["smallLogo"])) {
                $template->setImageValue('client.smallLogo', $vars["client"]["smallLogo"]);
                unset($vars["client"]["smallLogo"]);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->warning("Error in logo section: [$msg]");
        }
        foreach (ArrayUtils::flatten($vars['client'], 'client.') as $key => $value) {
            $template->setValue($key, $value);
        }

        foreach (ArrayUtils::flatten($vars['serviceProvider'], 'serviceProvider.') as $key => $value) {
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
            $template->cloneRow('asset.name', count($vars['assets']));
            foreach ($vars['assets'] as $index => $target) {
                $indexPlusOne = $index + 1;
                $template->setValue('asset.name#' . $indexPlusOne, $target['name']);
                $template->setValue('asset.kind#' . $indexPlusOne, $target['kind']);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->warning("Error in target section: [$msg]");
        }

        foreach ($vars['findings']['stats'] as $stat) {
            $template->setValue('findings.stats.count.' . $stat['severity'], $stat['count']);
        }

        $markdownParser = new GithubFlavoredMarkdownConverter();
        $word = new PhpWord();

        try {
            $template->cloneBlock('findings.list', count($vars['findings']['list']), true, true);
            foreach ($vars['findings']['list'] as $index => $vulnerability) {
                $template->setValue('findings.list.summary#' . ($index + 1), $vulnerability['summary']);

                if (!is_null($vulnerability['description'])) {
                    $description = $markdownParser->convert($vulnerability['description']);

                    $tempTable = $word->addSection()->addTable();
                    $cell = $tempTable->addRow()->addCell();
                    Html::addHtml($cell, $description);

                    $template->setComplexBlock('findings.list.description#' . ($index + 1), $tempTable);
                }

                if (!is_null($vulnerability['remediation'])) {
                    $remediation = $markdownParser->convert($vulnerability['remediation']);

                    $tempTable = $word->addSection()->addTable();
                    $cell = $tempTable->addRow()->addCell();
                    Html::addHtml($cell, $remediation);

                    $template->setComplexBlock('findings.list.remediation#' . ($index + 1), $tempTable);
                }

                $attachments = $vulnerability['attachments'];
                $template->cloneBlock('findings.list.attachments#' . ($index + 1), count($attachments), true, true);
                foreach ($attachments as $i => $attach) {
                    $name = 'findings.list.attachment.image#' . ($index + 1) . "#" . ($i + 1);
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

                    $template->setComplexBlock('findings.list.proof_of_concept#' . ($index + 1), $tempTable);
                }

                $template->setValue('findings.list.category_name#' . ($index + 1), $vulnerability['category_name']);
                $template->setValue('findings.list.cvss_score#' . ($index + 1), $vulnerability['cvss_score']);
                $template->setValue('findings.list.owasp_vector#' . ($index + 1), $vulnerability['owasp_vector']);
                $template->setValue('findings.list.owasp_overall#' . ($index + 1), $vulnerability['owasp_overall']);
                $template->setValue('findings.list.owasp_likelihood#' . ($index + 1), $vulnerability['owasp_likehood']);
                $template->setValue('findings.list.owasp_impact#' . ($index + 1), $vulnerability['owasp_impact']);
                $template->setValue('findings.list.severity#' . ($index + 1), $vulnerability['risk']);
                $template->setValue('findings.list.impact#' . ($index + 1), $vulnerability['impact']);
                $template->setValue('findings.list.references#' . ($index + 1), $vulnerability['external_refs']);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->warning("Error in findings section: [$msg]");
        }

        if (!empty($vars['client']['contacts'])) {
            try {
                $template->cloneBlock('client.contacts', count($vars['client']['contacts']), true, true);
                foreach ($vars['client']['contacts'] as $index => $vulnerability) {
                    $template->setValue('client.contacts.kind#' . ($index + 1), $vulnerability['kind']);
                    $template->setValue('client.contacts.name#' . ($index + 1), $vulnerability['name']);
                    $template->setValue('client.contacts.phone#' . ($index + 1), $vulnerability['phone']);
                    $template->setValue('client.contacts.email#' . ($index + 1), $vulnerability['email']);
                    $template->setValue('client.contacts.role#' . ($index + 1), $vulnerability['role']);
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $this->logger->warning("Error in contacts section: [$msg]");
            }
        }

        try {
            $template->cloneRow('revisionHistoryDateTime', count($vars['revisions']));
            foreach ($vars['revisions'] as $index => $reportRevision) {
                $indexPlusOne = $index + 1;
                $template->setValue('revisionHistoryDateTime#' . $indexPlusOne, $reportRevision['created_at']);
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
        $clientFileName = "reconmap-$projectName-v{$report->version_name}.docx";

        $attachment = new Attachment();
        $attachment->parent_type = 'report';
        $attachment->parent_id = $report->id;;
        $attachment->created_by_uid = $report->created_by_uid;

        $attachment->file_name = $fileName;
        $attachment->file_mimetype = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $attachment->file_hash = hash_file('md5', $filePath);
        $attachment->file_size = filesize($filePath);
        $attachment->client_file_name = $clientFileName;

        return $this->attachmentRepository->insert($attachment);
    }
}
