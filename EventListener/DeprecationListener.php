<?php

namespace Shopping\ApiTKDeprecationBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use ReflectionObject;
use ReflectionClass;
use ReflectionException;
use Shopping\ApiTKHeaderBundle\Service\HeaderInformation;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Shopping\ApiTKDeprecationBundle\Annotation\Deprecated;

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

        // check for @Deprecated on the controller class
        $classAnnotation = $this->getControllerClassAnnotation($event->getController());

        // check for @Deprecated on the controller method
        $methodAnnotation = $this->getControllerMethodAnnotation($event->getController());

        if (!$classAnnotation && !$methodAnnotation) {
            // no-op when neither the controller nor the method have @Deprecated annotations
            return;
        }

        // method annotations take precedence over class annotations
        /** @var Deprecated $annotation */
        $annotation = $methodAnnotation ?? $classAnnotation;

        $this->handleDeprecation($annotation);
    }


    /**
     * @param Deprecated $annotation
     */
    private function handleDeprecation(Deprecated $annotation): void
    {
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
     *
     * @throws ReflectionException
     *
     * @return Deprecated|null
     */
    private function getControllerMethodAnnotation(callable $controller): ?Deprecated
    {
        /** @var AbstractController $controllerObject */
        list($controllerObject, $methodName) = $controller;

        $controllerReflectionObject = new ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);

        $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Deprecated) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @param callable $controller
     *
     * @throws ReflectionException
     *
     * @return Deprecated|null
     */
    private function getControllerClassAnnotation(callable $controller): ?Deprecated
    {
        /** @var AbstractController $controllerObject */
        list($controllerObject) = $controller;

        $controllerReflectionClass = new ReflectionClass($controllerObject);
        $annotations = $this->reader->getClassAnnotations($controllerReflectionClass);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Deprecated) {
                return $annotation;
            }
        }

        return null;
    }
}
