<?php

namespace Concludis\ApiClient\Service;

use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Location;
use Concludis\ApiClient\Storage\CacheRepository;
use Concludis\ApiClient\Storage\LocationRepository;
use Concludis\ApiClient\Storage\ProjectRepository;
use Exception;
use RuntimeException;

class GeocodingService {
    /**
     * @param int $limit
     * @return void
     * @throws Exception
     */
    public static function doAutoGeocoding(int $limit = 50): void  {
        $stack = LocationRepository::fetchGeocodableLocationsStack($limit);

        foreach ($stack as $location) {

            $has_geocode = $location->lat !== null && $location->lon !== null;
            $source_fallback = $location->geocoding_source === Location::GEOCODING_SOURCE_FALLBACK;

            if($has_geocode && !$source_fallback) {
                return;
            }

            if(empty($location->country_code)
                || empty($location->postal_code)
                || empty($location->locality)
                || empty($location->address)) {
                continue;
            }

            try {

                $result = self::geocodeLatLon(
                    $location->address,
                    $location->postal_code,
                    $location->locality,
                    $location->country_code
                );

                $key = 'geocode:' . sha1(
                        $location->country_code . '::' .
                        $location->postal_code . '::' .
                        $location->locality . '::' .
                        $location->address
                    );

                $cache_data = [
                    'request' => [
                        'address' => $location->address,
                        'postal_code' => $location->postal_code,
                        'locality' => $location->locality,
                        'country_code' => $location->country_code,
                    ],
                    'response' => [
                        'lat' => $result['lat'],
                        'lon' => $result['lon'],
                    ]
                ];

                $location->lon = (float)$result['lon'];
                $location->lat = (float)$result['lat'];
                $location->geocoding_source = Location::GEOCODING_SOURCE_GOOGLE;

                $pdo = PDO::getInstance();

                try {

                    $pdo->beginTransaction();

                    CacheRepository::cache($key, $cache_data);

                    if(LocationRepository::save($location)) {
                        ProjectRepository::updateProjectLocationsLatLon($location);
                    }

                    $pdo->commit();

                } catch (Exception) {
                    $pdo->rollBack();
                }

            } catch (Exception) {}
        }
    }

    /**
     * @param string $address
     * @param string $postalCode
     * @param string $city
     * @param string $countryCode
     * @return array
     * @throws Exception
     */
    public static function geocodeLatLon(string $address, string $postalCode, string $city, string $countryCode): array {

        $apiKey = Baseconfig::$google_maps_api_key;
        if($apiKey === null) {
            throw new RuntimeException('google_maps_api_key not defined');
        }

        // Erstellen der vollständigen Adresse
        $full_address = urlencode($address . ', ' . $postalCode . ' ' . $city . ', ' . $countryCode);

        $url = sprintf(
            "https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s",
            $full_address,
            $apiKey
        );

        // cURL initialisieren
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // API-Aufruf durchführen
        $response = curl_exec($ch);

        // wait 20 milliseconds because we are allowed to send max 50 requests/sec
        usleep(20000);

        // Error-Handling für cURL
        $curl_errno = curl_errno($ch);
        if ($curl_errno) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException($error_msg, $curl_errno);
        }

        curl_close($ch);

        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if ($response['status'] === 'OK') {
            $lat = $response['results'][0]['geometry']['location']['lat'] ?? null;
            $lon = $response['results'][0]['geometry']['location']['lng'] ?? null;
            if($lat === null || $lon === null) {
                throw new RuntimeException('status OK but lat/lon not found');
            }
            return ['lat' => $lat, 'lon' => $lon];
        }

        throw new RuntimeException('invalid response status: ' . ($response['status'] ?? ''));

    }

}