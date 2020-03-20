<?php

namespace Shopping\ApiTKDeprecationBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Shopping\ApiTKHeaderBundle\Service\HeaderInformation;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Shopping\ApiTKDeprecationBundle\Annotation\Deprecated;
use Symfony\Component\HttpKernel\Tests\Controller;

/**
 * Class ControllerListener
 *
 * Remember, what controller got called in this request, so we can get the corresponding annotation in the ResponseView.
 *
 * @package Shopping\ApiTKDeprecationBundle\EventListener
 */
class DeprecationListener
{
    /**
     * @var bool
     */
    private $masterRequest = true;
    /**
     * @var Reader
     */
    private $reader;
    /**
     * @var HeaderInformation
     */
    private $headerInformation;

    /**
     * @param Reader $reader
     * @param HeaderInformation $headerInformation
     */
    public function __construct(Reader $reader, HeaderInformation $headerInformation)
    {
        $this->reader = $reader;
        $this->headerInformation = $headerInformation;
    }

    public function onKernelController(FilterControllerEvent $event): void
    {
        //Only transform on original action
        if (!$this->masterRequest) {
            return;
        }
        $this->masterRequest = false;

        if (!is_array($event->getController())) {
            return;
        }

        $annotation = $this->getViewAnnotationByController($event->getController());
        if (!$annotation) {
            return;
        }

        $this->headerInformation->add('deprecated', $annotation->getDescription() ?? 'deprecated');
        if ($annotation->getRemovedAfter()) {
            $this->headerInformation->add(
                'deprecated-removed-at',
                $annotation->getRemovedAfter()->format('Y-m-d')
            );
        }
        if ($annotation->getSince()) {
            $this->headerInformation->add('deprecated-since', $annotation->getSince()->format('Y-m-d'));
        }
    }

    /**
     * @param callable $controller
     * @return null|Deprecated
     */
    private function getViewAnnotationByController(callable $controller): ?Deprecated
    {
        /** @var Controller $controllerObject */
        list($controllerObject, $methodName) = $controller;

        $controllerReflectionObject = new \ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);

        $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Deprecated) {
                return $annotation;
            }
        }

        return null;
    }
}
