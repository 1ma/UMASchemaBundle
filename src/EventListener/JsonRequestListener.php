<?php

namespace UMA\SchemaBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use JsonSchema\Validator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Reader      $reader
     * @param FileLocator $locator
     */
    public function __construct(Reader $reader, FileLocator $locator)
    {
        $this->reader = $reader;
        $this->locator = $locator;
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
                $this->validate(
                    $event->getRequest(), $this->getSchemaPath($annotation)
                );
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
     * @param Request $request
     * @param string  $schemaPath
     *
     * @throws BadJsonRequestException
     */
    private function validate(Request $request, $schemaPath)
    {
        $validator = new Validator();
        $validator->check(
            $requestContent = json_decode($request->getContent()),
            (object) ['$ref' => $uri = sprintf('file://%s', $schemaPath)]
        );

        if (!$validator->isValid()) {
            throw new BadJsonRequestException(
                $requestContent,
                $validator->getSchemaStorage()->getSchema($uri),
                $validator->getErrors()
            );
        }
    }

    /**
     * @param JsonSchema $annotation
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    private function getSchemaPath(JsonSchema $annotation)
    {
        if (is_array($path = $this->locator->locate($annotation->filename))) {
            throw new \UnexpectedValueException('Multiple schemas found');
        }

        return $path;
    }
}
