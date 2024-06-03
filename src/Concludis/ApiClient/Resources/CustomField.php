<?php
/**
 * @package:    concludis
 * @file:       CustomField.php
 * @version:    1.0.0
 *
 * @author:     concludis <aa@concludis.de>
 * @copyright:  concludis GmbH © 2007-2022
 * @created:    01.12.22
 * @link:       https://www.concludis.com
 */

namespace Concludis\ApiClient\Resources;

use RuntimeException;

class CustomField {

    public const INPUT_TYPE_TEXT = 1;
    public const INPUT_TYPE_TEXTAREA = 2;
    public const INPUT_TYPE_SELECT_ONE = 10;
    public const INPUT_TYPE_SELECT_MULTIPLE = 20;

    /**
     * @var string
     */
    public string $source_id = '';

    /**
     * @var int
     */
    public int $id = 0;

    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var int
     */
    public int $type;

    /**
     * @var mixed
     */
    public $value;

    public function __construct(array $data = []) {


        if(array_key_exists('source_id', $data)) {
            $this->source_id = (string)$data['source_id'];
        }

        if(empty($this->source_id)) {
            throw new RuntimeException('source_id cannot be empty');
        }

        if(array_key_exists('id', $data)) {
            $this->id = (int)$data['id'];
        }

        if(array_key_exists('name', $data)) {
            $this->name = (string)$data['name'];
        }

        if(array_key_exists('type', $data)) {
            $this->type = (int)$data['type'];
        }

        if (array_key_exists('value', $data)) {
            $this->value = $data['value'];
        }

    }

}