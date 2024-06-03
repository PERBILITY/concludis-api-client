<?php
/**
 * Created by PhpStorm.
 * User: tmaass
 * Date: 05.09.2018
 * Time: 21:36
 */

namespace Concludis\ApiClient\Resources;


class Element {
    /**
     * @var string
     */
    public string $source_id;

    /**
     * @var int
     */
    public int $id;

    /**
     * @var int
     */
    public int $global_id;

    /**
     * @var string
     */
    public string $external_id;

    /**
     * @var string
     */
    public string $name;

    public function __construct(array $data = []) {

        $this->source_id = (string)($data['source_id'] ?? '');

        $this->id = (int)($data['id'] ?? 0);

        $this->global_id = (int)($data['global_id'] ?? 0);

        $this->external_id = (string)($data['external_id'] ?? '');

        $this->name = (string)($data['name'] ?? '');
    }
}