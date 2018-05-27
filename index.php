<?php
include('lib/PDF2TEXT.php');
/**
 * Created by PhpStorm.
 * User: Jonas Schroeter
 * Date: 27.05.2018
 * Time: 13:23
 */
downloadMensaplan();
function downloadMensaplan()
{
    $week = date("W");
    $year = date("Y");
    $day = date("l");
    if ($day == "Saturday" || $day == "Sunday") {
        $week++;
    }

    $fileLastUpdate = fopen("./lastUpdate.json", "r") or die("Unable to open file!");
    $jsonLastUpdate = fread($fileLastUpdate, filesize("lastUpdate.json"));
    if (json_decode($jsonLastUpdate)[5] == $week) {
        echo json_decode($jsonLastUpdate);
        fclose($fileLastUpdate);
        return;
    }
    fclose($fileLastUpdate);

    $url = "https://www.liebigmensaservice.de/speiseplan/sppl-asbhelmholtz-" . $week . $year . ".pdf";

    $a = new PDF2Text();
    $a->setFilename($url);
    $a->decodePDF();
    $pdfString = $a->output();
    $pdfString = trim(preg_replace('/\s+/', ' ', $pdfString));
    $pdfString = utf8_encode($pdfString);

    $pdfString = substr($pdfString, strpos($pdfString, "vegan") + 5);
    $pdfString = substr($pdfString, 0, strpos($pdfString, "Tagen") - 11);
    $dayArray = array();
    $days = array("Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
    for ($i = 0; $i < sizeof($days); $i++) {
        $dayArray[$i] = substr($pdfString, 0, strpos($pdfString, $days[$i]));
        if ($i == sizeof($days) - 1) {
            $dayArray[$i] = $pdfString;
        }
        $pdfString = substr($pdfString, strpos($pdfString, $days[$i]));
        //echo $dayArray[$i] . "<br>";
    }
    $dayArray[5] = $week . $year;
    $jsonString = json_encode($dayArray);
    echo $jsonString;
    $fileLastUpdate = fopen("./lastUpdate.json", "w+") or die("Unable to open file!");
    fwrite($fileLastUpdate, $jsonString);
    fclose($fileLastUpdate);
}