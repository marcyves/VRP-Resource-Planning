<?php

namespace App\Http\Controllers;

use ICal\ICal;

class CalendarController extends Controller
{
    public function readICSFile(String $file)
    {
        $file = $file.".ics";

        $filePath = $filePath ?? storage_path('/calendar/' . $file);

        // Check if the file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        try {
    $ical = new ICal($filePath, array(
        'defaultSpan'                 => 2,     // Default value
        'defaultTimeZone'             => 'UTC',
        'defaultWeekStart'            => 'MO',  // Default value
        'disableCharacterReplacement' => false, // Default value
        'filterDaysAfter'             => null,  // Default value
        'filterDaysBefore'            => null,  // Default value
        'httpUserAgent'               => null,  // Default value
        'skipRecurrence'              => false, // Default value
    ));
} catch (\Exception $e) {
    die($e);
}

        $events = [];
        foreach ($ical->events() as $event) {
            //dd($event);
            $events[] = [
                'summary' => $event->summary ?? 'No Title',
                'description' => $event->description ?? 'No Description',
                'category' => $event->additionalProperties['categories'] ?? 'No Category',
                'start' =>  date('Y-m-d H:i:s', strtotime($event->dtstart)) ?? 'No Start Date',
                'end' =>  date('Y-m-d H:i:s', strtotime($event->dtend)) ?? 'No End Date'
            ];

            
        }

        // Return the parsed events as JSON
        return response()->json($events);
    }
}
