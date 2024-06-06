<?php
/**
 * concludis - Value.php.
 * @author: Alex Agaltsev
 * created on: 13.06.2019
 */

namespace Concludis\ApiClient\Config;


use Concludis\ApiClient\Storage\SetupRepository;
use Exception;
use JsonException;
use stdClass;

class Setupitem {

    /**
     * @var string
     */
    public string $key = '';

    /**
     * @var mixed|null
     */
    public mixed $value;

    public function __construct(array $data = []) {

        if (array_key_exists('key', $data)) {
            $this->key = (string)$data['key'];
        }
        if (array_key_exists('value', $data)) {
            try {
                $this->value = json_decode($data['value'], false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) { }
        }
    }

    public function setValue(stdClass $value): void {
        $this->value = $value;
    }

    /**
     * @param string $value
     * @return void
     * @throws Exception
     */
    public function setValueFromJsonString(string $value): void {
        $this->value = json_decode($value, false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    public function getValue(): stdClass {

        if($this->value !== null){
            return $this->value;
        }

        $value = SetupRepository::fetchValue($this->key);

        if($value === null) {
            $this->value = null;
        } else {
            $this->setValueFromJsonString($value);
        }

        return $this->value;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save(): bool {
        return SetupRepository::saveSetupitem($this);
    }

}