<?php
require_once "vendor/autoload.php";
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A1']);
$html =  file_get_contents('phihtml/txx.html');
$mpdf->WriteHTML($html);
$mpdf->Output('teaxa.pdf');

