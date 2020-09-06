<?php

declare(strict_types=1);

namespace Reconmap\Integrations;

interface Integration
{

    public function getName(): string;
    public function getDescription(): string;
    public function hasConfiguration(): bool;
    public function getConfiguration(): array;
}
