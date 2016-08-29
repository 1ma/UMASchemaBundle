<?php

namespace UMA\Tests\SchemaBundle;

use Symfony\Component\BrowserKit\Client;

class FullSpectrumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $client;

    protected function setUp()
    {
        $kernel = new \TestKernel('test', true);
        $kernel->boot();

        $this->client = $kernel->getContainer()->get('test.client');

        parent::setUp();
    }

    /**
     * @test
     */
    public function validRequest()
    {
        $this->makeAndCheckRequest(200, '{"name": "John Doe", "age": 30}');
    }

    /**
     * @test
     * @dataProvider invalidRequestsProvider
     *
     * @param string $invalidPayload
     */
    public function invalidRequests($invalidPayload)
    {
        $this->makeAndCheckRequest(400, $invalidPayload);
    }

    public function invalidRequestsProvider()
    {
        return [
            'empty body' => [''],
            'no JSON' => ['huehue'],
            'empty JSON object' => ['{}'],
            'missing field' => ['{"name": "John Doe"}'],
            'invalid field type' => ['{"name": null, "age": 30}'],
            'invalid field value' => ['{"name": "Old Doe", "age": 99}'],
        ];
    }

    private function makeAndCheckRequest($expectedStatusCode, $payload)
    {
        $this->client
            ->request('GET', '/', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertSame($expectedStatusCode, $this->client->getInternalResponse()->getStatus());
    }
}
