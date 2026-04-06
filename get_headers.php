<?php
require 'vendor/autoload.php';
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

$spreadsheet = $reader->load('usuarios-fisicos.xlsx');
$sheet = $spreadsheet->getActiveSheet();
$fisico = [];
foreach ($sheet->getRowIterator(1, 1) as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $fisico[] = $cell->getValue();
    }
}
echo "Físicos: " . json_encode($fisico) . "\n";

$spreadsheet = $reader->load('usuarios-digitais.xlsx');
$sheet = $spreadsheet->getActiveSheet();
$digital = [];
foreach ($sheet->getRowIterator(1, 1) as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $digital[] = $cell->getValue();
    }
}
echo "Digitais: " . json_encode($digital) . "\n";
