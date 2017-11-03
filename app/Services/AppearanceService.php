<?php
namespace App\Services;

use App\Appearance;

class AppearanceService {

    public function getAllAppearances($spaceId) {

        $sortedAppearances = Appearance::where('spaceID', $spaceId)->orderBy('created_at', 'ASC')->get();
        $appearanceCount = count($sortedAppearances);

        if ( !empty($appearanceCount) ) {
            $firstAppearance = $sortedAppearances[0]->created_at;
            $firstYear = $firstAppearance->year;
            $firstMonth = $firstAppearance->month;

            $lastAppearance = $sortedAppearances[( $appearanceCount - 1 )]->created_at;
            $lastYear = $lastAppearance->year;
            $lastMonth = $lastAppearance->month;

            $yearSpan = (int)$lastYear - (int)$firstYear;

            $res = array();
            for ($year = 0; $year <= $yearSpan; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $joinsForMonth = count(Appearance::
                                           where('spaceID', $spaceId)
                                           ->whereYear('created_at', ( $firstYear + $year ) )
                                           ->whereMonth('created_at', ( $month ) )
                                           ->get()
                                     ); 
                    if ( !empty($joinsForMonth) ) { 
                        array_push($res, $joinsForMonth);
                    }
                }        
            }
            array_push(
                $res, 
                $firstYear, 
                $lastYear, 
                $firstMonth,
                $lastMonth 
            );
            return $res;
        }
    }

    /**
     * @param $spaceId
     * @return events@spaceID
     */
    public function getEventAppearances($spaceId) {
        // event
        $sortedAppearances = Appearance::where('spaceID', $spaceId)->where('eventID', '!=', NULL )->orderBy('created_at', 'ASC')->get();

        $appearanceCount = count($sortedAppearances);

        if ( !empty($appearanceCount) ) {
            $firstAppearance = $sortedAppearances[0]->created_at;
            $firstYear = $firstAppearance->year;
            $firstMonth = $firstAppearance->month;

            $lastAppearance = $sortedAppearances[( $appearanceCount - 1 )]->created_at;
            $lastYear = $lastAppearance->year;
            $lastMonth = $lastAppearance->month;

            $yearSpan = (int)$lastYear - (int)$firstYear;

            $res = array();
            for ($year = 0; $year <= $yearSpan; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $joinsForMonth = count(Appearance::
                                           where('spaceID', $spaceId)
                                           ->where('eventID', '!=', NULL)
                                           ->whereYear('created_at', ( $firstYear + $year ) )
                                           ->whereMonth('created_at', ( $month ) )
                                           ->get()
                                     ); 
                    if ( !empty($joinsForMonth) ) {
                        array_push($res, $joinsForMonth);
                    }
                }        
            }
            array_push(
                $res, 
                $firstYear, 
                $lastYear, 
                $firstMonth,
                $lastMonth 
            );
            return $res;

        }

    }

    public function getNonEventAppearances($spaceId, $occasion) {

        $sortedAppearances = Appearance::where('spaceID', $spaceId)->where('occasion', $occasion )->orderBy('created_at', 'ASC')->get();
        $appearanceCount = count($sortedAppearances);

        if ( !empty($appearanceCount) ) {
            $firstAppearance = $sortedAppearances[0]->created_at;
            $firstYear = $firstAppearance->year;
            $firstMonth = $firstAppearance->month;

            $lastAppearance = $sortedAppearances[( $appearanceCount - 1 )]->created_at;
            $lastYear = $lastAppearance->year;
            $lastMonth = $lastAppearance->month;

            $yearSpan = (int)$lastYear - (int)$firstYear;

            $res = array();
            for ($year = 0; $year <= $yearSpan; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $joinsForMonth = count(Appearance::
                                          where('spaceID', $spaceId)
                                          ->where('occasion', $occasion)
                                          ->whereYear('created_at', ( $firstYear + $year ) )
                                          ->whereMonth('created_at', ( $month ) )
                                          ->get()
                                     ); 
                    if ( !empty($joinsForMonth) ) { 
                        array_push($res, $joinsForMonth);
                    }
                }        
            }
            array_push(
                $res, 
                $firstYear, 
                $lastYear, 
                $firstMonth,
                $lastMonth 
            );
            return $res;
        }
    }

}