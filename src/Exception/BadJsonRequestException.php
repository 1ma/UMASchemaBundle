<?php

namespace UMA\SchemaBundle\Exception;

class BadJsonRequestException extends \RuntimeException
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $schema;

    /**
     * @var array
     */
    private $errors;

    /**
     * @param \stdClass|null $data
     * @param \stdClass      $schema
     * @param array          $errors
     */
    public function __construct($data, \stdClass $schema, array $errors)
    {
        $this->data = json_decode(json_encode($data), true);
        $this->schema = json_decode(json_encode($schema), true);
        $this->errors = $errors;

        // The 'id' field contains the local path to the schema file. It is
        // automatically added by the json-schema library when the schema
        // is first retrieved and loaded into memory.
        unset($this->schema['id']);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
