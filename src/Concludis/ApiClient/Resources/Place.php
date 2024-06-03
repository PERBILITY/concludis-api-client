<?php


namespace Concludis\ApiClient\Resources;


class Place {

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $country_code;

    /**
     * @var string
     */
    public $postal_code;

    /**
     * @var string
     */
    public $state_code;

    /**
     * @var string
     */
    public $state_name;

    /**
     * @var string
     */
    public $province_code;

    /**
     * @var string
     */
    public $province_name;

    /**
     * @var string
     */
    public $community_code;

    /**
     * @var string
     */
    public $community_name;

    /**
     * @var float
     */
    public $lat;

    /**
     * @var float
     */
    public $lon;


    public function __construct(array $data = []) {

        if(array_key_exists('id', $data)) {
            $this->id = (int)$data['id'];
        }
        if(array_key_exists('name', $data)) {
            $this->name = (string)$data['name'];
        }
        if(array_key_exists('country_code', $data)) {
            $this->country_code = (string)$data['country_code'];
        }
        if(array_key_exists('postal_code', $data)) {
            $this->postal_code = (string)$data['postal_code'];
        }
        if(array_key_exists('state_code', $data)) {
            $this->state_code = (string)$data['state_code'];
        }
        if(array_key_exists('state_name', $data)) {
            $this->state_name = (string)$data['state_name'];
        }
        if(array_key_exists('province_code', $data)) {
            $this->province_code = (string)$data['province_code'];
        }
        if(array_key_exists('province_name', $data)) {
            $this->province_name = (string)$data['province_name'];
        }
        if(array_key_exists('community_code', $data)) {
            $this->community_code = (string)$data['community_code'];
        }
        if(array_key_exists('community_name', $data)) {
            $this->community_name = (string)$data['community_name'];
        }
        if(array_key_exists('lat', $data)
            && $data['lat'] !== null
            && (float)$data['lat'] !== 0.0) {
            $this->lat = (float)$data['lat'];
        }
        if(array_key_exists('lon', $data)
            && $data['lon'] !== null
            && (float)$data['lon'] !== 0.0) {
            $this->lon = (float)$data['lon'];
        }
        if($this->lat === null || $this->lon === null){
            $this->lat = null;
            $this->lon = null;
        }

    }
}