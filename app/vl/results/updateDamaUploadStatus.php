<?php 

use App\Registries\ContainerRegistry;
use App\Utilities\DateUtility;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

$vlSampleIdList = $_POST['savedSampleIds'];
$result = 0;

if($vlSampleIdList != [] || ''){

    $colums = array();
    $colums['data_sync'] = 1;
    $colums['result_exported_datetime'] = DateUtility::getCurrentDateTime();
    foreach ($vlSampleIdList as $vlSampleId) {
        $db->where('vl_sample_id', $vlSampleId);
       $result =  $db->update('form_vl', $colums);
        
    }
}

echo "$result";

