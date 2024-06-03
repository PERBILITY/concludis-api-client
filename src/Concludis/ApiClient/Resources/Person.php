<?php
/**
 * Created by PhpStorm.
 * User: tmaass
 * Date: 05.09.2018
 * Time: 21:41
 */

namespace Concludis\ApiClient\Resources;


class Person {

    /**
     * @var string
     */
    public $source_id;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $external_id;

    /**
     * @var string
     */
    public $gender;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $profile_image;

    /**
     * @var string
     */
    public $position;

    /**
     * @var string
     */
    public $department;

    /**
     * @var string
     */
    public $division;

    /**
     * @var string
     */
    public $organisation;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $address1;

    /**
     * @var string
     */
    public $address2;

    /**
     * @var string
     */
    public $postal_code;

    /**
     * @var string
     */
    public $locality;

    /**
     * @var string
     */
    public $country_code;

    /**
     * @var array
     */
    public array $social_media = [];


    public function __construct(array $data = []) {

        if(array_key_exists('source_id', $data)) {
            $this->source_id = (string)$data['source_id'];
        }
        if(array_key_exists('id', $data)) {
            $this->id = (int)$data['id'];
        }
        if(array_key_exists('external_id', $data) && !empty($data['external_id'])) {
            $this->external_id = (string)$data['external_id'];
        }
        if(array_key_exists('gender', $data)) {
            $this->gender = (string)$data['gender'];
        }
        if(array_key_exists('title', $data)) {
            $this->title = (string)$data['title'];
        }
        if(array_key_exists('firstname', $data)) {
            $this->firstname = (string)$data['firstname'];
        }
        if(array_key_exists('lastname', $data)) {
            $this->lastname = (string)$data['lastname'];
        }
        if(array_key_exists('profile_image', $data)) {
            $this->profile_image = (string)$data['profile_image'];
        } elseif(array_key_exists('url_profile_image', $data)) {
            // backward compatibility fallback
            $this->profile_image = (string)$data['url_profile_image'];
        }
        if(array_key_exists('position', $data)) {
            $this->position = (string)$data['position'];
        }
        if(array_key_exists('department', $data)) {
            $this->department = (string)$data['department'];
        }
        if(array_key_exists('division', $data)) {
            $this->division = (string)$data['division'];
        }
        if(array_key_exists('organisation', $data)) {
            $this->organisation = (string)$data['organisation'];
        }
        if(array_key_exists('email', $data)) {
            $this->email = (string)$data['email'];
        }
        if(array_key_exists('phone', $data)) {
            $this->phone = (string)$data['phone'];
        }
        if(array_key_exists('address1', $data)) {
            $this->address1 = (string)$data['address1'];
        }
        if(array_key_exists('address2', $data)) {
            $this->address2 = (string)$data['address2'];
        }
        if(array_key_exists('postal_code', $data)) {
            $this->postal_code = (string)$data['postal_code'];
        }
        if(array_key_exists('locality', $data)) {
            $this->locality = (string)$data['locality'];
        }
        if(array_key_exists('country_code', $data)) {
            $this->country_code = (string)$data['country_code'];
        }
        if(array_key_exists('social_media', $data)) {
            $this->social_media = (array)$data['social_media'];
        }

    }

}