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
     * @var bool
     */
    private $triggerDeprecations;

    /**
     * @param Reader            $reader
     * @param HeaderInformation $headerInformation
     * @param bool              $triggerDeprecations
     */
    public function __construct(Reader $reader, HeaderInformation $headerInformation, bool $triggerDeprecations)
    {
        $this->reader = $reader;
        $this->headerInformation = $headerInformation;
        $this->triggerDeprecations = $triggerDeprecations;
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

        // check for @Deprecated on the controller class
        $classAnnotation = $this->getControllerClassAnnotation($event->getController());

        // check for @Deprecated on the controller method
        $methodAnnotation = $this->getControllerMethodAnnotation($event->getController());

        if (!$classAnnotation && !$methodAnnotation) {
            // no-op when neither the controller nor the method have @Deprecated annotations
            return;
        }

        // method annotations take precedence over class annotations
        $annotation = $methodAnnotation ?? $classAnnotation;

        $this->handleDeprecation($annotation);

        if ($this->triggerDeprecations) {
            $this->noticeDeprecationError($annotation, $event);
        }
    }

    /**
     * @param Deprecated      $annotation
     * @param ControllerEvent $event
     */
    private function noticeDeprecationError(Deprecated $annotation, ControllerEvent $event): void
    {
        @trigger_error(
            sprintf(
                'Using the "%s %s" route is deprecated%s%s. %s',
                $event->getRequest()->getMethod(),
                $event->getRequest()->attributes->get('_route') ?? $event->getRequest()->getRequestUri(),
                $annotation->getSince() ? ' since ' . $annotation->getSince()->format('Y-m-d') : '',
                $annotation->getRemovedAfter() ? ' and will be removed after ' . $annotation->getRemovedAfter()->format('Y-m-d') : '',
                $annotation->getDescription()
            ),
            E_USER_DEPRECATED
        );
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
     * @return Deprecated|null
     */
    private function getControllerClassAnnotation(callable $controller): ?Deprecated
    {
        /** @var AbstractController $controllerObject */
        list($controllerObject) = $controller;

        $controllerReflectionObject = new ReflectionObject($controllerObject);
        $annotations = $this->reader->getClassAnnotations($controllerReflectionObject);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Deprecated) {
                return $annotation;
            }
        }

        return null;
    }
}
