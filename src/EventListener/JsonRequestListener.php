<?php

namespace UMA\SchemaBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use UMA\SchemaBundle\Annotation\JsonSchema;
use UMA\SchemaBundle\Validation\JsonValidator;

class JsonRequestListener implements EventSubscriberInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var JsonValidator
     */
    private $validator;

    /**
     * @param Reader        $reader
     * @param JsonValidator $validator
     */
    public function __construct(Reader $reader, JsonValidator $validator)
    {
        $this->reader = $reader;
        $this->validator = $validator;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        foreach ($this->getActionAnnotations((array) $event->getController()) as $annotation) {
            if ($annotation instanceof JsonSchema) {
                $this->validator->validate($event->getRequest(), $annotation);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::CONTROLLER => 'onKernelController'];
    }

    /**
     * @param array $controller
     *
     * @return object[]
     */
    private function getActionAnnotations(array $controller)
    {
        $actionMethod = (new \ReflectionClass(get_class($controller[0])))
            ->getMethod($controller[1]);

        return $this->reader->getMethodAnnotations($actionMethod);
    }
}
