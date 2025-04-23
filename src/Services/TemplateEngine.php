<?php declare(strict_types=1);

namespace Reconmap\Services;

use Twig\Loader\FilesystemLoader;

class TemplateEngine
{
    private string $templatesDirectory;
    private \Twig\Environment $environment;

    public function __construct(ApplicationConfig $config)
    {
        $this->templatesDirectory = $config->getAppDir() . '/resources/templates';
        $filesystemLoader = new FilesystemLoader($this->templatesDirectory);
        $this->environment = new \Twig\Environment($filesystemLoader, ['strict_variables' => false]);
    }

    public function render(string $templateName, array $context): string
    {
        return $this->environment->render($templateName . '.twig', $context);
    }

    public function renderString(string $template, array $context): string
    {
        $template = $this->environment->createTemplate($template);
        return $template->render($context);
    }

    public function getTemplatesDirectory(): string
    {
        return $this->templatesDirectory;
    }
}
