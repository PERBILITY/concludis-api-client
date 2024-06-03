<?php


namespace Concludis\ApiClient\Config;


use Concludis\ApiClient\Common\AbstractClient;
use Concludis\ApiClient\V1\Client\Client AS V1Client;
use Concludis\ApiClient\V2\Client\Client AS V2Client;
use Exception;
use JsonException;
use RuntimeException;

class Source {

    public const API_VERSION_V1 = 'V1';
    public const API_VERSION_V2 = 'V2';
    public const API_GENERIC = 'generic';


    /**
     * @var string
     */
    public string $id = '';

    /**
     * @var string
     */
    public string $baseurl = '';

    /**
     * @var array
     */
    public array $filters = [];

    /**
     * @var string
     */
    public string $username = '';

    /**
     * @var string
     */
    public string $password = '';

    /**
     * @var string
     */
    public string $api = self::API_VERSION_V1;

    /**
     * @var bool
     */
    public bool $ssl_verify_peer = true;

    /**
     * @var array
     */
    public array $options = [];

    /**
     * @var AbstractClient|null
     */
    private ?AbstractClient $client = null;

    public function __construct(array $data = []) {

        if(array_key_exists('id', $data)) {
            $this->id = (string)$data['id'];
        }
        if(array_key_exists('baseurl', $data)) {
            $this->baseurl = (string)$data['baseurl'];
        }
        if(array_key_exists('filters', $data)) {
            if(is_array($data['filters'])) {
                $this->filters = $data['filters'];
            } else if (is_string($data['filters'])) {
                try {
                    $this->filters = (array)json_decode($data['filters'], true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException) {}
            }
        }
        if(array_key_exists('username', $data)) {
            $this->username = (string)$data['username'];
        }
        if(array_key_exists('password', $data)) {
            $this->password = (string)$data['password'];
        }
        if(array_key_exists('api', $data)) {
            if(is_string($data['api'])) {
                $this->api = $data['api'];
            } elseif ($data['api'] instanceof AbstractClient) {
                $this->api = self::API_GENERIC;
                $this->client = $data['api'];
                $this->client->setSource($this);
            }
        }
        if(array_key_exists('ssl_verify_peer', $data)) {
            $this->ssl_verify_peer = (bool)$data['ssl_verify_peer'];
        }
        if(array_key_exists('options', $data)) {
            if(is_array($data['options'])) {
                $this->options = $data['options'];
            } else if (is_string($data['options'])) {
                try {
                    $this->options = (array)json_decode($data['options'], true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException) {}
            }
        }
    }

    /**
     * @throws Exception
     * @return AbstractClient
     */
    public function client(): AbstractClient {

        if($this->client !== null) {
            return $this->client;
        }

        if($this->api === self::API_VERSION_V1) {
            $this->client = new V1Client($this);
        }

        if($this->api === self::API_VERSION_V2) {
            $this->client = new V2Client($this);
        }

        if($this->client === null) {
            throw new RuntimeException('Client not found. Maybe you speciefied an invalid API Version in your Source.');
        }

        return $this->client;
    }

}