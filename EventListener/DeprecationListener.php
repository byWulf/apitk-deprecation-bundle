<?php

namespace Ofeige\Rfc18Bundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Ofeige\ApiBundle\Service\HeaderInformation;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Ofeige\Rfc18Bundle\Annotation as Rfc18;
use Symfony\Component\HttpKernel\Tests\Controller;

/**
 * Class ControllerListener
 *
 * Remember, what controller got called in this request, so we can get the corresponding annotation in the ResponseView.
 *
 * @package Ofeige\Rfc18Bundle\EventListener
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

    public function onKernelController(FilterControllerEvent $event)
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

        $this->headerInformation->add('deprecated', 'deprecated');
        if ($annotation->getRemovedAfter()) {
            $this->headerInformation->add('deprecated-removed-at', $annotation->getRemovedAfter()->format('Y-m-d'));
        }
    }

    /**
     * @param callable $controller
     * @return null|Rfc18\Deprecated
     */
    private function getViewAnnotationByController(callable $controller): ?Rfc18\Deprecated
    {
        /** @var Controller $controllerObject */
        list($controllerObject, $methodName) = $controller;

        $controllerReflectionObject = new \ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);

        $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Rfc18\Deprecated) {
                return $annotation;
            }
        }

        return null;
    }
}