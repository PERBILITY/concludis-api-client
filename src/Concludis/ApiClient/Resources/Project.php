<?php

namespace Concludis\ApiClient\Resources;


use Concludis\ApiClient\Storage\ProjectRepository;
use Concludis\ApiClient\Util\DateUtil;
use DateTime;
use Exception;
use HTMLPurifier;
use HTMLPurifier_Config;
use JsonException;

class Project {

    public const STATUS_DRAFT = 1;
    public const STATUS_ACTIVE = 2;
    public const STATUS_INACTIVE = 3;

    public const APPLY_TYPE_PORTFOLIO = 'M';
    public const APPLY_TYPE_EMAIL = 'E';
    public const APPLY_TYPE_CALLBACK = 'C';
    public const APPLY_TYPE_SOCIALAPPLY = 'X';
    public const APPLY_TYPE_FORMAPPLY = 'F';
    public const APPLY_TYPE_PITCHYOU = 'P';
    public const APPLY_TYPE_TALKNJOB = 'T';

    public const REMOTE_TYPE_NONE = 0;
    public const REMOTE_TYPE_PARTIAL_HOMEOFFICE = 1;
    public const REMOTE_TYPE_COMPLETELY_HOMEOFFICE = 2;
    public const REMOTE_TYPE_REMOTE_WORK = 3;

    /**
     * Parttime Position (Teilzeit)
     */
    public const TAG_FULLTIME = 'fulltime';
    /**
     * Fulltime Position (Vollzeit)
     */
    public const TAG_PARTTIME = 'parttime';
    /**
     * Temporary Position (Befristet)
     */
    public const TAG_TEMPORARY = 'temporary';
    /**
     * Internship (Praktikum)
     */
    public const TAG_INTERN = 'intern';

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
    public string $gid = '';

    /**
     * @var int
     */
    public int $status = 0;

    /**
     * @var bool|null
     */
    public ?bool $is_published_public = null;

    /**
     * @var bool|null
     */
    public ?bool $is_published_internal = null;

    /**
     * @var bool|null
     */
    public ?bool $is_listed = null;

    /**
     * Ausbildung
     * @var bool|null
     */
    public ?bool $is_apprentice = null;

    /**
     * Initiativbewerbung
     * @var bool|null
     */
    public ?bool $is_unsolicited_application = null;

    /**
     * @var array
     */
    public array $apply_types = [];

    /**
     * @var Element[]
     */
    public array $family = [];

    /**
     * @var Award[]
     */
    public array $awards = [];

    /**
     * @var Board[]
     */
    public array $board = [];

    /**
     * @var Group[]
     */
    public array $group1 = [];

    /**
     * @var Group[]
     */
    public array $group2 = [];

    /**
     * @var Group[]
     */
    public array $group3 = [];

    /**
     * @var CustomField[]
     */
    public array $custom_fields = [];

    /**
     * @var Location[]
     */
    public array $locations = [];

    /**
     * @var Person[]
     */
    public array $manager = [];

    /**
     * @var Person|null
     */
    public ?Person $contact = null;

    /**
     * @var Person|null
     */
    public ?Person $contact_internal = null;

    public string $contact_custom_email = '';

    public string $contact_custom_phone = '';

    /**
     * @var Company|null
     */
    public ?Company $company = null;

    /**
     * @var Classification|null
     */
    public ?Classification $classification = null;

    /**
     * @var Seniority|null
     */
    public ?Seniority $seniority = null;

    /**
     * @var Schedule|null
     */
    public ?Schedule $schedule = null;

    /**
     * @var Element|null
     */
    public ?Element $years_of_experience = null;

    /**
     * @var Category|null
     */
    public ?Category $category = null;

    /**
     * @var Element[]
     */
    public array $tags = [];

    /**
     * @var JobadContainer[]|null
     */
    private ?array $jobad_containers = null;

    /**
     * @var string
     */
    public string $jobad_html = '';

    /**
     * @var string
     */
    public string $jobad_url = '';

    /**
     * @var string
     */
    public string $jobad_url_internal = '';

    /**
     * @var string
     */
    public string $apply_url = '';

    /**
     * @var string
     */
    public string $apply_url_internal = '';

    /**
     * @var string
     */
    public string $pdf_url = '';

    /**
     * @var string
     */
    public string $pdf_url_internal = '';

    /**
     * @var string|null
     */
    public ?string $lastupdate = null;

    /**
     * @var string|null
     */
    public ?string $created_at = null;

    /**
     * @var string|null
     */
    public ?string $date_from_public = null;

    /**
     * @var string|null
     */
    public ?string $date_from_internal = null;

    /**
     * @var string Format Y-m-d
     */
    public string $earlystartdate = '0000-00-00';

    /**
     * @var bool
     */
    public bool $ba_is_published = false;

    /**
     * @var string
     */
    public string $employerbrand = '';

    /**
     * @var int|null
     */
    public ?int $remotetype = null;

    /**
     * @var Salary|null
     */
    public ?Salary $salary = null;


    /**
     * @var int|null
     */
    public ?int $indeed_enabled = null;

    /**
     * @var array
     */
    public array $indeed_extended = [];

    /**
     * @var PositionInformation
     */
    public PositionInformation $position_information;

    public array $extended_props = [];

    use TranslatableTrait;

    /**
     * @param array $data
     */
    public function __construct(array $data = []) {

        self::setupTranslatable('project', ['source_id','id'], [
            'title', 'position_title', 'teaser', 'full_markup', 'tracking_code', 'google_for_jobs_code'
        ]);

        $this->initTranslations($data);

        $this->source_id = (string)($data['source_id'] ?? '');

        $this->id = (int)($data['id'] ?? 0);

        $this->gid = (string)($data['gid'] ?? 0);

        $this->status = (int)($data['status'] ?? 0);

        if(array_key_exists('is_published_public', $data)) {
            $this->is_published_public = (bool)$data['is_published_public'];
        }
        if(array_key_exists('is_published_internal', $data)) {
            $this->is_published_internal = (bool)$data['is_published_internal'];
        }
        if (array_key_exists('is_listed', $data)) {
            $this->is_listed = (bool)$data['is_listed'];
        }
        if (array_key_exists('is_apprentice', $data)) {
            $this->is_apprentice = (bool)$data['is_apprentice'];
        }
        if (array_key_exists('is_unsolicited_application', $data)) {
            $this->is_unsolicited_application = (bool)$data['is_unsolicited_application'];
        }

        if (array_key_exists('apply_types', $data) && is_array($data['apply_types'])) {
            $this->apply_types = $data['apply_types'];
        }
        if (array_key_exists('family', $data) && is_array($data['family'])) {
            $this->family = [];
            foreach($data['family'] as $v) {
                if($v instanceof Element) {
                    $this->family[] = $v;
                } else if(is_array($v)) {
                    $this->family[] = new Element($v);
                }
            }
        }
        if (array_key_exists('awards', $data) && is_array($data['awards'])) {
            $this->awards = [];
            foreach($data['awards'] as $v) {
                if($v instanceof Award) {
                    $this->awards[] = $v;
                } else if(is_array($v)) {
                    $this->awards[] = new Award($v);
                }
            }
        }
        if (array_key_exists('boards', $data) && is_array($data['boards'])) {
            $this->board = [];
            foreach($data['boards'] as $v) {
                if($v instanceof Board) {
                    $this->board[] = $v;
                } else if(is_array($v)) {
                    $this->board[] = new Board($v);
                }
            }
        }
        if (array_key_exists('group1', $data) && is_array($data['group1'])) {
            $this->group1 = [];
            foreach($data['group1'] as $v) {
                if($v instanceof Group) {
                    $this->group1[] = $v;
                } else if(is_array($v)) {
                    $this->group1[] = new Group($v);
                }
            }
        }
        if (array_key_exists('group2', $data) && is_array($data['group2'])) {
            $this->group2 = [];
            foreach($data['group2'] as $v) {
                if($v instanceof Group) {
                    $this->group2[] = $v;
                } else if(is_array($v)) {
                    $this->group2[] = new Group($v);
                }
            }
        }
        if (array_key_exists('group3', $data) && is_array($data['group3'])) {
            $this->group3 = [];
            foreach($data['group3'] as $v) {
                if($v instanceof Group) {
                    $this->group3[] = $v;
                } else if(is_array($v)) {
                    $this->group3[] = new Group($v);
                }
            }
        }
        if (array_key_exists('custom_fields', $data) && is_array($data['custom_fields'])) {
            $this->custom_fields = [];
            foreach($data['custom_fields'] as $v) {
                if($v instanceof CustomField) {
                    $this->custom_fields[] = $v;
                } else if(is_array($v)) {
                    $this->custom_fields[] = new CustomField($v);
                }
            }
        }
        if (array_key_exists('locations', $data) && is_array($data['locations'])) {
            $this->locations = [];
            foreach($data['locations'] as $v) {
                if($v instanceof Location) {
                    $this->locations[] = $v;
                } else if(is_array($v)) {
                    $this->locations[] = new Location($v);
                }
            }
        }
        if (array_key_exists('manager', $data) && is_array($data['manager'])) {
            $this->manager = [];
            foreach($data['manager'] as $v) {
                if ($v instanceof Person) {
                    $this->manager[] = $v;
                } else if(is_array($v)) {
                    $this->manager[] = new Person($v);
                }
            }
        }
        if (array_key_exists('contact', $data)) {
            if($data['contact'] instanceof Person) {
                $this->contact = $data['contact'];
            } else if(is_array($data['contact'])) {
                $this->contact = new Person($data['contact']);
            }
        }
        if (array_key_exists('contact_internal', $data)) {
            if($data['contact_internal'] instanceof Person) {
                $this->contact_internal = $data['contact_internal'];
            } else if(is_array($data['contact_internal'])) {
                $this->contact_internal = new Person($data['contact_internal']);
            }
        }

        $this->contact_custom_email = (string)($data['contact_custom_email'] ?? '');

        $this->contact_custom_phone = (string)($data['contact_custom_phone'] ?? '');

        if (array_key_exists('company', $data)) {
            if($data['company'] instanceof Company) {
                $this->company = $data['company'];
            } else if(is_array($data['company'])){
                $this->company = new Company($data['company']);
            }
        }

        if (array_key_exists('classification', $data)) {
            if($data['classification'] instanceof Classification) {
                $this->classification = $data['classification'];
            } else if(is_array($data['classification'])) {
                $this->classification = new Classification($data['classification']);
            }
        }
        if (array_key_exists('seniority', $data)) {
            if($data['seniority'] instanceof Seniority) {
                $this->seniority = $data['seniority'];
            } else if(is_array($data['seniority'])) {
                $this->seniority = new Seniority($data['seniority']);
            }
        }
        if (array_key_exists('schedule', $data)) {
            if($data['schedule'] instanceof Schedule) {
                $this->schedule = $data['schedule'];
            } else if(is_array($data['schedule'])) {
                $this->schedule = new Schedule($data['schedule']);
            }
        }
        if (array_key_exists('years_of_experience', $data)) {
            if($data['years_of_experience'] instanceof Element) {
                $this->years_of_experience = $data['years_of_experience'];
            } else if(is_array($data['years_of_experience'])) {
                $this->years_of_experience = new Element($data['years_of_experience']);
            }
        }
        if (array_key_exists('category', $data)) {
            if($data['category'] instanceof Category) {
                $this->category = $data['category'];
            } else if(is_array($data['category'])) {
                $this->category = new Category($data['category']);
            }
        }
        if (array_key_exists('tags', $data) && is_array($data['tags'])) {
            $this->tags = [];
            foreach($data['tags'] as $tag) {
                if($tag instanceof Element) {
                    $this->tags[] = $tag;
                } else if(is_array($tag)) {
                    $this->tags[] = new Element($tag);
                }
            }
        }
        if (array_key_exists('jobad_html', $data)) {
            $this->jobad_html = (string)$data['jobad_html'];
        }
        if (array_key_exists('jobad_containers', $data) && is_array($data['jobad_containers'])) {
            $this->jobad_containers = [];
            foreach($data['jobad_containers'] as $container) {
                if($container instanceof JobadContainer) {
                    $this->jobad_containers[] = $container;
                } else if(is_array($container)) {
                    $this->jobad_containers[] = new JobadContainer($container);
                }
            }
        }
        if (array_key_exists('jobad_url', $data)) {
            $this->jobad_url = (string)$data['jobad_url'];
        }
        if (array_key_exists('jobad_url_internal', $data)) {
            $this->jobad_url_internal = (string)$data['jobad_url_internal'];
        }
        if (array_key_exists('apply_url', $data)) {
            $this->apply_url = (string)$data['apply_url'];
        }
        if (array_key_exists('apply_url_internal', $data)) {
            $this->apply_url_internal = (string)$data['apply_url_internal'];
        }
        if (array_key_exists('pdf_url', $data)) {
            $this->pdf_url = (string)$data['pdf_url'];
        }
        if (array_key_exists('pdf_url_internal', $data)) {
            $this->pdf_url_internal = (string)$data['pdf_url_internal'];
        }
        if (array_key_exists('lastupdate', $data)) {
            $this->lastupdate = (string)$data['lastupdate'];
        }
        if (array_key_exists('created_at', $data)) {
            $this->created_at = (string)$data['created_at'];
        }
        if (array_key_exists('date_from_public', $data)) {
            $this->date_from_public = empty($data['date_from_public']) ? null : (string)$data['date_from_public'];
        } else if(array_key_exists('date_from', $data)) { // backward comptibility
            $this->date_from_public = empty($data['date_from']) ? null : (string)$data['date_from'];
        }
        if(!$this->is_published_public) {
            $this->date_from_public = null;
        }

        if (array_key_exists('date_from_internal', $data)) {
            $this->date_from_internal = empty($data['date_from_internal']) ? null : (string)$data['date_from_internal'];
        }
        if(!$this->is_published_internal) {
            $this->date_from_internal = null;
        }

        if (array_key_exists('earlystartdate', $data)) {
            $this->earlystartdate = '0000-00-00';
            $dt = DateUtil::parseMysqlDate((string)$data['earlystartdate']);
            if ($dt !== null) {
                $this->earlystartdate = $dt->format('Y-m-d');
            }
            unset($dt);
        }
        if (array_key_exists('ba_is_published', $data)) {
            $this->ba_is_published = (bool)$data['ba_is_published'];
        }

        $this->position_information =  new PositionInformation();
        if (array_key_exists('position_information', $data)) {
            $pi = $data['position_information'];
            if($pi instanceof PositionInformation) {
                $this->position_information = $pi;
            } else if(is_array($pi)) {
                $this->position_information = new PositionInformation($pi);
            }
        }
        if (array_key_exists('extended_props', $data)) {
            if(is_array($data['extended_props'])) {
                $this->extended_props = $data['extended_props'];
            } else if(is_string($data['extended_props'])) {
                try {
                    $this->extended_props = (array)json_decode($data['extended_props'], false, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                }
            }
        }


        if(array_key_exists('employerbrand', $data) && is_string($data['employerbrand'])) {
            $this->employerbrand = $data['employerbrand'];
        }

        if(array_key_exists('remotetype', $data) && is_int($data['remotetype'])) {
            $this->remotetype = $data['remotetype'];
        }

        if(array_key_exists('remotetype', $data) && is_int($data['remotetype'])) {
            $this->remotetype = $data['remotetype'];
        }

        if(array_key_exists('salary', $data)) {
            if($data['salary'] instanceof Salary) {
                $this->salary = $data['salary'];
            } else if(is_array($data['salary'])) {
                $this->salary = Salary::fromArray($data['salary']);
            }
        }

        if(array_key_exists('indeed_enabled', $data) && is_int($data['indeed_enabled'])) {
            $this->indeed_enabled = $data['indeed_enabled'];
        }

        if(array_key_exists('indeed_extended', $data) && is_array($data['indeed_enabled'])) {
            $this->indeed_extended = $data['indeed_extended'];
        }

    }

    public function getDateFromPublic(): ?DateTime {
        if(empty($this->date_from_public)) {
            return null;
        }
        return DateUtil::parseMysqlDate($this->date_from_public);
    }

    public function getDateFromInternal(): ?DateTime {
        if(empty($this->date_from_internal)) {
            return null;
        }
        return DateUtil::parseMysqlDate($this->date_from_internal);
    }

    public function gpid(): string {
        return $this->source_id . 'P' . $this->id;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getTitle(?string $locale = null): string {
        $alocale = ($locale ?? $this->locale);
        return $this->getPropTranslated('title', $alocale);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getPositionTitle(?string $locale = null): string {
        $alocale = ($locale ?? $this->locale);
        return $this->getPropTranslated('position_title', $alocale);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getTeaser(?string $locale = null): string {
        $alocale = ($locale ?? $this->locale);
        return $this->getPropTranslated('teaser', $alocale);
    }

    /**
     * returns an array of TAG_* strings depending on job categorization
     * @return array
     */
    public function getTags(): array {

        $tags = [];
        if($this->schedule !== null) {
            /**
             * Schedule Mapping
             */
            $schedule_global_id_tag_mapping = [
                1 => [self::TAG_FULLTIME],
                2 => [self::TAG_PARTTIME],
                5 => [self::TAG_PARTTIME],
                6 => [self::TAG_PARTTIME],
                7 => [self::TAG_PARTTIME],
                8 => [self::TAG_PARTTIME],
                12 => [self::TAG_FULLTIME, self::TAG_PARTTIME],
            ];
            if (array_key_exists($this->schedule->global_id, $schedule_global_id_tag_mapping)) {
                $target_tags = $schedule_global_id_tag_mapping[$this->schedule->global_id];
                foreach($target_tags as $tag) {
                    $tags[] = $tag;
                }
            }
        }

        /**
         * befristet (temporary)
         */
        if($this->position_information->position_description->duration_temporary_or_regular === 1) {
            $tags[] = self::TAG_TEMPORARY;
        }

        /**
         * praktikum
         */
        if($this->classification?->global_id === 5) {
            $tags[] = self::TAG_INTERN;
        }

        return $tags;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getFullMarkup(?string $locale = null): string {
        $alocale = ($locale ?? $this->locale);
        return $this->getPropTranslated('full_markup', $alocale);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getTrackingCode(?string $locale = null): string {
        $alocale = ($locale ?? $this->locale);
        return $this->getPropTranslated('tracking_code', $alocale);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getGoogleForJobsCode(?string $locale = null): string {
        $alocale = ($locale ?? $this->locale);
        return $this->getPropTranslated('google_for_jobs_code', $alocale);
    }

    /**
     * @param int $location_id
     * @return Location|null
     */
    public function getLocationById(int $location_id): ?Location {
        foreach($this->locations as $loc) {
            if($loc->id === $location_id) {
                return $loc;
            }
        }
        return null;
    }

    /**
     * @param float $lat
     * @param float $lon
     * @param int $max_distance
     * @return array
     */
    public function getClosestLocations(float $lat, float $lon, int $max_distance = 0): array {

        $loc_distances = [];

        foreach($this->locations as $loc) {
            $distance = $loc->distanceTo($lat, $lon);
            if($distance === null || ($max_distance !== 0 && $distance > $max_distance)) {
                continue;
            }
            $loc_distances[] = [
                'location' => $loc,
                'distance' => $distance
            ];
        }

        usort($loc_distances, static function ($a, $b) {
            if ($a['distance'] === $b['distance']) {
                return 0;
            }
            return ($a['distance'] < $b['distance']) ? -1 : 1;
        });

        return $loc_distances;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function loadJobadContainers(): void {
        $this->jobad_containers = ProjectRepository::fetchJobadContainers($this);
    }

    /**
     * @param bool $load
     * @return JobadContainer[]|null
     * @throws Exception
     */
    public function getJobadContainers(bool $load = true): ?array {
        if($this->jobad_containers === null && $load) {
            $this->loadJobadContainers();
        }
        return $this->jobad_containers;
    }

    /**
     * @param string|null $locale
     * @return JobadContainer[]
     * @throws Exception
     */
    public function getTranslatedJobadContainers(?string $locale = null): array {

        $locale = $locale ?? $this->locale;

        $containers = $this->getJobadContainers();

        $data = [];
        foreach($containers as $jc) {
            if ($jc->locale === $locale) {
                $data[] = $jc;
            }
        }

        if(!empty($data) || $locale === $this->locale) {
            return $data;
        }

        // fallback to fallback-locale
        return $this->getTranslatedJobadContainers($this->locale);
    }

    /**
     * @param string|null $type
     * @param string|null $locale
     * @return string
     * @throws Exception
     */
    public function getTranslatedContainerByType(?string $type, ?string $locale = null): string {

        $locale = $locale ?? $this->locale;

        $data = [];
        $containers = $this->getTranslatedJobadContainers($locale);

        foreach ($containers as $jc) {

            if ($jc->type !== $type) {
                continue;
            }

            if ($jc->locale === $locale) {
                $data[] = $jc->content_external;
            }

        }
        return implode('', $data);
    }

    /**
     * @param string|null $locale
     * @param bool $internal
     * @return string
     * @throws Exception
     */
    public function getJobadMarkupFromContainers(?string $locale = null, bool $internal = false): string {

        $containers = $this->getTranslatedJobadContainers($locale);

        $all_containers = [];
        foreach ($containers as $jc) {
            $type = $jc->type;

            $content = $jc->content_external;

            if($internal && !empty($jc->content_internal)) {
                $content = $jc->content_internal;
            }

            if (empty($content)) {
                continue;
            }

            if ($type === 'ptitel') {
                $content = '<h1>' . strip_tags($content, 'span') . '</h1>';
            }

            $all_containers[] = $content;
        }

        return implode("\n", $all_containers);
    }

    /**
     * @param string|null $locale
     * @param bool $internal
     * @return string
     * @throws Exception
     */
    public function getPurifiedJobadMarkupFromContainers(?string $locale = null, bool $internal = false): string {

        $config = HTMLPurifier_Config::createDefault();
        $cacheDirectory = sys_get_temp_dir();

        $config->set('Cache.SerializerPath', $cacheDirectory);
        $config->set('AutoFormat.RemoveEmpty', true); // remove empty elements
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict'); // valid XML output (?)
        $config->set('HTML.AllowedElements', array('p', 'br', 'ul', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'strong', 'em', 'b', 'i'));
        $config->set('CSS.AllowedProperties', array()); // remove all CSS

        return (new HTMLPurifier($config))->purify($this->getJobadMarkupFromContainers($locale, $internal));
    }

    /**
     * Returns all non-empty and visible jobad container contents grouped by its type.
     *
     * @param string|null $locale
     * @param bool $internal
     * @return array
     * @throws Exception
     */
    public function getMergedPurifiedJobadContainers(?string $locale = null, bool $internal = false): array {

        $jc_group = [];
        $merged_jc = [];
        $containers = $this->getTranslatedJobadContainers($locale);

        foreach ($containers as $jc) {
            $type = $jc->type;

            $content = $jc->content_external;

            if($internal && !empty($jc->content_internal)) {
                $content = $jc->content_internal;
            }

            if (empty($content)) {
                continue;
            }

            if ($type === 'company_description') {
                $jc_group['company_description'][] = $content;
            }

            if ($type === 'header_image') {
                $jc_group['header_image'] = $content;
            }

            if ($type === 'ptitel') {
                $jc_group['ptitle'][] = $content;
            }

            if ($type === 'pintro2') {
                $jc_group['pintro2'][] = $content;
            }

            if ($type === 'pintro') {
                $jc_group['pintro'][] = $content;
            }

            if ($type === 'ptask') {
                $jc_group['ptask'][] = $content;
            }

            if ($type === 'pquali') {
                $jc_group['pquali'][] = $content;
            }

            if ($type === 'pfeature') {
                $jc_group['pfeature'][] = $content;
            }

            if ($type === 'pclosing') {
                $jc_group['pclosing'][] = $content;
            }

            if ($type === 'contact') {
                $jc_group['contact'][] = $content;
            }

            if ($type === 'video') {
                $jc_group['video'][] = $content;
            }
        }

        foreach($jc_group as $k => $v) {
            if(is_array($v)) {
                $merged_jc[$k] = implode('', $v);
                continue;
            }
            if(is_string($v)) {
                $merged_jc[$k] = $v;
            }
        }

        return $merged_jc;
    }

    /**
     * @param array $board_ids
     * @return bool
     */
    public function hasBoards(array $board_ids): bool {
        $ids = [];
        foreach($this->board as $b) {
            $ids[] = $b->id;
        }
        return !empty(array_intersect($board_ids, $ids));
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save(): bool {
        return ProjectRepository::save($this) && $this->saveTranslations(true);
    }
}