<?php

namespace Concludis\ApiClient\Resources;

use Concludis\ApiClient\Storage\I18nRepository;
use Exception;

trait TranslatableTrait {

    public string $locale = 'de_DE';

    /**
     * The translations of i18n-fields.
     * 2-dimensional array with translations for the fields listed in
     * $translation_fields.
     * If translations are not loaded from db the value is NULL.
     * If no translations exist it will be an empty array.
     * If translations exists the array will look like this:
     * <code>
     * [
     *      "de_DE" => ["name" => "Der Name Deutsch", "another_i18n_prop" => "String Deutsch"],
     *      "fr_FR" => ["name" => "Le nom Francaise", "another_i18n_prop" => "String Francaise"]
     * ]
     * </code>
     * @var array|null
     */
    protected ?array $translations = null;

    /**
     * Static definition of i18n fields.
     * @var array
     */
    protected static array $translation_fields = [];

    private static string $translation_model = '';

    /**
     * @var array
     */
    private static array $translation_id_property = [];

    private static string $translation_id_property2 = '';

    /**
     * @param string $model
     * @param array $id_prop
     * @param array $fields
     * @return void
     */
    protected static function setupTranslatable(string $model, array $id_prop, array $fields): void {
        self::$translation_model = $model;
        self::$translation_id_property = $id_prop;
        self::$translation_fields = $fields;
    }

    /**
     * @param array $data
     * @return void
     */
    public function initTranslations(array &$data): void {

        /**
         * Load ID-Values into object props to make "loadTranslations"
         * be able to preload existing translations
         */
        foreach(self::$translation_id_property as $v) {
            if(property_exists($this, $v)) {
                $this->{$v} = $data[$v];
            }
        }

        try {
            if($data['__reset_translations'] ?? false) {
                $this->translations = [];
            } else {
                $this->loadTranslations();
            }
        } catch (Exception) {
            $this->translations = [];
        }

        $_locale = (string)($data['locale'] ?? null);

        if(!empty($_locale)) {
            $this->locale = $_locale;
        }


        foreach(self::$translation_fields as $key) {
            $v = $data[$key] ?? null;
            if (is_array($v)) {
                foreach ($v as $_locale => $value) {
                    $this->translations[$_locale][$key] = $value;
                }
                $data[$key] = (string)$v[$this->locale];
            } else if(is_string($v)) {
                $this->translations[$this->locale][$key] = $v;
                $data[$key] = $v;
            }
        }
    }

    /**
     * Get the assigned translations grouped by language.
     * @return array
     * @throws Exception
     */
    public function getTranslations(): array {
        if($this->translations !== null) {
            // translations have already been fetched
            return $this->translations;
        }

        $this->loadTranslations();

        return $this->translations;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function loadTranslations(): void {

        $this->translations = I18nRepository::fetchTranslationsByKeys(
            self::$translation_model,
            $this->getTranslationKey(),
            self::$translation_fields
        );
    }

    /**
     * @return string
     */
    private function getTranslationKey(): string {
        $foreign_key = '';

        foreach(self::$translation_id_property as $v) {
            if (property_exists($this, $v)) {
                if(!empty($foreign_key)) {
                    $foreign_key .= '::';
                }
                $foreign_key .= $this->{$v};
            }
        }
        return $foreign_key;
    }


    /**
     * @param bool $delete_non_present
     * @return bool
     * @throws Exception
     */
    public function saveTranslations(bool $delete_non_present = false): bool {

        if($this->translations !== null) {
            I18nRepository::saveTranslations(
                self::$translation_model,
                $this->getTranslationKey(),
                $this->translations,
                $delete_non_present
            );
        }
        return true;
    }

    /**
     * @param string $prop
     * @param string $locale
     * @return string
     */
    public function getPropTranslated(string $prop, string $locale): string {
        try {
            $trans = $this->getTranslations();
            return (string)($trans[$locale][$prop] ?? $trans[$this->locale][$prop]);
        } catch (Exception) {
            return '';
        }
    }

    /**
     * @param string $prop
     * @param array|null $locales
     * @return array
     * @throws Exception
     */
    public function getPropTranslations(string $prop, ?array $locales = null): array {
        $trans = $this->getTranslations();

        $locales = $locales ?? array_keys($trans);

        $r = [];
        foreach($locales as $locale) {
            $r[$locale] = $trans[$locale][$prop] ?? $this->{$prop};
        }
        return $r;
    }
}