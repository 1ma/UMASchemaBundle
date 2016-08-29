<?php

namespace UMA\SchemaBundle\Validation;

use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\Request;
use UMA\SchemaBundle\Exception\BadJsonRequestException;

class JsonValidator
{
    public function __construct()
    {
        $this->validator = new Validator();
    }

    /**
     * @param Request $request    An HTTP JSON request to validate
     * @param string  $schemaPath Absolute path to the schema file
     *
     * @throws BadJsonRequestException When the request does not pass
     *                                 the schema validation
     */
    public function validate(Request $request, $schemaPath)
    {
        $this->validator->check(
            $requestContent = json_decode($request->getContent()),
            (object) ['$ref' => $fullPath = sprintf('file://%s', $schemaPath)]
        );

        if (!$this->validator->isValid()) {
            throw new BadJsonRequestException(
                $requestContent,
                $this->validator->getSchemaStorage()->getSchema($fullPath),
                $this->validator->getErrors()
            );
        }
    }
}
