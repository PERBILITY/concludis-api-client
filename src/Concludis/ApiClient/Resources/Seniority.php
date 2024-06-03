<?php

namespace Concludis\ApiClient\Resources;

use Concludis\ApiClient\Storage\SeniorityRepository;
use Exception;

class Seniority extends Element {

    use TranslatableTrait;

    public function __construct(array $data = []) {

        self::setupTranslatable('local_seniority', ['source_id','id'], [
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
        return SeniorityRepository::save($this)
            && $this->saveTranslations();
    }

}