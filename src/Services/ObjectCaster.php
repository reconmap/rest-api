<?php declare(strict_types=1);

namespace Reconmap\Services;

class ObjectCaster
{
    static final public function cast(object $destObject, object $sourceObject): object
    {
        $sourceRef = new \ReflectionObject($sourceObject);
        $destRef = new \ReflectionObject($destObject);
        $sourceProps = $sourceRef->getProperties();
        foreach ($sourceProps as $sourceProp) {
            $propName = $sourceProp->getName();
            if ($sourceProp->isInitialized($sourceObject)) {
                $propValue = $sourceProp->getValue($sourceObject);
                if ($destRef->hasProperty($propName)) {
                    $propDest = $destRef->getProperty($propName);
                    $propDest->setValue($destObject, $propValue);
                } else {
                    $destObject->$propName = $propValue;
                }
            }
        }
        return $destObject;
    }
}
