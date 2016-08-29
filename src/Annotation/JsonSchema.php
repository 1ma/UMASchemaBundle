<?php

namespace UMA\SchemaBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class JsonSchema
{
    /**
     * @var string
     *
     * @Required
     */
    public $filename;
}
