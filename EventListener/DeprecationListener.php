<?php

declare(strict_types=1);

namespace Shopping\ApiTKDeprecationBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use ReflectionException;
use ReflectionObject;
use Shopping\ApiTKDeprecationBundle\Annotation\Deprecated;
use Shopping\ApiTKHeaderBundle\Service\HeaderInformation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class ControllerListener.
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
     * @param Reader            $reader
     * @param HeaderInformation $headerInformation
     */
    public function __construct(Reader $reader, HeaderInformation $headerInformation)
    {
        $this->reader = $reader;
        $this->headerInformation = $headerInformation;
    }

    /**
     * @param ControllerEvent $event
     *
     * @throws ReflectionException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        // only transform on original action
        if (!$this->masterRequest) {
            return;
        }
        $this->masterRequest = false;

        // If the controller is the class instead of method in the class
        $annotation = $this->getAnnotationControllerClass($event->getController());
        if (!is_array($event->getController()) && !$annotation) {
            return;
        }

        // Class annotation has priority over method annotation.
        $annotation = $annotation ?? $this->getViewAnnotationByController($event->getController());
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
     *
     * @throws ReflectionException
     *
     * @return Deprecated|null
     */
    private function getViewAnnotationByController(callable $controller): ?Deprecated
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
     * @return Deprecated|null
     */
    private function getAnnotationControllerClass(callable $controller): ?Deprecated
    {
        $annotations = $this->reader->getClassAnnotations(new ReflectionObject($controller));

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Deprecated) {
                return $annotation;
            }
        }

        return null;
    }
}
