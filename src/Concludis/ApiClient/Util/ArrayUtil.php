<?php

namespace Concludis\ApiClient\Util;


class ArrayUtil {


    /**
     * Returns a unique array of cast non-zero integers by a given array.
     *
     * @param array $arr
     * @return array
     */
    public static function toIntArray(array $arr): array {
        $out = array();
        foreach ($arr as $v) {
            $out[] = (int)$v;
        }
        return array_unique(array_filter($out));
    }


    /**
     * Returns a unique array of cast non-empty, sanitized strings by a given array.
     *
     * @param array $arr
     * @return array
     */
    public static function toStringArray(array $arr): array {
        $out = array();
        foreach ($arr as $v) {
            $out[] = trim((string)$v);
        }
        return array_unique(array_filter($out));
    }
}