<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();

$tableName = "user_details";
$userName = $db->escape($_POST['username']);
$password = $db->escape($_POST['password']);


$general = new \Vlsm\Models\General();
$facilityDb = new \Vlsm\Models\Facilities();
$user = new \Vlsm\Models\Users();


if ($_POST["csrf_token"] != $_SESSION["csrf_token"]) {
    // Reset token
    unset($_SESSION["csrf_token"]);
    $_SESSION['alertMsg'] = _("Request expired. Please try to login again.");
    unset($_SESSION);
    header("location:/login.php");
}
//$dashboardUrl = $general->getGlobalConfig('vldashboard_url');
/* Crosss Login Block Start */
$_SESSION['logged'] = false;
$systemInfo = $general->getSystemConfig();

$_SESSION['instanceType'] = $systemInfo['sc_user_type'];
$_SESSION['instanceLabId'] = !empty($systemInfo['sc_testing_lab_id']) ? $systemInfo['sc_testing_lab_id'] : null;


if (isset($_GET['u']) && isset($_GET['t']) && SYSTEM_CONFIG['recency']['crosslogin']) {

    $_GET['u'] = $db->escape($_GET['u']);
    $_GET['t'] = $db->escape($_GET['t']);

    $_POST['username'] = base64_decode($_GET['u']);
    $crossLoginQuery = "SELECT `login_id`,`password`,`user_name` FROM user_details WHERE `login_id` = ?";
    $check = $db->rawQueryOne($crossLoginQuery, array($db->escape($_POST['username'])));
    $_POST['password'] = "";

    if ($check) {
        $passwordCrossLoginSalt = $check['password'] . SYSTEM_CONFIG['recency']['crossloginSalt'];
        $_POST['password'] = hash('sha256', $passwordCrossLoginSalt);
        $password = "";
        if ($_POST['password'] == $_GET['t']) {
            $password = $check['password'];
            $_SESSION['logged'] = true;
        }
    }
} else {
    if (!SYSTEM_CONFIG['recency']['crosslogin'] && !isset($_POST['username']) && !empty($_POST['username'])) {
        $_SESSION['alertMsg'] = _("Sorry! Recency cross-login has not been activated. Please contact system administrator.");
    }
}
/* Crosss Login Block End */

try {
    $adminCount = $db->getValue("user_details", "count(*)");
    if ($adminCount != 0) {
        if (isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) {

            $username = $db->escape($_POST['username']);
            $password = $db->escape($_POST['password']);

            /* Crosss Login Block Start */
            if (empty($_GET) || empty($_GET['u']) || empty($_GET['t'])) {
                $password = sha1($password . SYSTEM_CONFIG['passwordSalt']);
            }

            $ipaddress = '';
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_FORWARDED'])) {
                $ipaddress = $_SERVER['HTTP_FORWARDED'];
            } else if (isset($_SERVER['REMOTE_ADDR'])) {
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            } else {
                $ipaddress = 'UNKNOWN';
            }
            $queryParams = array($username, 'active');
            $userRow = $db->rawQueryOne("SELECT * FROM user_details as ud 
                                        INNER JOIN roles as r ON ud.role_id=r.role_id 
                                        WHERE ud.login_id = ? AND ud.status = ?", $queryParams);
            $loginAttemptCount = $db->rawQueryOne(
                "SELECT 
                                                SUM(CASE WHEN login_id = ? THEN 1 ELSE 0 END) AS LoginIdCount,
                                                SUM(CASE WHEN ip_address = ? THEN 1 ELSE 0 END) AS IpCount
                                                FROM user_login_history
                                                WHERE login_status='failed' AND login_attempted_datetime > DATE_SUB(NOW(), INTERVAL 15 minute)",
                array($username, $username)
            );

            if ($loginAttemptCount['LoginIdCount'] < 3 || $loginAttemptCount['IpCount'] < 3) {
                if ($userRow['hash_algorithm'] == 'sha1') {
                    $password = sha1($password . SYSTEM_CONFIG['passwordSalt']);
                    if ($password == $userRow['password']) {
                        $newPassword = $user->passwordHash($db->escape($_POST['password']), $userRow['user_id']);
                        $db = $db->where('user_id', $userRow['user_id']);
                        $db->update('user_details', array('password' => $newPassword, 'hash_algorithm' => 'phb'));
                    } else {
                        header("location:/login.php");
                    }
                } else if ($userRow['hash_algorithm'] == 'phb') {
                    if (!password_verify($_POST['password'], $userRow['password'])) {
                        $user->userHistoryLog($username, $loginStatus = 'failed');
                        $_SESSION['alertMsg'] = _("Invalid password");
                        header("location:/login.php");
                    }
                }

                if (isset($userRow) && !empty($userRow)) {
                    $user->userHistoryLog($username, $loginStatus = 'successful');
                    //add random key
                    $instanceResult = $db->rawQueryOne("SELECT vlsm_instance_id, instance_facility_name FROM s_vlsm_instance");

                    if ($instanceResult) {
                        $_SESSION['instanceId'] = $instanceResult['vlsm_instance_id'];
                        $_SESSION['instanceFacilityName'] = $instanceResult['instance_facility_name'];
                    } else {
                        $id = $general->generateRandomString(32);
                        // deleting just in case there is a row already inserted
                        $db->delete('s_vlsm_instance');
                        $db->insert('s_vlsm_instance', array('vlsm_instance_id' => $id));
                        $_SESSION['instanceId'] = $id;
                        $_SESSION['instanceFacilityName'] = null;

                        //Update instance ID in facility and form_vl tbl
                        $data = array('vlsm_instance_id' => $id);
                        $db->update('facility_details', $data);
                    }


                    $_SESSION['userId'] = $userRow['user_id'];
                    $_SESSION['userName'] = ucwords($userRow['user_name']);
                    $_SESSION['roleCode'] = $userRow['role_code'];
                    $_SESSION['roleId'] = $userRow['role_id'];
                    $_SESSION['accessType'] = $userRow['access_type'];
                    $_SESSION['email'] = $userRow['email'];
                    $_SESSION['forcePasswordReset'] = $userRow['force_password_reset'];
                    $_SESSION['facilityMap'] = $facilityDb->getFacilityMap($userRow['user_id']);

                    //Add event log
                    $eventType = 'login';
                    $action = ucwords($userRow['user_name']) . ' logged in';
                    $resource = 'user-login';
                    $general->activityLog($eventType, $action, $resource);

                    $redirect = '/error/401.php';
                    //set role and privileges
                    $priQuery = "SELECT p.privilege_name, rp.privilege_id, r.module FROM roles_privileges_map as rp INNER JOIN privileges as p ON p.privilege_id=rp.privilege_id INNER JOIN resources as r ON r.resource_id=p.resource_id  where rp.role_id='" . $userRow['role_id'] . "'";
                    $priInfo = $db->query($priQuery);
                    $priId = array();
                    if ($priInfo) {
                        foreach ($priInfo as $id) {
                            $priId[] = $id['privilege_name'];
                            $module[$id['module']] = $id['module'];
                        }

                        if ($userRow['landing_page'] != '') {
                            $redirect = $userRow['landing_page'];
                        } else {
                            $fileNameList = array('index.php', 'addVlRequest.php', 'vlRequest.php', 'batchcode.php', 'vlRequestMail.php', 'addImportResult.php', 'vlPrintResult.php', 'vlTestResult.php', 'vl-sample-status.php', 'vl-export-data.php', 'highViralLoad.php', 'roles.php', 'users.php', 'facilities.php', 'globalConfig.php', 'importConfig.php');
                            $fileName = array('dashboard/index.php', '/vl/requests/addVlRequest.php', '/vl/requests/vlRequest.php', '/vl/batch/batchcode.php', 'mail/vlRequestMail.php', 'import-result/addImportResult.php', '/vl/results/vlPrintResult.php', '/vl/results/vlTestResult.php', 'program-management/vl-sample-status.php', 'program-management/vl-export-data.php', 'program-management/highViralLoad.php', 'roles/roles.php', 'users/$user.php', 'facilities/facilities.php', 'global-config/globalConfig.php', 'import-configs/importConfig.php');
                            foreach ($fileNameList as $redirectFile) {
                                if (in_array($redirectFile, $priId)) {
                                    $arrIndex = array_search($redirectFile, $fileNameList);
                                    $redirect = $fileName[$arrIndex];
                                    break;
                                }
                            }
                        }
                    }
                    //check clinic or lab user
                    $_SESSION['userType']   = '';
                    $_SESSION['privileges'] = $priId;
                    $_SESSION['module']     = $module;

                    if (!empty($_SESSION['forcePasswordReset']) && $_SESSION['forcePasswordReset'] == 1) {
                        $redirect = "/users/editProfile.php";
                        $_SESSION['alertMsg'] = _("Please change your password to proceed.");
                    }
                    header("location:" . $redirect);
                } else {
                    $user->userHistoryLog($username, $loginStatus = 'failed');
                    $_SESSION['alertMsg'] = _("Please check your login credentials");
                    header("location:/login.php");
                }
            } else if ($loginAttemptCount['LoginIdCount'] >= 3 || $loginAttemptCount['IpCount'] >= 3) {
                if ($userRow['hash_algorithm'] == 'sha1') {
                    $password = sha1($password . SYSTEM_CONFIG['passwordSalt']);
                    if ($password == $userRow['password']) {
                        $newPassword = $user->passwordHash($db->escape($_POST['password']), $userRow['user_id']);
                        $db = $db->where('user_id', $userRow['user_id']);
                        $db->update('user_details', array('password' => $newPassword, 'hash_algorithm' => 'phb'));
                    } else {
                        header("location:/login.php");
                    }
                } else if ($userRow['hash_algorithm'] == 'phb') {
                    if (!password_verify($_POST['password'], $userRow['password'])) {
                        $user->userHistoryLog($username, $loginStatus = 'failed');
                        $_SESSION['alertMsg'] = _("Invalid password");
                        header("location:/login.php");
                    }
                }
                if ($_POST['captcha'] != '') {
                    if (isset($userRow) && !empty($userRow)) {
                        $user->userHistoryLog($username, $loginStatus = 'successful');

                        //add random key
                        $instanceResult = $db->rawQueryOne("SELECT vlsm_instance_id, instance_facility_name FROM s_vlsm_instance");

                        if ($instanceResult) {
                            $_SESSION['instanceId'] = $instanceResult['vlsm_instance_id'];
                            $_SESSION['instanceFacilityName'] = $instanceResult['instance_facility_name'];
                        } else {
                            $id = $general->generateRandomString(32);
                            // deleting just in case there is a row already inserted
                            $db->delete('s_vlsm_instance');
                            $db->insert('s_vlsm_instance', array('vlsm_instance_id' => $id));
                            $_SESSION['instanceId'] = $id;
                            $_SESSION['instanceFacilityName'] = null;

                            //Update instance ID in facility and form_vl tbl
                            $data = array('vlsm_instance_id' => $id);
                            $db->update('facility_details', $data);
                        }
                        //Add event log
                        $eventType = 'login';
                        $action = ucwords($userRow['user_name']) . ' logged in';
                        $resource = 'user-login';

                        $general->activityLog($eventType, $action, $resource);

                        $_SESSION['userId'] = $userRow['user_id'];
                        $_SESSION['userName'] = ucwords($userRow['user_name']);
                        $_SESSION['roleCode'] = $userRow['role_code'];
                        $_SESSION['roleId'] = $userRow['role_id'];
                        $_SESSION['accessType'] = $userRow['access_type'];
                        $_SESSION['email'] = $userRow['email'];
                        $_SESSION['forcePasswordReset'] = $userRow['force_password_reset'];

                        $redirect = '/error/401.php';
                        //set role and privileges
                        $priQuery = "SELECT p.privilege_name, rp.privilege_id, r.module FROM roles_privileges_map as rp INNER JOIN privileges as p ON p.privilege_id=rp.privilege_id INNER JOIN resources as r ON r.resource_id=p.resource_id  where rp.role_id='" . $userRow['role_id'] . "'";
                        $priInfo = $db->query($priQuery);
                        $priId = array();
                        if ($priInfo) {
                            foreach ($priInfo as $id) {
                                $priId[] = $id['privilege_name'];
                                $module[$id['module']] = $id['module'];
                            }

                            if ($userRow['landing_page'] != '') {
                                $redirect = $userRow['landing_page'];
                            } else {
                                $fileNameList = array('index.php', 'addVlRequest.php', 'vlRequest.php', 'batchcode.php', 'vlRequestMail.php', 'addImportResult.php', 'vlPrintResult.php', 'vlTestResult.php', 'vl-sample-status.php', 'vl-export-data.php', 'highViralLoad.php', 'roles.php', 'users.php', 'facilities.php', 'globalConfig.php', 'importConfig.php');
                                $fileName = array('dashboard/index.php', '/vl/requests/addVlRequest.php', '/vl/requests/vlRequest.php', '/vl/batch/batchcode.php', 'mail/vlRequestMail.php', 'import-result/addImportResult.php', '/vl/results/vlPrintResult.php', '/vl/results/vlTestResult.php', 'program-management/vl-sample-status.php', 'program-management/vl-export-data.php', 'program-management/highViralLoad.php', 'roles/roles.php', '$user/users.php', 'facilities/facilities.php', 'global-config/globalConfig.php', 'import-configs/importConfig.php');
                                foreach ($fileNameList as $redirectFile) {
                                    if (in_array($redirectFile, $priId)) {
                                        $arrIndex = array_search($redirectFile, $fileNameList);
                                        $redirect = $fileName[$arrIndex];
                                        break;
                                    }
                                }
                            }
                        }
                        //check clinic or lab user
                        $_SESSION['userType']   = '';
                        $_SESSION['privileges'] = $priId;
                        $_SESSION['module']     = $module;

                        if (!empty($_SESSION['forcePasswordReset']) && $_SESSION['forcePasswordReset'] == 1) {
                            $redirect = "/users/editProfile.php";
                            $_SESSION['alertMsg'] = _("Please change your password to proceed.");
                        }

                        header("location:" . $redirect);
                    } else {
                        $user->userHistoryLog($username, $loginStatus = 'failed');
                        $_SESSION['alertMsg'] = _("Please check your login credentials");
                        header("location:/login.php");
                    }
                } 
                else {
                    $user->userHistoryLog($username, $loginStatus = 'failed');
                    $_SESSION['alertMsg'] = _("You have exhausted maximum number of login attempts. Please try to login after sometime.");
                    header("location:/login.php");
                }
            }
        } else {
            header("location:/login.php");
        }
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
