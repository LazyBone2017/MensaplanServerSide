<?php
include 'vendor/autoload.php';
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
    if (json_decode($jsonLastUpdate)[5][0] == $week) {
        echo json_decode($jsonLastUpdate);
        fclose($fileLastUpdate);
        return;
    }
    fclose($fileLastUpdate);

    $url = "https://www.liebigmensaservice.de/speiseplan/sppl-asbhelmholtz-" . $week . $year . ".pdf";

    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($url);

    $pdfString = $pdf->getText();
    $pdfString = nl2br($pdfString);

    $pdfString = substr($pdfString, strpos($pdfString, "vegan") + 5);
    $pdfString = substr($pdfString, 0, strpos($pdfString, "An allen"));
    //echo $pdfString;
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

    $mensaplan = array(array(), array(), array(), array(), array());
    for ($i = 0; $i < sizeof($mensaplan); $i++) { //menüs und deserts unterscheiden
        $dayArray[$i] = str_replace("\t", "", $dayArray[$i]);

        $mensaplan[$i][0] = substr($dayArray[$i], stripos($dayArray[$i], "\n"), stripos($dayArray[$i], "Dessert:") - stripos($dayArray[$i], "\n"));
        $dayArray[$i] = substr($dayArray[$i], stripos($dayArray[$i], "Dessert:"));
        $mensaplan[$i][1] = substr($dayArray[$i], stripos($dayArray[$i], "\n"), strripos($dayArray[$i], "Dessert:") - stripos($dayArray[$i], "\n"));
        $mensaplan[$i][2] = substr($dayArray[$i], stripos($dayArray[$i], "Dessert:"), stripos($dayArray[$i], "\n") - stripos($dayArray[$i], "Dessert:"));
    }

    $mensaplan[5] = array($week . $year);
    $jsonString = json_encode($mensaplan);
    $jsonString = str_replace("\\n", "", $jsonString);
    $jsonString = str_replace("<br \\/>", "", $jsonString);
    echo $jsonString;
    $fileLastUpdate = fopen("./lastUpdate.json", "w+") or die("Unable to open file!");
    fwrite($fileLastUpdate, $jsonString);
    fclose($fileLastUpdate);
}