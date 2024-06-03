<?php
/**
 * @package:    concludis
 * @file:       PositionDescription.php
 * @version:    1.0.0
 *
 * @author:     concludis <aa@concludis.de>
 * @copyright:  concludis GmbH © 2007-2022
 * @created:    19.01.22
 * @link:       https://www.concludis.com
 */

namespace Concludis\ApiClient\Resources;

class PositionDescription {

    /**
     * @var int|null
     */
    public $mini_job;

    /**
     * @var int|null
     */
    public $schedule_working_plan;

    /**
     * @var string|null
     */
    public $schedule_summary_text;

    /**
     * @var int|null
     */
    public $duration_temporary_or_regular;

    /**
     * @var string|null
     */
    public $duration_term_date;

    /**
     * @var int|null
     */
    public $duration_term_length;

    /**
     * @var int|null
     */
    public $duration_take_over;

    /**
     * @var string|null
     */
    public $salary;


    public function __construct(array $data = []) {

        if (array_key_exists('mini_job', $data) && $data['mini_job'] !== null) {
            $this->mini_job = (int)$data['mini_job'];
        }

        if (array_key_exists('schedule_working_plan', $data) && $data['schedule_working_plan'] !== null) {
            $this->schedule_working_plan = (int)$data['schedule_working_plan'];
        }

        if (array_key_exists('schedule_summary_text', $data) && $data['schedule_summary_text'] !== null) {
            $this->schedule_summary_text = (string)$data['schedule_summary_text'];
        }

        if (array_key_exists('duration_temporary_or_regular', $data) && $data['duration_temporary_or_regular'] !== null) {
            $this->duration_temporary_or_regular = (int)$data['duration_temporary_or_regular'];
        }

        if (array_key_exists('duration_term_date', $data) && $data['duration_term_date'] !== null) {
            $this->duration_term_date = (string)$data['duration_term_date'];
        }

        if (array_key_exists('duration_term_length', $data) && $data['duration_term_length'] !== null) {
            $this->duration_term_length = (int)$data['duration_term_length'];
        }

        if (array_key_exists('duration_take_over', $data) && $data['duration_take_over'] !== null) {
            $this->duration_take_over = (int)$data['duration_take_over'];
        }

        if (array_key_exists('salary', $data) && $data['salary'] !== null) {
            $this->salary = (string)$data['salary'];
        }

    }

}