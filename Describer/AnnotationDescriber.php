<?php

namespace Shopping\ApiTKDeprecationBundle\Describer;

use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Path;
use Shopping\ApiTKCommonBundle\Describer\AbstractDescriber;
use Shopping\ApiTKDeprecationBundle\Annotation\Deprecated;

/**
 * Class AnnotationDescriber
 *
 * Auto generates deprecation notices to the documentation
 *
 * @package Shopping\ApiTKDeprecationBundle\Describer
 */
class AnnotationDescriber extends AbstractDescriber
{
    /**
     * @param Operation         $operation
     * @param \ReflectionMethod $classMethod
     * @param Path              $path
     * @param string            $method
     */
    protected function handleOperation(
        Operation $operation,
        \ReflectionMethod $classMethod,
        Path $path,
        string $method
    ): void {
        foreach ($this->reader->getMethodAnnotations($classMethod) as $annotation) {
            if (!$annotation instanceof Deprecated) {
                continue;
            }

            if ($annotation->isHiddenInDoc()) {
                $path->removeOperation($method);
            }

            $deprecatedString = '!!! DEPRECATED !!! ';

            $removedString = '';
            if ($annotation->getRemovedAfter()) {
                $removedString .= 'REMOVED AT '
                    . $annotation->getRemovedAfter()->format('Y-m-d')
                    . ' OR LATER !!! ';
            }
            if ($annotation->getSince()) {
                $removedString .= 'Deprecated since '
                    . $annotation->getSince()->format('Y-m-d')
                    . ' ';
            }
            if (!empty($annotation->getDescription())) {
                $removedString .= $annotation->getDescription() . ' ';
            }

            /** @noinspection PhpToStringImplementationInspection */
            $operation->setSummary($deprecatedString . $removedString);
        }
    }
}
