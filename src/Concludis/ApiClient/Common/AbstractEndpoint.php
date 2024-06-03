<?php


namespace Concludis\ApiClient\Common;


abstract class AbstractEndpoint {

    /**
     * @var AbstractClient
     */
    protected AbstractClient $client;

    public function __construct(AbstractClient $client) {
        $this->client = $client;
    }

    /**
     * @return AbstractClient
     */
    public function client(): AbstractClient {
        return $this->client;
    }
}