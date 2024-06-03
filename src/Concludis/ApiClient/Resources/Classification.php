<?php

namespace Concludis\ApiClient\Resources;

use Concludis\ApiClient\Storage\ClassificationRepository;
use Exception;

class Classification extends Element {

    use TranslatableTrait;

    public function __construct(array $data = []) {

        self::setupTranslatable('local_classification', ['source_id','id'], [
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
        return ClassificationRepository::save($this)
            && $this->saveTranslations();
    }

}