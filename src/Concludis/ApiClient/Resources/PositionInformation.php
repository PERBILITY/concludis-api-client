<?php
/**
 * @package:    concludis
 * @file:       PositionInformation.php
 * @version:    10.21.0
 *
 * @author:     concludis <aa@concludis.de>
 * @copyright:  concludis GmbH © 2007-2022
 * @created:    18.01.22
 * @link:       https://www.concludis.com
 */

namespace Concludis\ApiClient\Resources;

class PositionInformation {

    /**
     * @var int
     */
    public int $job_offer_type = 0;

    /**
     * @var int|null
     */
    public $education_type;

    /**
     * @var int|null
     */
    public $degree_type;

    /**
     * @var string
     */
    public $position_title_description = '';

    /**
     * @var int|null
     */
    public $social_insurance;

    /**
     * @var PositionTitle
     */
    public $position_title;

    /**
     * @var PositionTitle[]
     */
    public $alternative_position_title = [];

    /**
     * @var PositionDescription
     */
    public $position_description;


    public function __construct(array $data = []) {

        if (array_key_exists('job_offer_type', $data)) {
            $this->job_offer_type = (int)$data['job_offer_type'];
        }
        if (array_key_exists('education_type', $data) && (int)$data['education_type'] > 0) {
            $this->education_type = (int)$data['education_type'];
        }
        if (array_key_exists('degree_type', $data) && (int)$data['degree_type'] > 0) {
            $this->degree_type = (int)$data['degree_type'];
        }
        if (array_key_exists('position_title_description', $data)) {
            $this->position_title_description = (string)$data['position_title_description'];
        }
        if (array_key_exists('social_insurance', $data) && $data['social_insurance'] !== null) {
            $this->social_insurance = (int)$data['social_insurance'];
        }

        $this->position_title = new PositionTitle();

        if (array_key_exists('position_title', $data)) {
            if($data['position_title'] instanceof PositionTitle) {
                $this->position_title = $data['position_title'];
            } else if(is_array($data['position_title'])) {
                $this->position_title = new PositionTitle($data['position_title']);
            }
        }

        if (array_key_exists('alternative_position_title', $data) && is_array($data['alternative_position_title'])) {
            foreach($data['alternative_position_title'] as $t) {
                if($t instanceof PositionTitle) {
                    $this->alternative_position_title[] = $t;
                } else if(is_array($t)) {
                    $this->alternative_position_title[] = new PositionTitle($t);
                }
            }
        }

        $this->position_description = new PositionDescription();

        if (array_key_exists('position_description', $data)) {
            if($data['position_description'] instanceof PositionDescription) {
                $this->position_description = $data['position_description'];
            } else if(is_array($data['position_description'])) {
                $this->position_description = new PositionDescription($data['position_description']);
            }
        }

    }

}