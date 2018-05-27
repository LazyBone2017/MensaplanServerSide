<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
    <?php
    include('lib/PDF2TEXT.php');
    /**
     * Created by PhpStorm.
     * User: Jonas Schroeter
     * Date: 27.05.2018
     * Time: 13:23
     */
    downloadMensaplan();
    function downloadMensaplan(){
        $week = date("W");
        $year = date("Y");
        $day = date("l");
        if($day == "Saturday" || $day == "Sunday"){
            $week++;
        }
        $url = "https://www.liebigmensaservice.de/speiseplan/sppl-asbhelmholtz-" . $week . $year . ".pdf";

        $a = new PDF2Text();
        $a->setFilename($url);
        $a->decodePDF();
        $pdfString =  $a->output();

        $pdfString = substr($pdfString, strpos($pdfString, "vegan") + 5);
        $pdfString = substr($pdfString, 0, strpos($pdfString, "Tagen") - 11);
        $dayArray = array();
        $days = array("Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
        for($i = 0; $i < sizeof($days); $i++) {
            $dayArray[$i] = substr($pdfString, 0, strpos($pdfString, $days[$i]));
            if($i == sizeof($days) - 1){
                $dayArray[$i] = $pdfString;
            }
            $pdfString = substr($pdfString, strpos($pdfString, $days[$i]));
            echo $dayArray[$i] . "<br>";
        }
    }
    ?>
    </body>
</html>

