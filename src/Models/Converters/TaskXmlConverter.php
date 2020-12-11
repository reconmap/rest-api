<?php declare(strict_types=1);

namespace Reconmap\Models\Converters;

use DOMDocument;
use DOMElement;

class TaskXmlConverter
{
    public function toXml(DOMDocument $xmlDoc, array $task): DOMElement
    {
        $vulnerabilityEl = $xmlDoc->createElement('task');
        $vulnerabilityEl->setAttribute('name', $task['name']);

        $vulnerabilityDescriptionNode = $xmlDoc->createElement('description');
        if (isset($task['description'])) {
            $vulnerabilityDescriptionNode->appendChild($xmlDoc->createTextNode($task['description']));
        }
        $vulnerabilityEl->appendChild($vulnerabilityDescriptionNode);
        return $vulnerabilityEl;
    }
}
