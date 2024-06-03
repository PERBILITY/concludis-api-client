<?php
/**
 * Created by PhpStorm.
 * User: tmaass
 * Date: 05.09.2018
 * Time: 21:45
 */

namespace Concludis\ApiClient\Resources;

class Company {

    /**
     * @var string
     */
    public string $source_id = '';

    /**
     * @var int
     */
    public int $id = 0;

    /**
     * @var int
     */
    public int $parent_id = 0;

    /**
     * @var string
     */
    public string $external_id = '';

    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var Element|null
     */
    public ?Element $industry = null;

    /**
     * @var string
     */
    public string $url_company_site = '';

    /**
     * @var string
     */
    public string $url_career_site = '';

    /**
     * @var string
     */
    public string $xing_profile_url;
    /**
     * @var string
     */
    public string $linkedin_profile_url;

    /**
     * @var string
     */
    public string $linkedin_reference;

    /**
     * @var string
     */
    public string $facebook = '';

    /**
     * @var string
     */
    public string $whatsapp = '';

    /**
     * @var string
     */
    public string $twitter = '';

    /**
     * @var string
     */
    public string $instagram = '';

    /**
     * @var string
     */
    public string $youtube = '';

    /**
     * @var string
     */
    public string $tiktok = '';


    /**
     * @var array|null
     */
    public ?array $kununu = null;

    /**
     * @var string|null
     */
    public ?string $url_logo = null;

    /**
     * @var string|null
     */
    public ?string $url_signet = null;

    /**
     * @var string|null
     */
    public ?string $headline_color = null;

    /**
     * @var string|null
     */
    public ?string $background_color = null;

    /**
     * @var string
     */
    public string $address = '';

    /**
     * @var string
     */
    public string $postal_code = '';

    /**
     * @var string
     */
    public string $locality = '';

    /**
     * @var string
     */
    public string $country_code = '';

    /**
     * @var string
     */
    public string $commercialregister = '';

    /**
     * @var bool
     */
    public bool $edu_auth = false;

    /**
     * @var string
     */
    public string $phone_number = '';

    /**
     * @var string
     */
    public string $invoice_email = '';

    /**
     * @var int
     */
    public int $gh_contact_id = 0;

    /**
     * @var string
     */
    public string $ba_account_id = '';

    /**
     * @var int
     */
    public int $ba_contact_id = 0;

    /**
     * @var int
     */
    public int $dp_contact_id = 0;

    /**
     * @var int
     */
    public int $dp_officer_id = 0;

    /**
     * @var int
     */
    public int $dp_responsible_company_id = 0;

    /**
     * @var int
     */
    public int $dp_inspecting_authority_id = 0;

    /**
     * @var int
     */
    public int $email_signature_id = 0;

    /**
     * @var array|null
     */
    public ?array $dataprivacy_statement = null;

    /**
     * @var array|null
     */
    public ?array $assigned_locations = null;

    /**
     * Company constructor.
     * @param array $data
     */
    public function __construct(array $data = []) {

        if(array_key_exists('source_id', $data)) {
            $this->source_id = (string)$data['source_id'];
        }
        if(array_key_exists('id', $data)) {
            $this->id = (int)$data['id'];
        }
        if(array_key_exists('parent_id', $data)) {
            $this->parent_id = (int)$data['parent_id'];
        }
        if(array_key_exists('external_id', $data) && !empty($data['external_id'])) {
            $this->external_id = (string)$data['external_id'];
        }
        if(array_key_exists('name', $data)) {
            $this->name = (string)$data['name'];
        }
        if (array_key_exists('industry', $data)) {
            if($data['industry'] instanceof Element) {
                $this->industry = $data['industry'];
            } else if(is_array($data['industry'])){
                $this->industry = new Element($data['industry']);
            }
        }
        if(array_key_exists('url_company_site', $data)) {
            $this->url_company_site = (string)$data['url_company_site'];
        }
        if(array_key_exists('url_career_site', $data)) {
            $this->url_career_site = (string)$data['url_career_site'];
        }

        $this->xing_profile_url = (string)($data['xing_profile_url'] ?? '');
        $this->linkedin_profile_url = (string)($data['linkedin_profile_url'] ?? '');
        $this->linkedin_reference = (string)($data['linkedin_reference'] ?? '');

        $this->facebook = (string)($data['facebook'] ?? '');
        $this->whatsapp = (string)($data['whatsapp'] ?? '');
        $this->twitter = (string)($data['twitter'] ?? '');
        $this->instagram = (string)($data['instagram'] ?? '');
        $this->youtube = (string)($data['youtube'] ?? '');
        $this->tiktok = (string)($data['tiktok'] ?? '');


        if(array_key_exists('kununu', $data)) {
            $this->kununu = (array)$data['kununu'];
        }

        if(array_key_exists('url_logo', $data)) {
            $this->url_logo = (string)$data['url_logo'];
        }
        if(array_key_exists('url_signet', $data)) {
            $this->url_signet = (string)$data['url_signet'];
        }
        if(array_key_exists('headline_color', $data) && !empty($data['headline_color'])) {
            $this->headline_color = (string)$data['headline_color'];
        }
        if(array_key_exists('background_color', $data) && !empty($data['background_color'])) {
            $this->background_color = (string)$data['background_color'];
        }
        if(array_key_exists('address', $data)) {
            $this->address = (string)$data['address'];
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
        if(array_key_exists('commercialregister', $data)) {
            $this->commercialregister = (string)$data['commercialregister'];
        }
        if(array_key_exists('edu_auth', $data)) {
            $this->edu_auth = (bool)$data['edu_auth'];
        }
        if(array_key_exists('phone_number', $data)) {
            $this->phone_number = (string)$data['phone_number'];
        }
        if(array_key_exists('invoice_email', $data)) {
            $this->invoice_email = (string)$data['invoice_email'];
        }
        if(array_key_exists('gh_contact_id', $data)) {
            $this->gh_contact_id = (int)$data['gh_contact_id'];
        }
        if(array_key_exists('ba_account_id', $data)) {
            $this->ba_account_id = (string)$data['ba_account_id'];
        }
        if(array_key_exists('ba_contact_id', $data)) {
            $this->ba_contact_id = (int)$data['ba_contact_id'];
        }
        if(array_key_exists('dp_contact_id', $data)) {
            $this->dp_contact_id = (int)$data['dp_contact_id'];
        }
        if(array_key_exists('dp_officer_id', $data)) {
            $this->dp_officer_id = (int)$data['dp_officer_id'];
        }
        if(array_key_exists('dp_responsible_company_id', $data)) {
            $this->dp_responsible_company_id = (int)$data['dp_responsible_company_id'];
        }
        if(array_key_exists('dp_inspecting_authority_id', $data)) {
            $this->dp_inspecting_authority_id = (int)$data['dp_inspecting_authority_id'];
        }
        if(array_key_exists('email_signature_id', $data)) {
            $this->email_signature_id = (int)$data['email_signature_id'];
        }

        if(array_key_exists('dataprivacy_statement', $data) && $data['dataprivacy_statement'] !== null) {
            $this->dataprivacy_statement = (array)$data['dataprivacy_statement'];
        }
        if(array_key_exists('assigned_locations', $data) && $data['assigned_locations'] !== null) {
            $this->assigned_locations = (array)$data['assigned_locations'];
        }

    }
}