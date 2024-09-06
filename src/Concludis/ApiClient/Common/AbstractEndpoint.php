<?php


namespace Concludis\ApiClient\Common;


abstract class AbstractEndpoint {

    /**
     * @var AbstractClient
     */
    protected AbstractClient $client;

    protected string $locale = 'de_DE';

    public function __construct(AbstractClient $client) {
        $this->client = $client;
    }

    /**
     * @return AbstractClient
     */
    public function client(): AbstractClient {
        return $this->client;
    }

    /**
     * @param string $locale
     * @return void
     */
    public function setLocale(string $locale): void {
        $this->locale = $locale;
    }
}