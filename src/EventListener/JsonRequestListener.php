<?php

namespace UMA\SchemaBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Config\FileLocator;
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
     * @var FileLocator
     */
    private $locator;

    /**
     * @var JsonValidator
     */
    private $validator;

    /**
     * @param Reader        $reader
     * @param FileLocator   $locator
     * @param JsonValidator $validator
     */
    public function __construct(Reader $reader, FileLocator $locator, JsonValidator $validator)
    {
        $this->reader = $reader;
        $this->locator = $locator;
        $this->validator = $validator;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        foreach ($this->getActionAnnotations((array) $event->getController()) as $annotation) {
            if ($annotation instanceof JsonSchema) {
                $this->validator->validate($event->getRequest(), $this->getSchemaPath($annotation));
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

    /**
     * @param JsonSchema $annotation
     *
     * @return string
     *
     * @throws \InvalidArgumentException When the schema file is not found
     * @throws \UnexpectedValueException When multiple schema files
     *                                   with the same name are found
     */
    private function getSchemaPath(JsonSchema $annotation)
    {
        if (is_array($path = $this->locator->locate($annotation->filename))) {
            throw new \UnexpectedValueException('Multiple schemas found');
        }

        return $path;
    }
}
