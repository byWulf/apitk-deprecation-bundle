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

            $headerInformation = [
                'x-api-deprecated' => [
                    'description' => 'Flag for endpoint deprecation',
                    'type' => 'string ("deprecated")',
                ]
            ];

            $removedString = '';
            if ($annotation->getRemovedAfter()) {
                $removedString = 'REMOVED AT ' . $annotation->getRemovedAfter()->format('Y-m-d') . ' OR LATER !!! ';

                $headerInformation['x-apitk-deprecated-removed-at'] = [
                    'description' => 'The time when the endpoint start being deprecated',
                    'type' => 'date ("Y-m-d")',
                ];
            }

            $response = $operation->getResponses()->get(200);
            $response->merge(['headers' => $headerInformation]);

            $operation->getResponses()->set(200, $response);


            /** @noinspection PhpToStringImplementationInspection */
            $operation->setSummary($deprecatedString . $removedString);
        }
    }
}