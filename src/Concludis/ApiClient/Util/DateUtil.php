<?php
/**
 * @package:    concludis
 * @file:       DateUtil.php
 * @version:    1.0.0
 *
 * @author:     concludis <aa@concludis.de>
 * @copyright:  concludis GmbH © 2007-2021
 * @created:    29.12.21
 * @link:       https://www.concludis.com
 */

namespace Concludis\ApiClient\Util;

use DateTime;
use DateTimeZone;

class DateUtil {

    /**
     * @param string $date Mysql datein format "Y-m-d"
     * @return DateTime|null
     */
    public static function parseMysqlDate(string $date): ?DateTime {

        if (!empty($date) && $date !== '0000-00-00') {
            $dt = DateTime::createFromFormat('Y-m-d', $date, new DateTimeZone('Europe/Berlin'));
            if ($dt instanceof DateTime) {
                return $dt;
            }
        }
        return null;
    }


    /**
     * @param $iso_8601_string
     * @param DateTimeZone|null $timezone
     * @param int|null $accuracy
     * @return DateTime|null
     */
    public static function parseIso8601($iso_8601_string, DateTimeZone $timezone = null, ?int &$accuracy = null): ?DateTime {

        if(empty($iso_8601_string)) {
            return null;
        }

        $formats = [
            ['format' => "Y-m-d\TH:i:s.u", 'accuracy' => 1],
            ['format' => "Y-m-d\TH:i:s.uP", 'accuracy' => 1],
            ['format' => "Y-m-d\TH:i:s", 'accuracy' => 2],
            ['format' => "Y-m-d\TH:i:sP", 'accuracy' => 2],
            ['format' => "Y-m-d\TH:i", 'accuracy' => 3],
            ['format' => "Y-m-d\TH:iP", 'accuracy' => 3],
            ['format' => "Y-m-d\TH", 'accuracy' => 4],
            ['format' => "Y-m-d\THP", 'accuracy' => 4],
            ['format' => 'Y-m-d', 'accuracy' => 5],
            ['format' => 'Y-m-dP', 'accuracy' => 5],
            ['format' => 'Y-m', 'accuracy' => 6],
            ['format' => 'Y-mP', 'accuracy' => 6],
            ['format' => 'Y', 'accuracy' => 7],
            ['format' => 'YP', 'accuracy' => 7],
        ];

        foreach($formats as $f) {
            $test = DateTime::createFromFormat($f['format'], $iso_8601_string);

            if($test instanceof DateTime) {
                if($timezone !== null) {
                    $test->setTimezone($timezone);
                }
                $final = null;
                if($f['accuracy'] === 1) {
                    $final = DateTime::createFromFormat('Y-m-d H:i:s.uP', $test->format('Y-m-d H:i:s.uP'));
                }
                elseif($f['accuracy'] === 2) {
                    $final = DateTime::createFromFormat('Y-m-d H:i:s.uP', $test->format('Y-m-d H:i:s.0P'));
                }
                elseif($f['accuracy'] === 3) {
                    $final = DateTime::createFromFormat('Y-m-d H:i:s.uP', $test->format('Y-m-d H:i:00.0P'));
                }
                elseif($f['accuracy'] === 4) {
                    $final = DateTime::createFromFormat('Y-m-d H:i:s.uP', $test->format('Y-m-d H:00:00.0P'));
                }
                elseif($f['accuracy'] === 5) {
                    $final = DateTime::createFromFormat('Y-m-d H:i:s.uP', $test->format('Y-m-d 00:00:00.0P'));
                }
                elseif($f['accuracy'] === 6) {
                    $final = DateTime::createFromFormat('Y-m-d H:i:s.uP', $test->format('Y-m-01 00:00:00.0P'));
                }
                elseif($f['accuracy'] === 7) {
                    $final = DateTime::createFromFormat('Y-m-d H:i:s.uP', $test->format('Y-01-01 00:00:00.0P'));
                }
                if($final instanceof DateTime) {
                    $accuracy = $f['accuracy'];
                    return $final;
                }
            }
        }

        return null;

//        $results = array();
//        $results[] = \DateTime::createFromFormat("Y-m-d\TH:i:s", $iso_8601_string);
//        $results[] = \DateTime::createFromFormat("Y-m-d\TH:i:s.u", $iso_8601_string);
//        $results[] = \DateTime::createFromFormat("Y-m-d\TH:i:s.uP", $iso_8601_string);
//        $results[] = \DateTime::createFromFormat("Y-m-d\TH:i:sP", $iso_8601_string);
//        $results[] = \DateTime::createFromFormat("Y-m-d\TH:iP", $iso_8601_string);
//        $results[] = \DateTime::createFromFormat(DATE_ATOM, $iso_8601_string);
//        $results[] = \DateTime::createFromFormat('Y-m-d', $iso_8601_string);
//        $results[] = \DateTime::createFromFormat('Y-m', $iso_8601_string);
//        $results[] = \DateTime::createFromFormat('Y', $iso_8601_string);
//
//        $success = array_values(array_filter($results));
//        if (\count($success) > 0) {
//            return $success[0];
//        }
//        return false;
    }

}