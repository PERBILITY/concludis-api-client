<?php

namespace Concludis\ApiClient\Resources;

use Concludis\ApiClient\Storage\BoardRepository;
use Exception;

class Board extends Element {

    public ?array $extended_props = null;

    public function __construct(array $data = []) {

        parent::__construct($data);

        if(array_key_exists('extended_props', $data)) {
            if(is_string($data['extended_props'])) {
                try {
                    $meta = (array)json_decode($data['extended_props'], true, 512, JSON_THROW_ON_ERROR);
                    $this->extended_props = $meta;
                } catch (Exception) { }
            } else if(is_array($data['extended_props'])) {
                $this->extended_props = $data['extended_props'];
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function save(): void {
        BoardRepository::save($this);
    }

}