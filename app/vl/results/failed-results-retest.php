<?php


// echo "<pre>";print_r($_POST['bulkIds']);die;

use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;

try {
    /** @var MysqliDb $db */
    $db = ContainerRegistry::get('db');

    /** @var CommonService $general */
    $general = ContainerRegistry::get(CommonService::class);

    // Sanitized values from $request object
    /** @var Laminas\Diactoros\ServerRequest $request */
    $request = $GLOBALS['request'];
    $_POST = $request->getParsedBody();

    $sarr = $general->getSystemConfig();
    /* Status definition */
    $status = 6;
    if ($_SESSION['instanceType'] == 'remoteuser' && $_SESSION['accessType'] == 'collection-site') {
        $status = 9;
    }
    $query = "SELECT sample_code, remote_sample_code, facility_id, sample_batch_id, result, result_status, vl_sample_id FROM form_vl";
    if ($_POST['bulkIds'] && is_array($_POST['vlId'])) {
        $query .= " WHERE vl_sample_id IN (" . implode(",", $_POST['vlId']) . ")";
    } else {
        $query .= " WHERE vl_sample_id = " . base64_decode($_POST['vlId']);
    }
    $response = $db->rawQuery($query);

    if ($_POST['bulkIds'] && is_array($_POST['vlId'])) {
        $db = $db->where("`vl_sample_id` IN (" . implode(",", $_POST['vlId']) . ")");
    } else {
        $db = $db->where('vl_sample_id', base64_decode($_POST['vlId']));
    }
    $id = $db->update("form_vl", array(
        "result_value_log"              => null,
        "result_value_absolute"         => null,
        "result_value_text"             => null,
        "result_value_absolute_decimal" => null,
        "result"                        => null,
        "sample_batch_id"               => null,
        "result_status"                 => $status
    ));

    if ($id > 0 && !empty($response)) {
        foreach ($response as $result) {
            if (isset($result['vl_sample_id']) && $result['vl_sample_id'] != "") {
                $db->insert('failed_result_retest_tracker', array(
                    'test_type_pid'         => (isset($result['vl_sample_id']) && $result['vl_sample_id'] != "") ? $result['vl_sample_id'] : null,
                    'test_type'             => 'vl',
                    'sample_code'           => (isset($result['sample_code']) && $result['sample_code'] != "") ? $result['sample_code'] : null,
                    'remote_sample_code'    => (isset($result['remote_sample_code']) && $result['remote_sample_code'] != "") ? $result['remote_sample_code'] : null,
                    'batch_id'              => (isset($result['sample_batch_id']) && $result['sample_batch_id'] != "") ? $result['sample_batch_id'] : null,
                    'facility_id'           => (isset($result['facility_id']) && $result['facility_id'] != "") ? $result['facility_id'] : null,
                    'result'                => (isset($result['result']) && $result['result'] != "") ? $result['result'] : null,
                    'result_status'         => (isset($result['result_status']) && $result['result_status'] != "") ? $result['result_status'] : null,
                    'updated_datetime'      => DateUtility::getCurrentDateTime(),
                    'update_by'             => $_SESSION['userId']
                ));
            }
        }
    }
    echo htmlspecialchars($id);
}
//catch exception
catch (Exception $e) {
    throw new SystemException($e->getMessage(), $e->getCode(), $e);
}
