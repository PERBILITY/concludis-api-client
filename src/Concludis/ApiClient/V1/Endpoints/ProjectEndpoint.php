<?php

namespace Concludis\ApiClient\V1\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Resources\Board;
use Concludis\ApiClient\Resources\Category;
use Concludis\ApiClient\Resources\Classification;
use Concludis\ApiClient\Resources\Company;
use Concludis\ApiClient\Resources\Element;
use Concludis\ApiClient\Resources\Group;
use Concludis\ApiClient\Resources\JobadContainer;
use Concludis\ApiClient\Resources\Location;
use Concludis\ApiClient\Resources\Person;
use Concludis\ApiClient\Resources\PositionDescription;
use Concludis\ApiClient\Resources\PositionInformation;
use Concludis\ApiClient\Resources\PositionTitle;
use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\Resources\Schedule;
use Concludis\ApiClient\Resources\Seniority;
use Concludis\ApiClient\Util\ArrayUtil;
use Concludis\ApiClient\V1\Client\Client;
use RuntimeException;

class ProjectEndpoint extends AbstractEndpoint {

    public const FILTER_TYPE_BOARD = 'board';
    public const FILTER_TYPE_GROUP1 = 'group1';
    public const FILTER_TYPE_GROUP2 = 'group2';
    public const FILTER_TYPE_GROUP3 = 'group3';
    public const FILTER_TYPE_LOCATIONGROUP = 'locationgroup';
    public const FILTER_TYPE_LOCATION = 'location';
    public const FILTER_TYPE_COMPANY = 'company';
    public const FILTER_TYPE_CLASSIICATION = 'classification';
    public const FILTER_TYPE_SCHEDULE = 'schedule';
    public const FILTER_TYPE_SENIORITY = 'seniority';
    public const FILTER_TYPE_YOE = 'yoe';
    public const FILTER_TYPE_COUNTRYCODE = 'countrycode';
    public const FILTER_TYPE_STATECODE = 'statecode';
    public const FILTER_TYPE_AREACODE = 'areacode';
    public const FILTER_TYPE_CATEGORY = 'category';
    public const FILTER_TYPE_INTEXT = 'int_ext';
    public const FILTER_TYPE_JB = 'jb';
    private const FILTER_TYPE_RADIUS = 'radius';
    private const FILTER_TYPE_PAGINATION = 'pagination';

    public const FILTER_VALUE_INTEXT_INT = 'i';
    public const FILTER_VALUE_INTEXT_EXT = 'e';
    public const FILTER_VALUE_INTEXT_BOTH = 'a';

    /**
     * @var array
     */
    private array $filter = [];


    public function __construct(Client $client) {
        parent::__construct($client);
    }

    public function radius(string $postal_code, int $km): ProjectEndpoint {
        $this->filter[self::FILTER_TYPE_RADIUS] = [
            'postal_code' => $postal_code,
            'km' => $km
        ];
        return $this;
    }

    public function paginate(int $limit, int $offset): ProjectEndpoint {
        $this->filter[self::FILTER_TYPE_PAGINATION] = [
            'limit' => $limit,
            'offset' => $offset
        ];
        return $this;
    }

    public function addFilter(string $filter_type,  $value): ProjectEndpoint {

        $this->filter[$filter_type] = $value;
        return $this;
    }

    /**
     * @param string $update_datetime
     * @param array $filters
     * @return Project[]
     */
    public function listFull(string $update_datetime, array $filters = []): array {

        $res = $this->call('FULL', $filters);

        if($res->error !== null) {
            throw $res->error->exception;
        }

        if($res->success) {
            $data = $res->data;

            unset($data['stat']);

            $r = [];
            foreach($data as $d) {
                $d['lastupdate'] = $update_datetime;
                $r[] = $this->createProject($d);
            }

            return $r;
        }

        throw new RuntimeException('Something went wrong, listFull failed');
    }

    /**
     * @param string $update_datetime
     * @param array $filters
     * @return Project[]
     */
    public function listSlim(string $update_datetime, array $filters = []): array {

        $res = $this->call('SLIM', $filters);

        if($res->error !== null) {
            throw $res->error->exception;
        }

        if($res->success) {
            $data = $res->data;

            unset($data['stat']);

            $r = [];
            foreach($data as $d) {
                $d['lastupdate'] = $update_datetime;
                $r[] = $this->createProject($d);
            }

            return $r;
        }
        throw new RuntimeException('Something went wrong, listSlim failed');
    }

    /**
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []): int {

        $res = $this->call('COUNT', $filters);

        if($res->error !== null) {
            throw $res->error->exception;
        }

        if($res->success) {
            return (int)$res->data['int'];
        }
        throw new RuntimeException('Something went wrong, count projects failed');
    }

    public function byId(string $update_datetime, int $id): ?Project {

        $res = $this->call($id);

        if($res->success && array_key_exists($id, $res->data)) {
            $d = (array)$res->data[$id];
            $d['lastupdate'] = $update_datetime;
            return $this->createProject($d);
        }
        return null;

    }

    /**
     * @param $pid
     * @param array $filters
     * @return ApiResponse
     */
    private function call($pid, array $filters = []): ApiResponse {

        $boards = 'ALL';
        if(array_key_exists('boards', $filters)){
            $_boards = ArrayUtil::toIntArray($filters['boards']);
            if(!empty($_boards)){
                $boards = implode(',', $_boards);
            }
        }

        $url_params = [
            '{lang}' => 'DE',
            '{pid}' => $pid,
            '{board}' => $boards,
            '{group1}' => 'ALL',
            '{group2}' => 'ALL',
            '{group3}' => 'ALL',
            '{locationgroup}' => 'ALL',
            '{location}' => 'ALL',
            '{company}' => 'ALL',
            '{classification}' => 'ALL',
            '{schedule}' => 'ALL',
            '{seniority}' => 'ALL',
            '{yoe}' => 'ALL',
            '{countrycode}' => 'ALL',
            '{statecode}' => 'ALL',
            '{areacode}' => 'ALL',
            '{category}' => 'ALL'
        ];

        $this->applyIntFilter(self::FILTER_TYPE_BOARD, '{board}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_GROUP1, '{group1}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_GROUP2, '{group2}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_GROUP3, '{group3}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_LOCATIONGROUP, '{locationgroup}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_LOCATION, '{location}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_COMPANY, '{company}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_CLASSIICATION, '{classification}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_SCHEDULE, '{schedule}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_SENIORITY, '{seniority}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_YOE, '{yoe}', $url_params);
        $this->applyStringFilter(self::FILTER_TYPE_COUNTRYCODE, '{countrycode}', $url_params);
        $this->applyStringFilter(self::FILTER_TYPE_STATECODE, '{statecode}', $url_params);
        $this->applyStringFilter(self::FILTER_TYPE_AREACODE, '{areacode}', $url_params);
        $this->applyIntFilter(self::FILTER_TYPE_CATEGORY, '{category}', $url_params);


        $endpoint = '/{lang}/project/' .
            '{pid}/{board}/{group1}/{group2}/{group3}' .
            '/{locationgroup}/{location}/{company}' .
            '/{classification}/{schedule}/{seniority}' .
            '/{yoe}/{countrycode}/{statecode}/{areacode}' .
            '/{category}';

        $endpoint = strtr($endpoint, $url_params);

        $get_params = [
            'filter_intext' => self::FILTER_VALUE_INTEXT_EXT,
            'filter_jb' => 1,
        ];

        if(array_key_exists(self::FILTER_TYPE_INTEXT, $this->filter)) {
            $get_params['filter_intext'] = $this->filter[self::FILTER_TYPE_INTEXT];
        }

        if(array_key_exists(self::FILTER_TYPE_JB, $this->filter)) {
            $get_params['filter_jb'] = $this->filter[self::FILTER_TYPE_JB];
        }

        if(array_key_exists(self::FILTER_TYPE_PAGINATION, $this->filter)) {
            $get_params['limit'] = $this->filter[self::FILTER_TYPE_PAGINATION]['limit'];
            $get_params['offset'] = $this->filter[self::FILTER_TYPE_PAGINATION]['offset'];
        }

        if(array_key_exists(self::FILTER_TYPE_RADIUS, $this->filter)) {
            $get_params['radius_zip'] = $this->filter[self::FILTER_TYPE_RADIUS]['postal_code'];
            $get_params['radius_km'] = $this->filter[self::FILTER_TYPE_RADIUS]['km'];
        }

        return $this->client->call($endpoint, $get_params, 'GET');



        //(?(limit={limit})(&offset={offset})(&filter_intext={filter_intext})(&filter_jb={filter_jb})(&radius_zip={radius_zip})(&radius_km={radius_km}))';
    }

    private function applyIntFilter(string $filter_type, string $url_params_key, array &$url_params): void {
        if(array_key_exists($filter_type, $this->filter)) {
            $filter_value = ArrayUtil::toIntArray($this->filter[$filter_type]);
            if(!empty($filter_value)) {
                $url_params[$url_params_key] = implode(',', $filter_value);
            }
        }
    }

    private function applyStringFilter(string $filter_type, string $url_params_key, array &$url_params): void {
        if(array_key_exists($filter_type, $this->filter)) {
            $filter_value = ArrayUtil::toStringArray($this->filter[$filter_type]);
            if(!empty($filter_value)) {
                $url_params[$url_params_key] = implode(',', $filter_value);
            }
        }
    }

    private function createProject($data): Project {

//        {
//        "job_status": "aktiv",
//        "job_listed": 1,
//        "job_apprentice": 1,
//        "job_city": {},
//        "job_unsolicited_application": 0,
//        "job_date_from": "2008-01-01",
//        "job_date_to": "2019-12-31",
//        "job_intern_date_from": "0000-00-00",
//        "job_intern_date_to": "0000-00-00",
//        "job_auto_deactivate": 1,
//        "job_intern_auto_deactivate": 1,
//        "job_date_earlystart": "0000-00-00",
//        "job_count": 25,
//        "job_contract_temp": 1,
//        "job_default_locale": "de_DE",
//        "job_company": {
//            "job_company_id": 1,
//            "job_company_name": "Hipster Media Company",
//            "job_company_industry": {
//                "job_company_industry_id": 67,
//                "job_company_industry_name": "Internet und Onlinemedien"
//            },
//            "job_company_url": "",
//            "job_company_career_site_url": "",
//            "job_company_logo": null,
//            "job_company_address": "Kurfürstendamm 1000",
//            "job_company_zip": "10707",
//            "job_company_city": "Berlin",
//            "job_company_country_code": "DE",
//            "job_company_commercialregister": "",
//            "job_company_email_disclaimer": "",
//            "job_company_external_id": "",
//            "job_company_invoice_email": ""
//        },
//        "job_category_id": null,
//        "job_processing_with_concludis": 1,
//        "job_processing_with_concludis_recipient_email": "",
//        "job_current_locale": "de_DE",
//        "job_category": {
//            "job_occupation": {}
//        },
//        "job_ad_url": "http://trunk3.local.dev.concludis.de/prj/shw/c4ca4238a0b923820dcc509a6f75849b_0/1/Senior_Fullstack_PHP_Web_Entwickler_m_w.htm",
//        "job_ad_url_internal": "http://trunk3.local.dev.concludis.de/prj/intranet/shw/c4ca4238a0b923820dcc509a6f75849b_0/1/Senior_Fullstack_PHP_Web_Entwickler_m_w.htm",
//        "job_apply_url": "http://trunk3.local.dev.concludis.de/bewerber/landingpage.php?prj=c4ca4238a0b923820dcc509a6f75849b&lang=de_DE&ie=1&oid=",
//        "job_apply_url_internal": "http://trunk3.local.dev.concludis.de/bewerber/landingpage.php?prj=c4ca4238a0b923820dcc509a6f75849b&lang=de_DE&ie=0&oid=",
//        "job_pdf_url": "http://trunk3.local.dev.concludis.de/bewerber/job2pdf.php?jobid=1",
//        "job_pdf_url_internal": "http://trunk3.local.dev.concludis.de/bewerber/job2pdf.php?jobid=1&ie=0"
//    }

//        print_r($data);

        $pdata = [];

        $pdata['source_id'] = $this->client->source()->id;

        if(array_key_exists('gid', $data)) {
            $pdata['gid'] = (string)$data['gid'];
        }
        if(array_key_exists('job_id', $data)) {
            $pdata['id'] = (int)$data['job_id'];
        }

        $pdata['locale'] = (string)$data['job_current_locale'];

        if(array_key_exists('job_status', $data)) {
            if($data['job_status'] === 'aktiv') {
                $pdata['status'] = Project::STATUS_ACTIVE;
            } else if($data['job_status'] === 'passiv') {
                $pdata['status'] = Project::STATUS_INACTIVE;
            } else {
                $pdata['status'] = Project::STATUS_DRAFT;
            }
        }

        if(array_key_exists('priority', $data)) {
            $pdata['priority'] = (int)$data['priority'];
        }

        $pdata['is_published_public'] = true;
        $pdata['is_published_internal'] = false;

        if(array_key_exists('job_listed', $data)) {
            $pdata['is_listed'] = (int)$data['job_listed'] === 1;
        }
        if(array_key_exists('job_apprentice', $data)) {
            $pdata['is_apprentice'] = (int)$data['job_apprentice'] === 1;
        }
        if(array_key_exists('job_unsolicited_application', $data)) {
            $pdata['is_unsolicited_application'] = (int)$data['job_unsolicited_application'] === 1;
        }

        if(array_key_exists('job_title', $data)) {
            $pdata['title'] = (string)$data['job_title'];
        }
        if(array_key_exists('job_position_title', $data)) {
            $pdata['position_title'] = (string)$data['job_position_title'];
        }
        if(array_key_exists('job_teaser', $data)) {
            $pdata['teaser'] = (string)$data['job_teaser'];
        }

        if(array_key_exists('job_city', $data) && is_array($data['job_city'])) {
            $pdata['locations'] = [];
            foreach($data['job_city'] as $v) {

                $tmp_region = null;
                if($v['job_region_id'] !== null ){
                    $tmp_region = new Element([
                        'source_id' => $pdata['source_id'],
                        'id' => $v['job_region_id'],
                        'name' => $v['job_region_name']
                    ]);
                }

                $pdata['locations'][] = new Location([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['job_city_id'],
                    'external_id' => $v['job_city_external_id'],
                    'name' => $v['job_location_name'],
                    'address' => $v['job_location_address'],
                    'country_code' => $v['job_city_countrycode'],
                    'postal_code' => $v['job_city_zip'],
                    'locality' => $v['job_city_name'],
                    'custom1' => $v['custom1'],
                    'custom2' => $v['custom2'],
                    'custom3' => $v['custom3'],
                    'lat' => $v['latitude'],
                    'lon' => $v['longitude'],
                    'region' => $tmp_region
                ]);

            }
        }

        if(array_key_exists('job_family', $data) && is_array($data['job_family'])) {
            $pdata['family'] = [];
            foreach($data['job_family'] as $v) {
                $pdata['family'][] = new Element([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['job_family_id'],
                    'name' => $v['job_family_name']
                ]);
            }
        }



        if(array_key_exists('job_board', $data) && is_array($data['job_board'])) {
            $pdata['boards'] = [];
            foreach($data['job_board'] as $v) {
                $pdata['boards'][] = new Board([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['job_board_id'],
                    'external_id' => $v['job_board_external_id'],
                    'name' => $v['job_board_name']
                ]);
            }
        }

        if(array_key_exists('job_group1', $data) && is_array($data['job_group1'])) {
            $pdata['group1'] = [];
            foreach($data['job_group1'] as $v) {
                $pdata['group1'][] = new Group([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['job_group1_id'],
                    'name' => $v['job_group1_name']
                ]);
            }
        }

        if(array_key_exists('job_group2', $data) && is_array($data['job_group2'])) {
            $pdata['group2'] = [];
            foreach($data['job_group2'] as $v) {
                $pdata['group2'][] = new Group([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['job_group2_id'],
                    'name' => $v['job_group2_name']
                ]);
            }
        }

        if(array_key_exists('job_group3', $data) && is_array($data['job_group3'])) {
            $pdata['group3'] = [];
            foreach($data['job_group3'] as $v) {
                $pdata['group3'][] = new Group([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['job_group3_id'],
                    'name' => $v['job_group3_name']
                ]);
            }
        }

        if(array_key_exists('job_manager', $data) && is_array($data['job_manager'])){
            $pdata['manager'] = [];
            foreach($data['job_manager'] as $v) {
                $pdata['manager'][] = new Person([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['job_manager_id'],
                    'external_id' => $v['job_manager_external_id'],
                    'gender' => $v['job_manager_sex'],
                    'title' => $v['job_manager_title'],
                    'firstname' => $v['job_manager_firstname'],
                    'lastname' => $v['job_manager_lastname'],
                    'profile_image' => $v['job_manager_image_url'],
                    'position' => $v['job_manager_position'],
                    'department' => $v['job_manager_department'],
                    'division' => $v['job_manager_division'],
                    'organisation' => $v['job_manager_orga'],
                    'email' => $v['job_manager_email'],
                    'phone' => $v['job_manager_tel'],
                    'address1' => $v['job_manager_adress1'],
                    'address2' => $v['job_manager_adress2'],
                    'postal_code' => $v['job_manager_zip'],
                    'locality' => $v['job_manager_city'],
                    'country_code' => $v['job_manager_country_code'],
                ]);
            }
        }
        if(array_key_exists('job_contact', $data) && $data['job_contact'] !== null){
            $v = $data['job_contact'];
            $pdata['contact'] = new Person([
                'source_id' => $pdata['source_id'],
                'id' => $v['job_contact_id'],
                'external_id' => $v['job_contact_external_id'],
                'gender' => $v['job_contact_sex'],
                'title' => $v['job_contact_title'],
                'firstname' => $v['job_contact_firstname'],
                'lastname' => $v['job_contact_lastname'],
                'profile_image' => $v['job_contact_image_url'],
                'position' => $v['job_contact_position'],
                'department' => $v['job_contact_department'],
                'division' => $v['job_contact_division'],
                'organisation' => $v['job_contact_orga'],
                'email' => $v['job_contact_email'],
                'phone' => $v['job_contact_tel'],
                'address1' => $v['job_contact_adress1'],
                'address2' => $v['job_contact_adress2'],
                'postal_code' => $v['job_contact_zip'],
                'locality' => $v['job_contact_city'],
                'country_code' => $v['job_contact_country_code'],
            ]);
        }
        if(array_key_exists('job_contact_internal', $data) && $data['job_contact_internal'] !== null){
            $v = $data['job_contact_internal'];
            $pdata['contact_internal'] = new Person([
                'source_id' => $pdata['source_id'],
                'id' => $v['job_contact_id'],
                'external_id' => $v['job_contact_external_id'],
                'gender' => $v['job_contact_sex'],
                'title' => $v['job_contact_title'],
                'firstname' => $v['job_contact_firstname'],
                'lastname' => $v['job_contact_lastname'],
                'profile_image' => $v['job_contact_image_url'],
                'position' => $v['job_contact_position'],
                'department' => $v['job_contact_department'],
                'division' => $v['job_contact_division'],
                'organisation' => $v['job_contact_orga'],
                'email' => $v['job_contact_email'],
                'phone' => $v['job_contact_tel'],
                'address1' => $v['job_contact_adress1'],
                'address2' => $v['job_contact_adress2'],
                'postal_code' => $v['job_contact_zip'],
                'locality' => $v['job_contact_city'],
                'country_code' => $v['job_contact_country_code'],
            ]);
        }

        if(array_key_exists('job_company', $data) && $data['job_company'] !== null) {
            $pdata['company'] = new Company([
                'source_id' => $pdata['source_id'],
                'id' => $data['job_company']['job_company_id'],
                'external_id' => $data['job_company']['job_company_external_id'],
                'name' => $data['job_company']['job_company_name'],
                'industry' => new Element([
                    'source_id' => $pdata['source_id'],
                    'id' => $data['job_company']['job_company_industry']['job_company_industry_id'],
                    'name' => $data['job_company']['job_company_industry']['job_company_industry_name']
                ]),
                'url_company_site' => $data['job_company']['job_company_url'],
                'url_career_site' => $data['job_company']['job_company_career_site_url'],
                'url_logo' => $data['job_company']['job_company_logo'],
                'background_color' => $data['job_company']['job_company_background_color'],
                'headline_color' => $data['job_company']['job_company_headline_color'],
                'address' => $data['job_company']['job_company_address'],
                'postal_code' => $data['job_company']['job_company_zip'],
                'locality' => $data['job_company']['job_company_city'],
                'country_code' => $data['job_company']['job_company_country_code'],
                'commercialregister' => $data['job_company']['job_company_commercialregister']
            ]);
        }

        if(array_key_exists('job_classification', $data)){
            $tmp = $data['job_classification'];
            if($tmp !== null) {
                $pdata['classification'] = new Classification([
                    'source_id' => $pdata['source_id'],
                    'id' => $tmp['id_classification'],
                    'global_id' => array_key_exists('global_id', $tmp) ? $tmp['global_id'] : 0,
                    'name' => $tmp['name']
                ]);
            }
        }
        if(array_key_exists('job_seniority', $data)){
            $tmp = $data['job_seniority'];
            if($tmp !== null) {
                $pdata['seniority'] = new Seniority([
                    'source_id' => $pdata['source_id'],
                    'id' => $tmp['id_seniority'],
                    'global_id' => array_key_exists('global_id', $tmp) ? $tmp['global_id'] : 0,
                    'name' => $tmp['name']
                ]);
            }
        }
        if(array_key_exists('job_schedule', $data)){
            $tmp = $data['job_schedule'];
            if($tmp !== null) {
                $pdata['schedule'] = new Schedule([
                    'source_id' => $pdata['source_id'],
                    'id' => $tmp['id_schedule'],
                    'global_id' => array_key_exists('global_id', $tmp) ? $tmp['global_id'] : 0,
                    'name' => $tmp['name']
                ]);
            }
        }
        if(array_key_exists('job_years_of_experience', $data)){
            $tmp = $data['job_years_of_experience'];
            if($tmp !== null) {
                $pdata['years_of_experience'] = new Element([
                    'source_id' => $pdata['source_id'],
                    'id' => $tmp['id'],
                    'name' => $tmp['name']
                ]);
            }
        }
        if(array_key_exists('job_category', $data)) {

            $tmp_occupations = [];
            foreach($data['job_category']['job_occupation'] as $v) {
                $tmp_occupations[] = new Element([
                    'source_id' => $pdata['source_id'],
                    'id' => $v['id'],
                    'global_id' => $v['id_concludis'],
                    'name' => $v['name']
                ]);
            }

            if(!empty($tmp_occupations)) {
                $pdata['category'] = new Category([
                    'source_id' => $pdata['source_id'],
                    'id' => $data['job_category']['id'],
                    'global_id' => $data['job_category']['id_concludis'],
                    'name' => $data['job_category']['name'],
                    'occupations' => $tmp_occupations
                ]);
            }
        }
        if(array_key_exists('job_tags', $data) && is_array($data['job_tags'])) {
            $pdata['tags'] = [];
            foreach($data['job_tags'] as $tag) {
                $pdata['tags'][] = new Element([
                    'source_id' => $pdata['source_id'],
                    'id' => $tag['tag_id'],
                    'name' => $tag['term']
                ]);
            }
        }
        if(array_key_exists('job_ad', $data)) {
            if(array_key_exists('job_ad_html', $data['job_ad'])) {
                $pdata['jobad_html'] = (string)$data['job_ad']['job_ad_html'];
            }
            if(array_key_exists('job_ad_url', $data['job_ad'])) {
                $pdata['jobad_url'] = (string)$data['job_ad']['job_ad_url'];
            }
            if(array_key_exists('job_ad_url_internal', $data['job_ad'])) {
                $pdata['jobad_url_internal'] = (string)$data['job_ad']['job_ad_url_internal'];
            }

            if(array_key_exists('job_ad_container', $data['job_ad']) && is_array($data['job_ad']['job_ad_container'])) {
                $pdata['jobad_containers'] = [];
                foreach($data['job_ad']['job_ad_container'] as $datafield_id => $datafield_data) {

                    if((bool)($datafield_data['show_in_text_ad'] ?? true) === false) {
                        continue;
                    }

                    $pdata['jobad_containers'][] = new JobadContainer([
                        'source_id' => $pdata['source_id'],
                        'project_id' => $pdata['id'],
                        'datafield_id' => (int)$datafield_id,
                        'locale' => $pdata['locale'],
                        'type' => !empty($datafield_data['type']) ? (string)$datafield_data['type'] : null,
                        'container_type' => (string)$datafield_data['container_type'],
                        'content_external' => (string)$datafield_data['contenthtml'],
                        'content_internal' => array_key_exists('contenthtml_internal', $datafield_data) ? (string)$datafield_data['contenthtml_internal'] : '',
                    ]);
                }
                $pdata['jobad_containers'] = array_filter($pdata['jobad_containers'], static function(JobadContainer $c) {
                    // try to drop empty containers
                    $allowed_tags = ['img','a','ul','li','hr'];
                    return trim(strip_tags($c->content_external, $allowed_tags)) !== ''
                        ||trim(strip_tags($c->content_internal, $allowed_tags)) !== '';
                });
            }

            if(array_key_exists('tracking_code', $data['job_ad'])) {
                $pdata['tracking_code'] = (string)$data['job_ad']['tracking_code'];
            }

            if(array_key_exists('google_for_jobs_code', $data['job_ad'])) {
                $pdata['google_for_jobs_code'] = (string)$data['job_ad']['google_for_jobs_code'];
            }

        } else {

            if(array_key_exists('job_ad_url', $data)) {
                $pdata['jobad_url'] = (string)$data['job_ad_url'];
            }
            if(array_key_exists('job_ad_url_internal', $data)) {
                $pdata['jobad_url_internal'] = (string)$data['job_ad_url_internal'];
            }

        }

        if(array_key_exists('job_apply_url', $data)) {
            $pdata['apply_url'] = (string)$data['job_apply_url'];
        }
        if(array_key_exists('job_apply_url_internal', $data)) {
            $pdata['apply_url_internal'] = (string)$data['job_apply_url_internal'];
        }
        if(array_key_exists('job_pdf_url', $data)) {
            $pdata['pdf_url'] = (string)$data['job_pdf_url'];
        }
        if(array_key_exists('job_pdf_url_internal', $data)) {
            $pdata['pdf_url_internal'] = (string)$data['job_pdf_url_internal'];
        }
        if(array_key_exists('job_date_earlystart', $data)) {
            $pdata['earlystartdate'] = (string)$data['job_date_earlystart'];
        }
        if(array_key_exists('ba_is_published', $data)) {
            $pdata['ba_is_published'] = (bool)$data['ba_is_published'];
        }
        if(array_key_exists('lastupdate', $data)) {
            $pdata['lastupdate'] = (string)$data['lastupdate'];
        }
        if(array_key_exists('job_created_at', $data)) {
            $pdata['created_at'] = (string)$data['job_created_at'];
        }
        if(array_key_exists('job_date_from', $data)) {
            $pdata['date_from_public'] = (string)$data['job_date_from'];
        }
        if(array_key_exists('job_intern_date_from', $data)) {
            $pdata['date_from_internal'] = (string)$data['job_intern_date_from'];
        }

        if(array_key_exists('position_information', $data)) {

            $position_title = new PositionTitle();

            if(array_key_exists('job_position_title', $data['position_information'])) {
                $v = $data['position_information']['job_position_title'];
                $position_title = new PositionTitle([
                    'title_code' => $v['title_code'] ?? null,
                    'degree'     => $v['degree'] ?? null,
                    'course'     => $v['course'] ?? null
                ]);
            }

            $alternative_position_title = [];
            if(array_key_exists('alternative_job_position_title', $data['position_information'])) {
                foreach($data['position_information']['alternative_job_position_title'] as $v) {
                    $alternative_position_title[] = new PositionTitle([
                        'title_code' => $v['title_code'] ?? null,
                        'degree' => $v['degree'] ?? null,
                    ]);
                }
            }

            $position_description = new PositionDescription();

            if(array_key_exists('job_position_description', $data['position_information'])) {
                $v = $data['position_information']['job_position_description'];
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

            $_data = $data['position_information'];

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