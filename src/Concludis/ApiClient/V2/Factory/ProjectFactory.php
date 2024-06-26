<?php

namespace Concludis\ApiClient\V2\Factory;

use Concludis\ApiClient\Resources\Award;
use Concludis\ApiClient\Resources\Board;
use Concludis\ApiClient\Resources\Category;
use Concludis\ApiClient\Resources\Classification;
use Concludis\ApiClient\Resources\Company;
use Concludis\ApiClient\Resources\CustomField;
use Concludis\ApiClient\Resources\Element;
use Concludis\ApiClient\Resources\Group;
use Concludis\ApiClient\Resources\JobadContainer;
use Concludis\ApiClient\Resources\Location;
use Concludis\ApiClient\Resources\PositionDescription;
use Concludis\ApiClient\Resources\PositionInformation;
use Concludis\ApiClient\Resources\PositionTitle;
use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\Resources\Schedule;
use Concludis\ApiClient\Resources\Seniority;
use Concludis\ApiClient\Util\DateUtil;
use DateTimeZone;
use Exception;

class ProjectFactory {

    private static function extractJobadContainers(string $source_id, string $project_id, string $locale, array $source, array &$target): void {

        foreach($source as $datafield_data) {

            if((bool)($datafield_data['show_in_text_ad'] ?? true) === false) {
                continue;
            }

            $target[] = new JobadContainer([
                'source_id' => $source_id,
                'project_id' => $project_id,
                'datafield_id' => (int)$datafield_data['id'],
                'locale' => $locale,
                'type' => !empty($datafield_data['type']) ? (string)$datafield_data['type'] : null,
                'sortorder' => (int)($datafield_data['sortorder'] ?? 0),
                'container_type' => (string)$datafield_data['container_type'],
                'content_external' => (string)$datafield_data['contenthtml'],
                'content_internal' => (string)($datafield_data['contenthtml_internal'] ?? '')
            ]);
        }
    }

    /**
     * @throws Exception
     */
    public static function createFromResponseObject(string $source_id, array $data): Project {

//        {
//              "id": 777,
//              "global_id": "1P777",
//              "locale": "de_DE",
//              "title": "Bewerter-Test 2",
//              "position_title": "Bewerter-Test 2",
//              "teaser": "<p>Das ist eine Kurzbeschreibung die beim Anlegen eines Projektes immer gezogen werden soll.</p>",
//              "abbr": "Bewe",
//              "apply_types": ["F", "P"],
//              "department": "",
//              "earliest_entry_date": "0000-00-00",
//              "listed": true,
//              "public_start_date": "2018-11-14",
//              "public_end_date": "2020-12-31",
//              "public_applicable": true,
//              "internal_start_date": "2018-11-14",
//              "internal_end_date": "2020-12-31",
//              "internal_applicable": true,
//              "unsolicited": false,
//              "apprentice": false,
//              "temporary": false,
//              "term_length_months": 0,
//              "term_date": null,
//              "takeover_possible": false,
//              "hours_per_week": 0,
//              "social_insurance": false,
//              "questionnaire_id": 0,
//              "years_of_experience": null,
//              "schedule": null,
//              "classification": {
//                    "id": 1,
//                "global_id": 1,
//                "name": "Festanstellung"
//              },
//              "jobfamily": {
//                  "id": 7,
//                  "name": "Sekretariat"
//              },
//              "category": null,
//              "jobgroup1":  [
//                  {
//                      "id": 14,
//                      "external_id": "",
//                      "name": "Auszubildende"
//                  }
//              ],
//              "jobgroup2": [],
//              "jobgroup3": [],
//              "seniority": null,
//              "leadership": {
//                    "id": 3,
//                "name": "Teamleitung, Projektleitung, Gruppenleitung"
//              },
//              "custom_fields": [
//                {
//                    "id": 1,
//                  "name": "Einzeiliges Texteingabefeld",
//                  "type": 1,
//                  "value": "das ist schön & gut"
//                },
//                {
//                    "id": 2,
//                  "name": "Mehrzeiliges Texteingabefeld",
//                  "type": 2,
//                  "value": "das ist noch schöner\n& noch guter"
//                },
//                {
//                    "id": 3,
//                  "name": "Auswahlfeld (Einfach-Auswahl)",
//                  "type": 10,
//                  "value": {
//                    "id": 1,
//                    "display_name": "A",
//                    "external_id": "a"
//                  }
//                },
//                {
//                    "id": 4,
//                  "name": "Auswahlfeld (Mehrfach-Auswahl)",
//                  "type": 20,
//                  "value": [
//                    {
//                        "id": 4,
//                      "display_name": "Cyan",
//                      "external_id": "c"
//                    },
//                    {
//                        "id": 5,
//                      "display_name": "Magenta",
//                      "external_id": "m"
//                    },
//                    {
//                        "id": 6,
//                      "display_name": "Yellow",
//                      "external_id": "y"
//                    },
//                    {
//                        "id": 7,
//                      "display_name": "Black",
//                      "external_id": "b"
//                    }
//                  ]
//                }
//              ],
//              "contact": {
//                    "id": 197,
//                "gender": "m",
//                "title": "Dr.",
//                "firstname": "Timm Volkwin Chefkoch",
//                "lastname": "Maass",
//                "email": "tm@concludis.de",
//                "phone": "",
//                "profile_image": "https://tmaass.dev.concludis.de/assets/concludis/img/user.png",
//                "position": "Entwickler",
//                "division": "Geschäftsleitung",
//                "department": "EG",
//                "organisation": "Concludis"
//              },
//              "contact_internal": {
//                    "id": 197,
//                "gender": "m",
//                "title": "Dr.",
//                "firstname": "Timm Volkwin Chefkoch",
//                "lastname": "Maass",
//                "email": "tm@concludis.de",
//                "phone": "",
//                "profile_image": "https://tmaass.dev.concludis.de/assets/concludis/img/user.png",
//                "position": "Entwickler",
//                "division": "Geschäftsleitung",
//                "department": "EG",
//                "organisation": "Concludis"
//              },
//              "managers": [
//                {
//                    "id": 257,
//                  "gender": "m",
//                  "title": "",
//                  "firstname": "He-Man",
//                  "lastname": "Master of the Universe",
//                  "email": "tm@concludis.de",
//                  "phone": "",
//                  "profile_image": "https://tmaass.dev.concludis.de/assets/concludis/img/user.png",
//                  "position": "",
//                  "division": "",
//                  "department": "",
//                  "organisation": ""
//                }
//              ],
//              "primary_company": {
//                    "id": 4,
//                "name": "Pizza2 GmbH &#38; Co. KG",
//                "logo": "https://tmaass.dev.concludis.de/download/design/MTAwMDA2Nzk=/StevenTeller.JPG",
//                "company_site": "",
//                "carreer_site": "",
//                "address": "Pizzapiazza 2",
//                "postal_code": "55553",
//                "city": "Vitello Tonato",
//                "country_code": "IT"
//              },
//              "location_groups": [
//                {
//                    "id": 777,
//                  "name": "Arbeitsortgruppe #777",
//                  "vacancies": 1,
//                  "location_assignments": [
//                    {
//                        "company_id": null,
//                      "usergroup_id": null,
//                      "location_id": 27
//                    }
//                  ]
//                }
//              ],
//              "vacancies_sum": 1,
//              "countrywide": null,
//              "tags": [
//                {
//                    "id": 102,
//                  "term": "Projektmanagement"
//                },
//                {
//                    "id": 293,
//                  "term": "test"
//                }
//              ],
//              "job_ad_url": "https://tmaass.dev.concludis.de/prj/shw/f1c1592588411002af340cbaedd6fc33_0/777/Bewerter-Test_2.htm",
//              "job_ad_url_internal": "https://tmaass.dev.concludis.de/prj/intranet/shw/f1c1592588411002af340cbaedd6fc33_0/777/Bewerter-Test_2.htm",
//              "pdf_url": "https://tmaass.dev.concludis.de/bewerber/job2pdf.php?jobid=777",
//              "pdf_url_internal": "https://tmaass.dev.concludis.de/bewerber/job2pdf.php?jobid=777&ie=0",
//              "apply_url": "https://tmaass.dev.concludis.de/bewerber/landingpage.php?prj=1P777&lang=de_DE&ie=1",
//              "apply_url_internal": "https://tmaass.dev.concludis.de/bewerber/landingpage.php?prj=1P777&lang=de_DE&ie=0"
//    }

        $pdata = [];


        $fallback_locale = (string)($data['fallback_locale'] ?? '');
        $i18n_present = (bool)($data['i18n_present'] ?? false);

        $pdata['__reset_translations'] = true;

//        $locales_available = (array)($data['locales_available'] ?? []);
//        if(!empty($locales_available)) {
//            foreach($locales_available as $locale) {
//                $pdata['translations'][$locale] = [];
//                unset($locale);
//            }
//        }

        $pdata['source_id'] = $source_id;
        $pdata['gid'] = (string)($data['global_id'] ?? '');
        $pdata['id'] = (int)($data['id'] ?? 0);
        $pdata['locale'] = (string)$data['locale'];
        $pdata['status'] = Project::STATUS_ACTIVE; // @todo
        $pdata['is_published_public'] = (bool)$data['public_published'];
        $pdata['is_published_internal'] = (bool)$data['internal_published'];
        $pdata['is_listed'] = (bool)$data['listed'];
        $pdata['is_apprentice'] = (bool)$data['apprentice'];
        $pdata['is_unsolicited_application'] = (bool)$data['unsolicited'];

        $pdata['title'] = $data['title'] ?? null;
        $pdata['position_title'] = $data['position_title'] ?? null;
        $pdata['teaser'] = $data['teaser'] ?? null;

        $tmp = DateUtil::parseIso8601($data['public_start_date']);
        if($tmp !== null) {
            $tmp->setTimezone(new DateTimeZone(date_default_timezone_get()));
            $pdata['date_from_public'] = $tmp->format('Y-m-d');
        }

        $tmp = DateUtil::parseIso8601($data['internal_start_date']);
        if($tmp !== null) {
            $tmp->setTimezone(new DateTimeZone(date_default_timezone_get()));
            $pdata['date_from_internal'] = $tmp->format('Y-m-d');
        }

        if(array_key_exists('apply_types', $data) && is_array($data['apply_types'])) {
            $pdata['apply_types'] = $data['apply_types'];
        }

        if(array_key_exists('earliest_entry_date', $data)) {
            $tmp = DateUtil::parseIso8601((string)$data['earliest_entry_date']);
            if($tmp) {
                $pdata['earlystartdate'] = $tmp->format('Y-m-d');
            }
            unset($tmp);
        }

        if(array_key_exists('created_at', $data)) {
            $tmp = DateUtil::parseIso8601($data['created_at']);
            if($tmp !== null) {
                $tmp->setTimezone(new DateTimeZone(date_default_timezone_get()));
                $pdata['created_at'] = $tmp->format('Y-m-d H:i:s');
            }
            unset($tmp);
        }



//  locations...
//        {
//            "id": 23,
//          "display_name": "Concludis Zentrale, Frankfurter Str. 561, DE-51145 Köln",
//          "name": "Concludis Zentrale",
//          "address": "Frankfurter Str. 561",
//          "city": "Köln",
//          "postal_code": "51145",
//          "country_code": "DE",
//          "defaultgroup": 0,
//          "defaultgroup_name": null,
//          "region": 1,
//          "region_name": "Region Nord",
//          "sortorder": 11,
//          "active": true,
//          "external_id": "emde_rules",
//          "intern_freetext1": "Das ist was ganz ganz ganz ganz langes",
//          "intern_freetext2": "Und noch länger",
//          "intern_freetext3": "Es geht aber noch viel länger. Es geht aber noch viel länger. Es geht aber noch viel länger. Es geht aber noch viel länger. Es geht aber noch viel länger. Es geht aber noch viel länger. Es geht aber noch viel länger. Es geht aber noch viel länger. Es geht",
//          "latitude": 50.89128,
//          "longitude": 7.07828
//        }

        if(array_key_exists('locations', $data) && is_array($data['locations'])) {
            $pdata['locations'] = [];
            foreach($data['locations'] as $v) {

                $tmp_region = null;
                if($v['region'] > 0){
                    $tmp_region = new Element([
                        'source_id' => $source_id,
                        'id' => $v['region'],
                        'name' => $v['region_name']
                    ]);
                }

                $pdata['locations'][] = new Location([
                    'source_id' => $source_id,
                    'id' => $v['id'],
                    'external_id' => $v['external_id'],
                    'name' => $v['name'],
                    'address' => $v['address'],
                    'postal_code' => $v['postal_code'],
                    'locality' => $v['city'],
                    'country_code' => $v['country_code'],
                    'custom1' => $v['intern_freetext1'],
                    'custom2' => $v['intern_freetext2'],
                    'custom3' => $v['intern_freetext3'],
                    'lat' => $v['latitude'],
                    'lon' => $v['longitude'],
                    'region' => $tmp_region
                ]);

                unset($v, $tmp_region);
            }
        }

        if(array_key_exists('jobfamily', $data) && is_array($data['jobfamily'])) {
            $pdata['family'] = [
                new Element([
                    'source_id' => $source_id,
                    'id' => (int)$data['jobfamily']['id'],
                    'name' => $i18n_present ? (string)$data['jobfamily']['name'][$fallback_locale] : (string)$data['jobfamily']['name']
                ])
            ];
        }

        if(array_key_exists('boards', $data) && is_array($data['boards'])) {
            $pdata['boards'] = [];
            foreach($data['boards'] as $v) {
                $pdata['boards'][] = new Board([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['id'],
                    'external_id' => $v['external_id'],
                    'name' => $v['name']
                ]);
                unset($v);
            }
        }

        if(array_key_exists('awards', $data) && is_array($data['awards'])) {
            $pdata['awards'] = [];
            foreach($data['awards'] as $v) {
                $pdata['awards'][] = new Award([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'valid_from' => $v['valid_from'],
                    'valid_until' => $v['valid_until'],
                    'url' => $v['url']
                ]);
                unset($v);
            }
        }

        if(array_key_exists('jobgroup1', $data) && is_array($data['jobgroup1'])) {
            $pdata['group1'] = [];
            foreach($data['jobgroup1'] as $v) {
                $pdata['group1'][] = new Group([
                    'source_id' => $source_id,
                    'id' => $v['id'],
                    'external_id' => $v['external_id'],
                    'name' => $v['name'],
                    'locale' => $v['locale'] ?? $pdata['locale']
                ]);
                unset($v);
            }
        }

        if(array_key_exists('jobgroup2', $data) && is_array($data['jobgroup2'])) {
            $pdata['group2'] = [];
            foreach($data['jobgroup2'] as $v) {
                $pdata['group2'][] = new Group([
                    'source_id' => $source_id,
                    'id' => $v['id'],
                    'external_id' => $v['external_id'],
                    'name' => $v['name'],
                    'locale' => $v['locale'] ?? $pdata['locale']
                ]);
                unset($v);
            }
        }

        if(array_key_exists('jobgroup3', $data) && is_array($data['jobgroup3'])) {
            $pdata['group3'] = [];
            foreach($data['jobgroup3'] as $v) {
                $pdata['group3'][] = new Group([
                    'source_id' => $source_id,
                    'id' => $v['id'],
                    'external_id' => $v['external_id'],
                    'name' => $v['name'],
                    'locale' => $v['locale'] ?? $pdata['locale']
                ]);
                unset($v);
            }
        }

        if(array_key_exists('custom_fields', $data)) {
            $pdata['custom_fields'] = [];
            foreach($data['custom_fields'] as $v) {
                $pdata['custom_fields'][] = new CustomField([
                    'source_id' => $source_id,
                    'id' => $v['id'],
                    'type' => $v['type'],
                    'name' => $i18n_present ? $v['name'][$fallback_locale] : $v['name'],
                    'value' => $v['value']
                ]);
                unset($v);
            }
        }

//              "managers": [
//                {
//                    "id": 257,
//                  "gender": "m",
//                  "title": "",
//                  "firstname": "He-Man",
//                  "lastname": "Master of the Universe",
//                  "email": "tm@concludis.de",
//                  "phone": "",
//                  "profile_image": "https://tmaass.dev.concludis.de/assets/concludis/img/user.png",
//                  "position": "",
//                  "division": "",
//                  "department": "",
//                  "organisation": ""
//                }
//              ],
        if(array_key_exists('managers', $data) && is_array($data['managers'])){
            $pdata['manager'] = [];
            foreach($data['managers'] as $v) {
                $pdata['manager'][] = PersonFactory::createFromResponseObject($source_id, $v);
                unset($v);
            }
        }

        if(array_key_exists('contact', $data) && $data['contact'] !== null){
            $pdata['contact'] = PersonFactory::createFromResponseObject($source_id, $data['contact']);
        }

        if(array_key_exists('contact_internal', $data) && $data['contact_internal'] !== null){
            $pdata['contact_internal'] = PersonFactory::createFromResponseObject($source_id, $data['contact_internal']);
        }

        if(array_key_exists('employerbrand', $data) && is_string($data['employerbrand'])){
            $pdata['employerbrand'] = trim($data['employerbrand']);
        }

        if(array_key_exists('primary_company', $data) && $data['primary_company'] !== null){

            $v = $data['primary_company'];

            $pdata['company'] = new Company([
                'source_id' => $source_id,
                'id' => $v['id'],
                'external_id' => $v['external_id'],
                'name' => $v['name'],
//                'industry' => new Element([  // @todo
//                    'source_id' => $pdata['source_id'],
//                    'id' => $data['job_company']['job_company_industry']['job_company_industry_id'],
//                    'name' => $data['job_company']['job_company_industry']['job_company_industry_name']
//                ]),
                'url_logo' => $v['logo'],
                'background_color' => $v['background_color'],
                'headline_color' => $v['headline_color'],
                'address' => $v['address'],
                'postal_code' => $v['postal_code'],
                'locality' => $v['city'],
                'country_code' => $v['country_code'],
                'commercialregister' => $v['commercialregister'],

                'url_company_site' => $v['company_site'],
                'url_career_site' => $v['carreer_site'],
                'xing_profile_url' => (string)($v['xing_profile_url'] ?? ''),
                'linkedin_profile_url' => (string)($v['linkedin_profile_url'] ?? ''),
                'linkedin_reference' => (string)($v['linkedin_reference'] ?? ''),

                'facebook' => $v['facebook'] ?? '',
                'whatsapp' => $v['whatsapp'] ?? '',
                'twitter' => $v['twitter'] ?? '',
                'instagram' => $v['instagram'] ?? '',
                'youtube' => $v['youtube'] ?? '',
                'tiktok' => $v['tiktok'] ?? '',

                'kununu' => $v['kununu'] ?? null
            ]);
            unset($v);
        }

        if(array_key_exists('classification', $data)){
            $v = $data['classification'];
            if($v !== null) {

                // Ausbildung (global_id = 3) in Verbindung mit dualem Studium (education_type = 1) wird auf classification "Duales Studium (id 16)" gemappt.
                // Die 16 gibt in concludis nur als deleted = 1
                if($v['global_id'] === 3 && ($data['ba_position_information']['education_type'] ?? null) === 1) {
                    $v['global_id'] = 16;
                }
                $pdata['classification'] = new Classification([
                    'source_id' => $source_id,
                    'id' => $v['id'],
                    'global_id' => (int)($v['global_id'] ?? 0),
                    'name' => $v['name'],
                    'locale' => $v['locale'] ?? $pdata['locale']
                ]);
            }
            unset($v);
        }

        if(array_key_exists('seniority', $data)){
            $v = $data['seniority'];
            if($v !== null) {
                $pdata['seniority'] = new Seniority([
                    'source_id' => $source_id,
                    'id' => $v['id'],
                    'global_id' => (int)($v['global_id'] ?? 0),
                    'name' => $v['name'],
                    'locale' => $v['locale'] ?? $pdata['locale']
                ]);
            }
            unset($v);
        }

        if(array_key_exists('schedule', $data)) {
            $v = $data['schedule'];
            if($v !== null) {
                $pdata['schedule'] = new Schedule([
                    'source_id' => $source_id,
                    'id' => $v['id'],
                    'global_id' => (int)($v['global_id'] ?? 0),
                    'name' => $v['name'],
                    'locale' => $v['locale'] ?? $pdata['locale']
                ]);
            }
            unset($v);
        }

        if(array_key_exists('years_of_experience', $data)){
            $v = $data['years_of_experience'];
            if($v !== null) {
                $pdata['years_of_experience'] = new Element([
                    'source_id' => $source_id,
                    'id' => $v['id'],
                    'name' => $v['name']
                ]);
            }
            unset($v);
        }

        if(array_key_exists('category', $data)) {

            $tmp = $data['category'];
            if($tmp !== null) {

                $tmp_occupations = [];
                foreach ($data['category']['occupations'] as $v) {
                    $tmp_occupations[] = new Element([
                        'source_id' => $source_id,
                        'id' => $v['id'],
                        'global_id' => $v['global_id'],
                        'name' => $i18n_present ? $v['name'][$fallback_locale] : $v['name']
                    ]);
                    unset($v);
                }

                if (!empty($tmp_occupations)) {
                    $pdata['category'] = new Category([
                        'source_id' => $source_id,
                        'id' => $tmp['id'],
                        'global_id' => $tmp['global_id'],
                        'name' => $tmp['name'],
                        'locale' => $tmp['locale'] ?? $pdata['locale'],
                        'occupations' => $tmp_occupations
                    ]);
                }
                unset($tmp_occupations);
            }
            unset($tmp);
        }

        if(array_key_exists('tags', $data) && is_array($data['tags'])) {
            $pdata['tags'] = [];
            foreach($data['tags'] as $tag) {
                $pdata['tags'][] = new Element([
                    'source_id' => $source_id,
                    'id' => $tag['id'],
                    'name' => $tag['term']
                ]);
            }
        }


        if(array_key_exists('remotetype', $data) && is_int($data['remotetype'])) {
            $pdata['remotetype'] = $data['remotetype'];
        }

        if(array_key_exists('salary', $data) && is_array($data['salary'])) {
            $pdata['salary'] = $data['salary'];
        }

        if(array_key_exists('indeed_enabled', $data) && is_int($data['indeed_enabled'])) {
            $pdata['indeed_enabled'] = $data['indeed_enabled'];
        }
        if(array_key_exists('indeed_extended', $data) && is_array($data['indeed_extended'])) {
            $pdata['indeed_apply_data'] = $data['indeed_extended'];
        }


        $pdata['jobad_url'] = (string)$data['job_ad_url'];
        $pdata['jobad_url_internal'] = (string)$data['job_ad_url_internal'];
        $pdata['apply_url'] = (string)$data['apply_url'];
        $pdata['apply_url_internal'] = (string)$data['apply_url_internal'];
        $pdata['pdf_url'] = (string)$data['pdf_url'];
        $pdata['pdf_url_internal'] = (string)$data['pdf_url_internal'];

        if(array_key_exists('jobad', $data)) {

            $jobad = $data['jobad'];

            if(array_key_exists('full_markup', $jobad)) {
                $pdata['jobad_html'] = $jobad['full_markup'];
            }

            if(array_key_exists('containers', $jobad) && is_array($jobad['containers'])) {
                $pdata['jobad_containers'] = [];
                if($i18n_present) {
                    foreach($jobad['containers'] as $locale => $containers) {
                        self::extractJobadContainers($source_id, (string)$pdata['id'], $locale, $containers, $pdata['jobad_containers']);
                    }
                } else {
                    self::extractJobadContainers($source_id, (string)$pdata['id'], $pdata['locale'], $jobad['containers'], $pdata['jobad_containers']);
                }

                $pdata['jobad_containers'] = array_filter($pdata['jobad_containers'], static function(JobadContainer $c) {
                    // try to drop empty containers
                    $allowed_tags = ['img','a','ul','li','hr'];
                    return trim(strip_tags($c->content_external, $allowed_tags)) !== ''
                        ||trim(strip_tags($c->content_internal, $allowed_tags)) !== '';
                });
            }

            if(array_key_exists('tracking_code', $jobad)) {
                $pdata['tracking_code'] = $jobad['tracking_code'];
            }

            if(array_key_exists('google_for_jobs_code', $jobad)) {
                $pdata['google_for_jobs_code'] = $jobad['google_for_jobs_code'];
            }

            unset($jobad);
        }


        if(array_key_exists('ba_published', $data)) {
            $pdata['ba_is_published'] = (bool)$data['ba_published'];
        }



        if(array_key_exists('ba_position_information', $data)) {

            $position_title = new PositionTitle();

            if(array_key_exists('job_position_title', $data['ba_position_information'])) {
                $v = $data['ba_position_information']['job_position_title'];
                $position_title = new PositionTitle([
                    'title_code' => $v['title_code'] ?? null,
                    'degree'     => $v['degree'] ?? null,
                    'course'     => $v['course'] ?? null
                ]);
            }

            $alternative_position_title = [];
            if(array_key_exists('alternative_job_position_title', $data['ba_position_information'])) {
                foreach($data['ba_position_information']['alternative_job_position_title'] as $v) {
                    $alternative_position_title[] = new PositionTitle([
                        'title_code' => $v['title_code'] ?? null,
                        'degree' => $v['degree'] ?? null,
                    ]);
                }
            }

            $position_description = new PositionDescription();

            if(array_key_exists('job_position_description', $data['ba_position_information'])) {
                $v = $data['ba_position_information']['job_position_description'];
                $position_description = new PositionDescription([
                    'mini_job' => $v['mini_job'] ?? null,
                    'salary' => $v['salary'] ?? null,
                    'schedule_working_plan' => $v['schedule_working_plan'] ?? null,
                    'schedule_summary_text' => $v['schedule_summary_text'] ?? null,
                    'duration_temporary_or_regular' => $v['duration_temporary_or_regular'] ?? null,
                    'duration_term_date' => $v['duration_term_date'] ?? null,
                    'duration_term_length' => $v['duration_term_length'] ?? null,
                    'duration_take_over' => $v['duration_take_over'] ?? null
                ]);
            }

            $_data = $data['ba_position_information'];

            $pdata['position_information'] = new PositionInformation([
                'job_offer_type' => (int)$_data['job_offer_type'],
                'education_type' => $_data['education_type'] ?? null,
                'degree_type' => $_data['degree_type'] ?? null,
                'position_title_description' => (string)$_data['job_position_title_description'],
                'social_insurance' => $_data['social_insurance'] ?? null,
                'position_title' => $position_title,
                'alternative_position_title' => $alternative_position_title,
                'position_description' => $position_description
            ]);

        }

        return new Project($pdata);
    }
}