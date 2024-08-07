<?php

namespace Concludis\ApiClient\Resources;

use Exception;
use RuntimeException;

class Salary {

    public const TYPE_STRUCTURED = 'structured';
    public const TYPE_TEXT = 'text';

    public const PERIOD_YEAR = 'year';
    public const PERIOD_MONTH = 'month';
    public const PERIOD_DAY = 'day';
    public const PERIOD_HOUR = 'hour';

    public string $type;

    public ?string $text = null;

    public ?string $period = null;
    public ?string $currency = null;
    public ?float $min = null;
    public ?float $max = null;

    /**
     * @param  array  $data
     * @throws Exception
     */
    public function __construct(array $data) {

        $type = (string)($data['type'] ?? '');

        if($type === self::TYPE_STRUCTURED) {
            $this->type = $type;
            $this->period = (string)($data['period'] ?? '');
            if(!in_array($this->period, self::periodEnum(), true)) {
                throw new RuntimeException('invalid period');
            }
            $this->currency = (string)($data['currency'] ?? '');

            if(is_numeric(($data['min'] ?? null))) {
                $this->min = (float)$data['min'];
            }
            if(is_numeric(($data['max'] ?? null))) {
                $this->max = (float)$data['max'];
            }
            if($this->min === null && $this->max === null) {
                throw new RuntimeException('missing min/max range');
            }
            if($this->min !== null && $this->max !== null && $this->min > $this->max) {
                throw new RuntimeException('invalid range');
            }
            return;
        }

        if($type === self::TYPE_TEXT) {
            $this->type = $type;
            $this->text = (string)($data['text'] ?? '');
            return;
        }

        throw new RuntimeException('invalid salary type');
    }


    public function toIndeedString(): string {
        if ($this->type === self::TYPE_STRUCTURED) {

            $min = $this->min ?? null;
            $max = $this->max ?? null;
            $period = $this->period;

            $map = [
                'EUR' => '€',
                'USD' => '$',
                'GBP' => '£'
            ];

            $currency = (string)($map[$this->currency] ?? $this->currency);

            if ($min !== null && $max !== null){
                if ($min === $max){
                    return sprintf("%s%s / %s", $currency,
                        number_format($min, 2, '.', ''),
                        $period
                    );
                }
                return sprintf("%s%s - %s%s / %s", $currency,
                    number_format($min, 2, '.', ''),
                    $currency,
                    number_format($max, 2, '.', ''),
                    $period
                );
            }

            if ($min !== null) {
                return sprintf("%s%s+ per %s", $currency,
                    number_format($min, 2, '.', ''),
                    $period
                );
            }

            if ($max !== null) {
                return sprintf("%s%s / %s", $currency,
                    number_format($max, 2, '.', ''),
                    $period
                );
            }
        }
        return '';

    }

    public static function periodEnum(): array {
        return [
            self::PERIOD_YEAR,
            self::PERIOD_MONTH,
            self::PERIOD_DAY,
            self::PERIOD_HOUR,
        ];
    }

    public static function fromArray(?array $data): ?Salary {
        if($data !== null) {
            try {
                return new Salary($data);
            } catch (Exception) {}
        }
        return null;
    }


}