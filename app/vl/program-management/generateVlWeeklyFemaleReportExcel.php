<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

if (isset($_SESSION['vlStatisticsFemaleQuery']) && trim($_SESSION['vlStatisticsFemaleQuery']) != "") {
    $filename = '';
    $rResult = $db->rawQuery($_SESSION['vlStatisticsFemaleQuery']);

    $excel = new Spreadsheet();
    $output = [];
    $sheet = $excel->getActiveSheet();

    $headings = array("Province/State", "District/County", "Site Name", "Total Female", "Pregnant <=1000 cp/ml", "Pregnant >1000 cp/ml", "Breastfeeding <=1000 cp/ml", "Breastfeeding >1000 cp/ml", "Age > 15 <=1000 cp/ml", "Age > 15 >1000 cp/ml", "Age Unknown <=1000 cp/ml", "Age Unknown >1000 cp/ml", "Age <=15 <=1000 cp/ml", "Age <=15 >1000 cp/ml");

    $colNo = 1;

    $styleArray = array(
        'font' => array(
            'bold' => true,
            'size' => '13',
        ),
        'alignment' => array(
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ),
        'borders' => array(
            'outline' => array(
                'style' => Border::BORDER_THIN,
            ),
        )
    );

    $borderStyle = array(
        'alignment' => array(
            //  'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ),
        'borders' => array(
            'outline' => array(
                'style' => Border::BORDER_THIN,
            ),
        )
    );

    $sheet->mergeCells('A1:N1');
    $nameValue = '';
    foreach ($_POST as $key => $value) {
        if (trim($value) != '' && trim($value) != '-- Select --') {
            $nameValue .= str_replace("_", " ", $key) . " : " . $value . ",&nbsp;&nbsp;";
        }
    }
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNo) . '1', html_entity_decode($nameValue));

    foreach ($headings as $field => $value) {
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNo) . '3', html_entity_decode($value));
        $colNo++;
    }
    $sheet->getStyle('A3:N3')->applyFromArray($styleArray);

    foreach ($rResult as $aRow) {

        $row = [];
        $row[] = ($aRow['facility_state']);
        $row[] = ($aRow['facility_district']);
        $row[] = ($aRow['facility_name']);
        $row[] = $aRow['totalFemale'];
        $row[] = $aRow['pregSuppressed'];
        $row[] = $aRow['pregNotSuppressed'];
        $row[] = $aRow['bfsuppressed'];
        $row[] = $aRow['bfNotSuppressed'];
        $row[] = $aRow['gt15suppressedF'];
        $row[] = $aRow['gt15NotSuppressedF'];
        $row[] = $aRow['ltUnKnownAgesuppressed'];
        $row[] = $aRow['ltUnKnownAgeNotSuppressed'];
        $row[] = $aRow['lt15suppressed'];
        $row[] = $aRow['lt15NotSuppressed'];
        $output[] = $row;
    }

    $start = (count($output)) + 2;
    foreach ($output as $rowNo => $rowData) {
        $colNo = 1;
        foreach ($rowData as $field => $value) {
            $rRowCount = $rowNo + 4;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNo) . $rRowCount, html_entity_decode($value));

            $colNo++;
        }
    }
    $writer = IOFactory::createWriter($excel, 'Xlsx');
    $filename = 'VLSM-VL-Lab-Female-Weekly-Report-' . date('d-M-Y-H-i-s') . '.xlsx';
    $writer->save(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
    echo $filename;
} else {
    echo '';
}
