<?php


namespace Concludis\ApiClient\Util;


class CliUtil {

    public static function output(string $msg): void {
        echo $msg . PHP_EOL;
    }

    public static function showStatus($done, $total, $size = 30): void {

        static $start_time = null;

        // if we go over our bound, just ignore it
        if ($done > $total) {
            return;
        }
        if ($start_time === null) {
            $start_time = time();
        }
        $now = time();

        if ((int)$total === 0) {
            $perc = 1;
        } else {
            $perc = (double)($done / $total);
        }
        $bar = floor($perc * $size);

        $status_bar = "\r[";
        $status_bar .= str_repeat('=', $bar);
        if ($bar < $size) {
            $status_bar .= '>';
            $status_bar .= str_repeat(' ', $size - $bar);
        } else {
            $status_bar .= '=';
        }

        $disp = number_format($perc * 100);

        $status_bar .= "] $disp% $done/$total";

        $rate = ($done === 0) ? 0 : (($now - $start_time) / $done);
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar .= ' remaining: ' . number_format($eta) . ' sec. elapsed: ' . number_format($elapsed) . ' sec.';

        echo "$status_bar ";

        flush();

        // when done, send a newline
        if ($done === $total) {
            echo "\n";
            $start_time = null;
        }
    }
}