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
            $sourceProp->setAccessible(true);
            $propName = $sourceProp->getName();
            if ($destRef->hasProperty($propName) && $sourceProp->isInitialized($sourceObject)) {
                $propValue = $sourceProp->getValue($sourceObject);

                $propDest = $destRef->getProperty($propName);
                $propDest->setAccessible(true);
                $propDest->setValue($destObject, $propValue);
            }
        }
        return $destObject;
    }
}
