<?php
if (session_status() == PHP_SESSION_NONE) {
     session_start();
}


use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$sarr = $general->getSystemConfig();

if (isset($_SESSION['highTbResult']) && trim($_SESSION['highTbResult']) != "") {
     error_log($_SESSION['highTbResult']);
     $rResult = $db->rawQuery($_SESSION['highTbResult']);

     $excel = new Spreadsheet();
     $output = [];
     $sheet = $excel->getActiveSheet();
     $headings = array('Sample Code', 'Remote Sample Code', "Facility Name", "Patient ART no.", "Patient's Name", "Sample Collection Date", "Sample Tested Date", "Lab Name", "VL Result in cp/ml");
     if ($sarr['sc_user_type'] == 'standalone') {
          if (($key = array_search("Remote Sample Code", $headings)) !== false) {
               unset($headings[$key]);
          }
     }

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


     $sheet->mergeCells('A1:AE1');
     $nameValue = '';

     $filters = array(
          'hvlSampleTestDate' => 'Sample Test Date',
          'hvlBatchCode' => 'Batch Code',
          'hvlSampleType' => 'Sample Type',
          'hvlFacilityName' => 'Facility Name',
          'hvlContactStatus' => 'Contact Status',
          'hvlGender' => 'Gender'
     );

     foreach ($_POST as $key => $value) {
          if (trim($value) != '' && trim($value) != '-- Select --' && trim($key) != 'markAsComplete') {
               $nameValue .= str_replace("_", " ", $key) . " : " . $value . "&nbsp;&nbsp;";
          }
     }
     $sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '1')
          ->setValueExplicit(html_entity_decode($nameValue));
     foreach ($headings as $field => $value) {
          $sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '3')
               ->setValueExplicit(html_entity_decode($value));
          $colNo++;
     }
     $sheet->getStyle('A3:A3')->applyFromArray($styleArray);
     $sheet->getStyle('B3:B3')->applyFromArray($styleArray);
     $sheet->getStyle('C3:C3')->applyFromArray($styleArray);
     $sheet->getStyle('D3:D3')->applyFromArray($styleArray);
     $sheet->getStyle('E3:E3')->applyFromArray($styleArray);
     $sheet->getStyle('F3:F3')->applyFromArray($styleArray);
     $sheet->getStyle('G3:G3')->applyFromArray($styleArray);
     $sheet->getStyle('H3:H3')->applyFromArray($styleArray);
     $sheet->getStyle('I3:I3')->applyFromArray($styleArray);
     if ($_SESSION['instanceType'] != 'standalone') {
          $sheet->getStyle('J3:J3')->applyFromArray($styleArray);
     }

     $vlSampleId = [];
     foreach ($rResult as $aRow) {
          $row = [];
          //sample collecion date
          $sampleCollectionDate = '';
          $sampleTestDate = '';
          if ($aRow['sample_collection_date'] != null && trim($aRow['sample_collection_date']) != '' && $aRow['sample_collection_date'] != '0000-00-00 00:00:00') {
               $expStr = explode(" ", $aRow['sample_collection_date']);
               $sampleCollectionDate =  date("d-m-Y", strtotime($expStr[0]));
          }
          if ($aRow['sample_tested_datetime'] != null && trim($aRow['sample_tested_datetime']) != '' && $aRow['sample_tested_datetime'] != '0000-00-00 00:00:00') {
               $expStr = explode(" ", $aRow['sample_tested_datetime']);
               $sampleTestDate =  date("d-m-Y", strtotime($expStr[0]));
          }

          if ($aRow['remote_sample'] == 'yes') {
               $decrypt = 'remote_sample_code';
          } else {
               $decrypt = 'sample_code';
          }
          $patientFname = $general->crypto('doNothing', $aRow['patient_name'], $aRow[$decrypt]);
          $patientMname = $general->crypto('doNothing', $aRow['patient_surname'], $aRow[$decrypt]);
          $row[] = $aRow['sample_code'];
          if ($_SESSION['instanceType'] != 'standalone') {
               $row[] = $aRow['remote_sample_code'];
          }
          $row[] = ($aRow['facility_name']);
          $row[] = $aRow['patient_id'];
          $row[] = ($patientFname . " " . $patientMname);
          $row[] = $sampleCollectionDate;
          $row[] = $sampleTestDate;
          $row[] = $aRow['labName'];
          $row[] = $aRow['result'];
          $vlSampleId[] = $aRow['vl_sample_id'];
          $output[] = $row;
     }
     if ($_POST['markAsComplete'] == 'true') {
          $vlId = implode(",", $vlSampleId);
     }
     if (isset($_SESSION['highTbResultCount']) && $_SESSION['highTbResultCount'] > 5000) {

          $fileName = TEMP_PATH . DIRECTORY_SEPARATOR . 'VLSM-High-TB-Report-' . date('d-M-Y-H-i-s') . '.csv';
          $file = new SplFileObject($fileName, 'w');
          $file->setCsvControl(",", "\r\n");
          $file->fputcsv($headings);
          foreach ($output as $row) {
               $file->fputcsv($row);
          }
          // we dont need the $file variable anymore
          $file = null;
          echo base64_encode($fileName);
     } else {
          $start = (count($output)) + 2;
          foreach ($output as $rowNo => $rowData) {
               $colNo = 1;
               $rRowCount = $rowNo + 4;
               foreach ($rowData as $field => $value) {
                    $sheet->setCellValue(
                         Coordinate::stringFromColumnIndex($colNo) . $rRowCount,
                         html_entity_decode($value)
                    );
                    $colNo++;
               }
          }
          $writer = IOFactory::createWriter($excel, 'Xlsx');
          $fileName = TEMP_PATH . DIRECTORY_SEPARATOR . 'VLSM-High-TB-Report' . date('d-M-Y-H-i-s') . '.xlsx';
          $writer->save($fileName);
          echo base64_encode($fileName);
     }
}
