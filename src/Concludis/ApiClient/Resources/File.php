<?php

namespace Concludis\ApiClient\Resources;

class File {

    /**
     * @var int
     */
    public int $id;

    /**
     * @var int|null
     */
    public ?int $candidate_id = null;

    /**
     * @var string
     */
    public string $name = '';


    /**
     * @var string
     */
    public string $mime_type = '';

    /**
     * @var int
     */
    public int $local_file_type = 0;

    /**
     * @var int
     */
    public int $global_file_type = 0;

    /**
     * @var int
     */
    public int $size = 0;

    /**
     * @var int
     */
    public int $mktime = 0;

    /**
     * @var string
     */
    public string $checksum = '';

    /**
     * @var string|null
     */
    public ?string $content = null;

    public function __construct(array $data = []) {

        $this->id = (int)($data['id'] ?? 0);
        $this->candidate_id = (int)($data['candidate_id'] ?? 0);
        $this->name = (string)($data['name'] ?? '');
        $this->mime_type = (string)($data['mime_type'] ?? '');
        $this->local_file_type = (int)($data['local_file_type'] ?? 0);
        $this->global_file_type = (int)($data['global_file_type'] ?? 0);
        $this->size = (int)($data['size'] ?? 0);
        $this->mktime = (int)($data['mktime'] ?? 0);
        $this->checksum = (string)($data['content'] ?? '');

        if(array_key_exists('content', $data)) {
            $this->content = (string)($data['content'] ?? '');
        }

    }
}