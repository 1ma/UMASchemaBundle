<?php

namespace UMA\SchemaBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use UMA\SchemaBundle\Exception\BadJsonRequestException;

class BadJsonRequestExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof BadJsonRequestException) {
            return;
        }

        $response = new JsonResponse([
            'errors' => $exception->getErrors(),
            'sent_data' => $exception->getData(),
            'schema' => $exception->getSchema(),
        ], Response::HTTP_BAD_REQUEST);

        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }
}
