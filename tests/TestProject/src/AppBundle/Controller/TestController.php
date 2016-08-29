<?php

namespace TestProject\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UMA\SchemaBundle\Annotation\JsonSchema;

class TestController extends Controller
{
    /**
     * @JsonSchema(filename="person.json")
     */
    public function indexAction(Request $request)
    {
        $receivedData = json_decode($request->getContent(), true);

        if (
            isset($receivedData['age']) &&
            isset($receivedData['name']) &&
            is_int($receivedData['age']) &&
            is_string($receivedData['name'])
        ) {
            return new Response(
                'Lookin\' good',
                Response::HTTP_OK
            );
        } else {
            return new Response(
                'With the @JsonSchema annotation invalid data should never reach the controller',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
