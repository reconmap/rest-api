<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers\Models;

class Asset
{
    public function __construct(private readonly AssetKind $kind,
                                private readonly string    $value,
                                private array              $tags = [],
                                private array              $children = [],
                                private array              $vulnerabilities = []) {
    }

    public function getValue():string {
        return $this->value;
    }

    public function getKind():AssetKind {
        return $this->kind;
    }

    public function addTag(string $tag): void {
        $this->tags[] = $tag;
    }

    public function getTags(): array {
        return $this->tags;
    }

    public function addChild(Asset $child): void {
        $this->children[] = $child;
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function addVulnerability(Vulnerability $vulnerability): void {
        $this->vulnerabilities[] = $vulnerability;
    }

    public function getVulnerabilities(): array {
        return $this->vulnerabilities;
    }
}
