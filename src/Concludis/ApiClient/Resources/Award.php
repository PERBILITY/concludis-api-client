<?php

namespace Concludis\ApiClient\Resources;


class Award extends Element {

    /**
     * @var string|null
     */
    public ?string $valid_from = null;

    /**
     * @var string|null
     */
    public ?string $valid_until = null;

    /**
     * @var string
     */
    public string $url = '';

    /**
     * @var string
     */
    public string $name = '';

    public function __construct(array $data = []) {

        parent::__construct($data);

        if(array_key_exists('valid_from', $data) && $data['valid_from'] !== null) {
            $this->valid_from = (string)$data['valid_from'];
        }

        if(array_key_exists('valid_until', $data) && $data['valid_until'] !== null) {
            $this->valid_until = (string)$data['valid_until'];
        }

        if(array_key_exists('url', $data)) {
            $this->url = (string)$data['url'];
        }

        if(array_key_exists('name', $data)) {
            $this->name = (string)$data['name'];
        }

    }

}