<?php

namespace UMA\SchemaBundle\Validation;

use JsonSchema\Validator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use UMA\SchemaBundle\Annotation\JsonSchema;
use UMA\SchemaBundle\Exception\BadJsonRequestException;

class JsonValidator
{
    /**
     * @var FileLocator
     */
    private $locator;

    /**
     * @param FileLocator $locator
     */
    public function __construct(FileLocator $locator)
    {
        $this->locator = $locator;
        $this->validator = new Validator();
    }

    /**
     * @param Request    $request    An HTTP JSON request to validate
     * @param JsonSchema $annotation The shit
     *
     * @throws BadJsonRequestException When the request does not pass
     *                                 the schema validation
     */
    public function validate(Request $request, JsonSchema $annotation)
    {
        if (null === $requestContent = json_decode($request->getContent())) {
            throw new BadJsonRequestException($request->getContent(), null, ['HTTP request body does not contain a JSON payload']);
        }

        if ($annotation->strict && $request->headers->get('Content-Type') !== $annotation->contentType) {
            throw new BadJsonRequestException($request->getContent(), null, ["HTTP request Content-Type must be {$annotation->contentType}"]);
        }

        $this->validator->check(
            $requestContent, (object) ['$ref' => $fullPath = sprintf('file://%s', $this->getSchemaPath($annotation))]
        );

        if (!$this->validator->isValid()) {
            throw new BadJsonRequestException(
                $requestContent,
                $this->validator->getSchemaStorage()->getSchema($fullPath),
                $this->validator->getErrors()
            );
        }
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
