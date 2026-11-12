<?php

namespace App\Support;

use App\Http\Utility\Tools;
use Illuminate\Support\Collection;

class PlanningBillingSummary
{
    /**
     * @param  iterable<int, object>  $planning
     * @return array{schools: Collection<int, array<string, mixed>>, hours: float, gain: float}
     */
    public static function fromPlanning(iterable $planning): array
    {
        $billingSchools = [];
        $hours = 0.0;
        $gain = 0.0;

        foreach ($planning as $event) {
            $sessionGain = Tools::planningGain($event->begin, $event->end, $event->rate, $event->billable_rate);
            $hours += (float) $event->session_length;
            $gain += $sessionGain;

            $schoolId = (int) $event->school_id;
            if (! isset($billingSchools[$schoolId])) {
                $billingSchools[$schoolId] = [
                    'id' => $schoolId,
                    'name' => $event->school_name,
                    'sessions' => 0,
                    'hours' => 0.0,
                    'amount_ht' => 0.0,
                    'unbilled_sessions' => 0,
                    'unbilled_amount_ht' => 0.0,
                ];
            }
            $billingSchools[$schoolId]['sessions']++;
            $billingSchools[$schoolId]['hours'] += (float) $event->session_length;
            $billingSchools[$schoolId]['amount_ht'] += $sessionGain;
            if ($event->invoice_id === null || $event->invoice_id === '') {
                $billingSchools[$schoolId]['unbilled_sessions']++;
                $billingSchools[$schoolId]['unbilled_amount_ht'] += $sessionGain;
            }
        }

        $schools = collect($billingSchools)
            ->map(function (array $school) {
                $school['amount_ttc'] = round($school['amount_ht'] * 1.2, 2);
                $school['unbilled_amount_ttc'] = round($school['unbilled_amount_ht'] * 1.2, 2);
                $school['amount_ht'] = round($school['amount_ht'], 2);
                $school['unbilled_amount_ht'] = round($school['unbilled_amount_ht'], 2);

                return $school;
            })
            ->sortByDesc(fn (array $school) => [$school['unbilled_sessions'], $school['unbilled_amount_ht'], $school['hours']])
            ->values();

        return [
            'schools' => $schools,
            'hours' => $hours,
            'gain' => $gain,
        ];
    }
}
