<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Grei\TanggalMerah;

class TanggalMerahService
{
    private ?TanggalMerah $checker = null;

    public function __construct()
    {
        try {
            $previousErrorReporting = error_reporting();
            error_reporting($previousErrorReporting & ~E_DEPRECATED);
            $this->checker = new TanggalMerah();
            error_reporting($previousErrorReporting);
        } catch (\Throwable $e) {
            $this->checker = null;

            if (isset($previousErrorReporting)) {
                error_reporting($previousErrorReporting);
            }
        }
    }

    /**
     * @param CarbonInterface $date
     * @return array{is_red: bool, is_national_holiday: bool, is_sunday: bool, label: string|null}
     */
    public function getDateInfo(CarbonInterface $date): array
    {
        $isSunday = $date->isSunday();

        if (! $this->checker) {
            return [
                'is_red' => $isSunday,
                'is_national_holiday' => false,
                'is_sunday' => $isSunday,
                'label' => $isSunday ? 'Minggu' : null,
            ];
        }

        try {
            $this->checker->event = [];
            $this->checker->set_date($date->toDateString());
            $isRed = $this->checker->check();
            $events = $this->checker->get_event();

            $holidayLabels = array_values(array_filter($events, fn (string $event): bool => strtolower($event) !== 'sunday'));

            return [
                'is_red' => (bool) $isRed,
                'is_national_holiday' => ! empty($holidayLabels),
                'is_sunday' => $isSunday,
                'label' => $holidayLabels[0] ?? ($isSunday ? 'Minggu' : null),
            ];
        } catch (\Throwable $e) {
            return [
                'is_red' => $isSunday,
                'is_national_holiday' => false,
                'is_sunday' => $isSunday,
                'label' => $isSunday ? 'Minggu' : null,
            ];
        }
    }
}
