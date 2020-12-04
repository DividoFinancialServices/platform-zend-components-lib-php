<?php
class Divido_OptionsYears
{

    //
    // Years options based on your liking
    //
    static function getYearsOptions($count = 10, $direction = 'forwards', $fromDate = null) {

        $years = [];
        $fromDate = 2018;
        $df = ($direction == 'forwards') ? -1 : 1;

        for ($i = 0; $i < $count; $i++) {
            $year = date("Y", mktime(0, 0, 0, 1, 1, $fromDate - $i * $df));
            $years[] = ['key' => $year, 'label' => $year];
        }

        return $years;

    }

}

?>
