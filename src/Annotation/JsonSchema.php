<?php

namespace UMA\SchemaBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class JsonSchema
{
    /**
     * Name of the json schema file. It must be present
     * in one of the directories listed under uma_schema.paths
     * in the project configuration file (usually app/config.yml).
     *
     * @example foo.json
     *
     * @var string
     *
     * @Required
     */
    public $filename;

    /**
     * When $strict is true, incoming requests will be
     * forced to honor $contentType.
     *
     * @var string
     */
    public $contentType = 'application/json';

    /**
     * Whether to return an HTTP 400 response when a
     * request does not have the Content-Type header or
     * when it does not equal $contentType.
     *
     * @var bool
     */
    public $strict = true;
}
