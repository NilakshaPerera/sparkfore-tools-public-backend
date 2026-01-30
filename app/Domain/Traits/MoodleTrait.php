<?php

namespace App\Domain\Traits;

trait MoodleTrait
{
    /**
     * Get Moodle version based on the version string.
     *
     * @param string $versionString
     * @return string
     */
    public function findMoodleVersion($versionString) : string
    {
        $versionMap = [
            '2002' => ['01' => '1.0'],
            '2003' => ['01' => '1.1'],
            '2004' => ['01' => '1.2'],
            '2005' => ['01' => '1.4'], // Moodle version 1.3 was skipped
            '2006' => ['01' => '1.5'],
            '2007' => ['01' => '1.6'],
            '2008' => ['01' => '1.9'], // Moodle versions 1.7 and 1.8 were released
            '2009' => ['01' => '1.9'],
            '2010' => ['01' => '2.0'],
            '2011' => ['01' => '2.1'],
            '2012' => ['01' => '2.2'],
            '2013' => ['01' => '2.4'], // Moodle version 2.3 was skipped
            '2014' => ['01' => '2.7'], // Moodle versions 2.5 and 2.6 were skipped
            '2015' => ['01' => '2.8'],
            '2016' => ['01' => '3.1'],
            '2017' => [
                '01' => '3.2', // Released in December 2016
                '06' => '3.3', // Released in June 2017
            ],
            '2018' => ['01' => '3.4'], // Released in November 2017
            '2019' => ['01' => '3.5'],
            '2020' => ['01' => '3.6'],
            '2021' => [
                '01' => '3.7', // Released in May 2019
                '07' => '3.8', // Released in November 2019
                '12' => '3.9', // Released in November 2020
            ],
            '2022' => ['01' => '4.0'],
            '2023' => ['01' => '4.1'],
            '2024' => ['01' => '4.2'], // Update this as needed
        ];

         // Extract the date part of the version string
        $datePart = substr($versionString, 0, 8); // Get the YYYYMMDD part
        $year = substr($datePart, 0, 4); // Extract the year
        $month = substr($datePart, 4, 2); // Extract the month

        // Default return if no version is found
        $closestVersion = '';

        // Find the closest version for the given date
        foreach ($versionMap as $mapYear => $months) {
            if ($year <= $mapYear) {
                // If the year is less than or equal to the current map year
                $yearMonth = $months[$month] ?? null;
                if ($yearMonth) {
                    return $yearMonth;
                }

                // If no exact month match, look for the closest month
                $sortedMonths = array_keys($months);
                usort($sortedMonths, function ($a, $b) use ($month) {
                    return abs($a - $month) - abs($b - $month);
                });

                foreach ($sortedMonths as $sortedMonth) {
                    if ($sortedMonth >= $month) {
                        $closestVersion = $months[$sortedMonth];
                        break;
                    }
                }
                break;
            }
        }

        // Return the closest version found or an empty string
        return $closestVersion ?: '';
    }
}
