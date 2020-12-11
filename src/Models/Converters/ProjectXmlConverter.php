<?php declare(strict_types=1);

namespace Reconmap\Models\Converters;

use DOMDocument;
use DOMElement;

class ProjectXmlConverter
{
    public function toXml(DOMDocument $xmlDoc, array $project): DOMElement
    {
        $vulnerabilityEl = $xmlDoc->createElement('project');
        $vulnerabilityEl->setAttribute('name', $project['name']);

        $vulnerabilityDescriptionNode = $xmlDoc->createElement('description');
        if (isset($project['description'])) {
            $vulnerabilityDescriptionNode->appendChild($xmlDoc->createTextNode($project['description']));
        }
        $vulnerabilityEl->appendChild($vulnerabilityDescriptionNode);
        return $vulnerabilityEl;
    }
}
