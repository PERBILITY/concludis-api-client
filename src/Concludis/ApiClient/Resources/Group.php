<?php

namespace Concludis\ApiClient\Resources;

use Concludis\ApiClient\Storage\GroupRepository;
use Exception;

class Group extends Element {

    use TranslatableTrait;

    public function __construct(array $data = []) {

        self::setupTranslatable('local_group', ['source_id','id'], [
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
     * @param int $group_key
     * @return bool
     * @throws Exception
     */
    public function save(int $group_key): bool {
        return GroupRepository::save($this, $group_key)
            && $this->saveTranslations();
    }


}