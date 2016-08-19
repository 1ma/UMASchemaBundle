<?php

namespace UMA\SchemaBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use JsonSchema\Validator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use UMA\SchemaBundle\Annotation\JsonSchema;
use UMA\SchemaBundle\Exception\BadJsonRequestException;

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
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param Reader             $reader
     * @param FileLocator        $locator
     * @param ContainerInterface $container
     */
    public function __construct(Reader $reader, FileLocator $locator, ContainerInterface $container)
    {
        $this->reader = $reader;
        $this->locator = $locator;
        $this->container = $container;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $actionMethod = (new \ReflectionClass(get_class($controller[0])))
            ->getMethod($controller[1]);

        foreach ($this->reader->getMethodAnnotations($actionMethod) as $annotation) {
            if ($annotation instanceof JsonSchema) {
                if (is_array($fullpath = $this->locator->locate($annotation->uri))) {
                    throw new \UnexpectedValueException('Multiple schemas found');
                }

                $validator = new Validator();
                $validator->check(
                    $requestContent = json_decode($event->getRequest()->getContent()),
                    (object)['$ref' => $uri = sprintf('file://%s', $fullpath)]
                );

                if (!$validator->isValid()) {
                    throw new BadJsonRequestException(
                        $requestContent,
                        $validator->getSchemaStorage()->getSchema($uri),
                        $validator->getErrors()
                    );
                }

                break;
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
}
