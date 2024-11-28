<?php
/**
 * Created by PhpStorm.
 * User: tmaass
 * Date: 05.09.2018
 * Time: 21:49
 */

namespace Concludis\ApiClient\Resources;


class JobadContainer {

    /**
     * @var string
     */
    public string $source_id;

    /**
     * @var int
     */
    public int $project_id;

    /**
     * @var int
     */
    public int $datafield_id;

    /**
     * @var string
     */
    public string $locale = 'de_DE';

    /**
     * @var string
     */
    public string $type = '';

    /**
     * @var int
     */
    public int $sortorder = 0;

    /**
     * @var string
     */
    public string $container_type;

    /**
     * @var string
     */
    public string $content_external;

    /**
     * @var string
     */
    public string $content_internal;


    public function __construct(array $data = []) {

        $this->source_id = (string)($data['source_id'] ?? '');

        $this->project_id = (int)($data['project_id'] ?? 0);

        $this->datafield_id = (int)($data['datafield_id'] ?? 0);

        $this->locale = (string)($data['locale'] ?? $this->locale);

        $this->type = (string)($data['type'] ?? '');

        $this->sortorder = (int)($data['sortorder'] ?? 0);

        $this->container_type = (string)($data['container_type'] ?? '');

        $this->content_external = (string)($data['content_external'] ?? '');

        $this->content_internal = (string)($data['content_internal'] ?? '');
    }

    public function createValueChecksum(): string {
        return sha1($this->type . '::' . $this->container_type . '::' . $this->sortorder . '::' . sha1($this->content_external) . '::' . sha1($this->content_internal));
    }

}