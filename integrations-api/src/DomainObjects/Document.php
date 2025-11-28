<?php declare(strict_types=1);

namespace Reconmap\DomainObjects;

class Document extends \Reconmap\Models\Document
{
    static public function fromObject(object $object): static
    {
        $self = new static();
        $other = new \ReflectionObject($object);
        $props = array_filter($other->getProperties(), fn($prop) => property_exists($self, $prop->getName()));
        array_walk($props, fn($prop) => $self->{$prop->getName()} = $prop->getValue($object));
        return $self;
    }
}
