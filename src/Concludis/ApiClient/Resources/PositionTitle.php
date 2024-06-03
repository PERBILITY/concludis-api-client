<?php
/**
 * @package:    concludis
 * @file:       PositionTitle.php
 * @version:    1.0.0
 *
 * @author:     concludis <aa@concludis.de>
 * @copyright:  concludis GmbH © 2007-2022
 * @created:    19.01.22
 * @link:       https://www.concludis.com
 */

namespace Concludis\ApiClient\Resources;

class PositionTitle {

    /**
     * @var int|null
     */
    public $title_code;

    /**
     * @var int|null
     */
    public $degree;

    /**
     * @var int|null
     */
    public $course;

    public function __construct(array $data = []) {

        if (array_key_exists('title_code', $data)) {
            $this->title_code = (int)$data['title_code'];
        }

        if (array_key_exists('degree', $data) && (int)$data['degree'] > 0) {
            $this->degree = (int)$data['degree'];
        }

        if (array_key_exists('course', $data) && (int)$data['course'] > 0) {
            $this->course = (int)$data['course'];
        }

    }

}