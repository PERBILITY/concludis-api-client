<?php

namespace Concludis\ApiClient\Resources;

use Concludis\ApiClient\Storage\ScheduleRepository;
use Exception;

class Schedule extends Element {

    public static array $merge_map = [
        12 => [1,8]
    ];

    use TranslatableTrait;

    public function __construct(array $data = []) {

        self::setupTranslatable('local_schedule', ['source_id','id'], [
            'name'
        ]);

        $this->initTranslations($data);

        parent::__construct($data);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName(?string $locale = null): string {
        $alocale = ($locale ?? $this->locale);
        return $this->getPropTranslated('name', $alocale);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save(): bool {
        return ScheduleRepository::save($this)
            && $this->saveTranslations();
    }
}