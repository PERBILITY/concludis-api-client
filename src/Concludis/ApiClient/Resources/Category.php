<?php
/**
 * Created by PhpStorm.
 * User: tmaass
 * Date: 05.09.2018
 * Time: 21:36
 */

namespace Concludis\ApiClient\Resources;


use Concludis\ApiClient\Storage\CategoryRepository;
use Exception;

class Category extends Element {

    use TranslatableTrait;

    /**
     * @var Element[]
     */
    public array $occupations = [];

    public function __construct(array $data = []) {

        self::setupTranslatable('local_category', ['source_id','id'], [
            'name'
        ]);

        $this->initTranslations($data);

        parent::__construct($data);

        $this->occupations = [];
        if(array_key_exists('occupations', $data) && is_array($data['occupations'])) {
            foreach($data['occupations'] as $occupation) {
                if($occupation instanceof Element) {
                    $this->occupations[] = $occupation;
                } else if(is_array($occupation)) {
                    $this->occupations[] = new Element($occupation);
                }
            }
        }

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
        return CategoryRepository::save($this)
            && $this->saveTranslations();
    }
}