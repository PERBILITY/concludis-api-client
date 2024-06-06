<?php
/**
 * Created by PhpStorm.
 * User: tmaass
 * Date: 05.09.2018
 * Time: 21:40
 */

namespace Concludis\ApiClient\Resources;


use Concludis\ApiClient\Storage\LocationRepository;
use Concludis\ApiClient\Storage\RegionRepository;
use Exception;

class Location {

    /**
     * @var string
     */
    public string $source_id;

    /**
     * @var int
     */
    public int $id;

    /**
     * @var string
     */
    public string $external_id = '';

    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var string
     */
    public string $country_code = '';

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
    public string $address = '';

    /**
     * @var string
     */
    public string $custom1 = '';

    /**
     * @var string
     */
    public string $custom2 = '';

    /**
     * @var string
     */
    public string $custom3 = '';

    /**
     * @var float|null
     */
    public ?float $lat = null;

    /**
     * @var float|null
     */
    public ?float $lon = null;

    public int $geocoding_source = 0;

    /**
     * @var Element|null
     */
    public ?Element $region = null;


    public const GEOCODING_SOURCE_ORIGIN = 0;
    public const GEOCODING_SOURCE_GOOGLE = 1;
    public const GEOCODING_SOURCE_FALLBACK = 2;

    public function __construct(array $data = []) {

        $this->source_id = (string)($data['source_id'] ?? '');

        if(array_key_exists('id', $data)) {
            $this->id = (int)$data['id'];
        } else if(array_key_exists('location_id', $data)) {
            $this->id = (int)$data['location_id'];
        }

        $this->external_id = (string)($data['external_id'] ?? '');

        if(array_key_exists('name', $data)) {
            $this->name = (string)$data['name'];
        }
        if(array_key_exists('country_code', $data)) {
            $this->country_code = (string)$data['country_code'];
        }
        if(array_key_exists('postal_code', $data)) {
            $this->postal_code = (string)$data['postal_code'];
        }
        if(array_key_exists('locality', $data)) {
            $this->locality = (string)$data['locality'];
        }
        if(array_key_exists('address', $data)) {
            $this->address = (string)$data['address'];
        }
        if(array_key_exists('custom1', $data)) {
            $this->custom1 = (string)$data['custom1'];
        } else if(array_key_exists('custom_text1', $data)) {
            $this->custom1 = (string)$data['custom_text1'];
        }
        if(array_key_exists('custom2', $data)) {
            $this->custom2 = (string)$data['custom2'];
        } else if(array_key_exists('custom_text2', $data)) {
            $this->custom2 = (string)$data['custom_text2'];
        }
        if(array_key_exists('custom3', $data)) {
            $this->custom3 = (string)$data['custom3'];
        } else if(array_key_exists('custom_text3', $data)) {
            $this->custom3 = (string)$data['custom_text3'];
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
        if(array_key_exists('region', $data)) {
            if($data['region'] instanceof Element){
                $this->region = $data['region'];
            }
        } else if(array_key_exists('region_id', $data) && $data['region_id'] > 0) {
            try {
                $this->region = RegionRepository::fetchById($this->source_id, $data['region_id']);
            } catch (Exception) {}
        }

        if(array_key_exists('geocoding_source', $data)) {
            $this->geocoding_source = (int)$data['geocoding_source'];
        }

        LocationRepository::fulfillLatLonFromCache($this);
    }

    public function distanceTo(float $lat, float $lon): ?float {

        if($this->lat === null || $this->lon === null) {
            return null;
        }

        $earthRadius = 6371000;

        // convert from degrees to radians
        $latFrom = deg2rad($lat);
        $lonFrom = deg2rad($lon);
        $latTo = deg2rad($this->lat);
        $lonTo = deg2rad($this->lon);

        $lonDelta = $lonTo - $lonFrom;
        $a = ((cos($latTo) * sin($lonDelta)) ** 2) +
            ((cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta)) ** 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return round($angle * $earthRadius, 2);
    }

    public function getInternArray(): array {
        $intern = array();
        if(!empty($this->intern_freetext1)) {
            $intern[] = $this->intern_freetext1;
        }
        if(!empty($this->intern_freetext2)) {
            $intern[] = $this->intern_freetext2;
        }
        if(!empty($this->intern_freetext3)) {
            $intern[] = $this->intern_freetext3;
        }

        return $intern;
    }

    public function getDisplayParts($with_custom = false): array {

        $parts = [];
        if(!empty($this->name)) {
            $parts[] = $this->name;
        }
        if(!empty($this->address) && $this->address !== $this->name) {
            $parts[] = $this->address;
        }

        $not_empty_zip_code = !empty(trim($this->postal_code));
        $not_empty_city = !empty(trim($this->locality));
        $not_empty_country_code = !empty(trim($this->country_code));

        if($not_empty_zip_code && $not_empty_city && $not_empty_country_code) {
            $parts[] = trim($this->country_code . '-' . $this->postal_code . ' ' . $this->locality);
        } else if($not_empty_city && $not_empty_country_code) {
            $parts[] = trim($this->country_code . '-' . $this->locality);
        } else if($not_empty_city) {
            $parts[] = trim($this->locality);
        }

        if ($with_custom) {
            $intern = [];
            if(!empty($this->custom1)) {
                $intern[] = $this->custom1;
            }
            if(!empty($this->custom2)) {
                $intern[] = $this->custom2;
            }
            if(!empty($this->custom3)) {
                $intern[] = $this->custom3;
            }
            foreach($intern as $v) {
                $parts[] = trim($v);
            }
        }

        return array_filter($parts);
    }

    public function getDisplayName($with_custom = false): string {

        $parts = $this->getDisplayParts($with_custom);

        return implode(', ', $parts);
    }


}