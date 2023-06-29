<?php

use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;
use App\Services\FacilitiesService;
use App\Services\CommonService;
use App\Services\UsersService;
use App\Services\GenericTestsService;
use App\Translation\Translation;




$applicationConfig = ContainerRegistry::get('applicationConfig');

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

/** @var GenericTestsService $genericTestsService */
$genericTestsService = ContainerRegistry::get(GenericTestsService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

$_SESSION['module'] = $_SESSION['module'] ?? [];

$syncLatestTime = $general->getLastSyncDateTime();

$arr = $general->getGlobalConfig();
$sarr = $general->getSystemConfig();

$skin = "skin-blue";

//set user language ID (lid) based on user local 1 for english and 2 for French 
$_SESSION['lid'] = 1;
if($_SESSION['userLocale'] == 'fr_FR'){
	$_SESSION['lid'] = 2;

}else {
	$_SESSION['lid'] = 1;
}
$lid = $_SESSION['lid'];

$logoName = "<img src='/assets/img/flask.png' style='margin-top:-5px;max-width:22px;'> <span style=''>LIS</span>";
$smallLogoName = "<img src='/assets/img/flask.png'>";
$systemType = _("Lab Sample Management Module");
$shortName = _("Sample Management");
if (!empty($_SESSION['instanceType']) && $_SESSION['instanceType'] == 'remoteuser') {
	$skin = "skin-red";
	$systemType = _("Remote Sample Tracking Module");
	$logoName = "<span class='fa fa-medkit'></span> STS";
	$smallLogoName = "<span class='fa fa-medkit'></span>";
	$shortName = _("Sample Tracking");
}

if (!empty($applicationConfig['instanceName'])) {
	$systemType = $applicationConfig['instanceName'];
}

// Check if the user can access the requested page
$currentPage = !empty($_SESSION['requestedURI']) ? $_SESSION['requestedURI'] : $_SERVER['PHP_SELF'];
if (!$usersService->isAllowed(basename($currentPage))) {
	http_response_code(401);
	throw new SystemException(_('Unauthorized access. You do not have permission to access this page.'), 401);
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('roles.php', 'users.php', 'facilities.php', 'globalConfig.php', 'importConfig.php', 'otherConfig.php'))) {
	$allAdminMenuAccess = true;
} else {
	$allAdminMenuAccess = false;
}
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('vlRequest.php', 'addVlRequest.php', 'addSamplesFromManifest.php', 'batchcode.php', 'specimenReferralManifestList.php'))) {
	$vlRequestMenuAccess = true;
} else {
	$vlRequestMenuAccess = false;
}
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('addImportResult.php', 'vlTestResult.php', 'vlResultApproval.php', 'vlResultMail.php'))) {
	$vlTestResultMenuAccess = true;
} else {
	$vlTestResultMenuAccess = false;
}
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('vl-sample-status.php', 'vl-export-data.php', 'highViralLoad.php', 'vlControlReport.php', 'vlWeeklyReport.php', 'sampleRejectionReport.php', 'vlMonitoringReport.php', 'vlPrintResult.php'))) {
	$vlManagementMenuAccess = true;
} else {
	$vlManagementMenuAccess = false;
}

// EID MENUS
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('eid-requests.php', 'eid-add-request.php', 'addSamplesFromManifest.php', 'eid-batches.php', 'specimenReferralManifestList.php'))) {
	$eidTestRequestMenuAccess = true;
} else {
	$eidTestRequestMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('addImportResult.php', 'eid-manual-results.php', 'eid-result-status.php'))) {
	$eidTestResultMenuAccess = true;
} else {
	$eidTestResultMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('eid-sample-status.php', 'eid-export-data.php', 'eid-print-results.php', 'eid-sample-rejection-report.php', 'eid-clinic-report.php'))) {
	$eidManagementMenuAccess = true;
} else {
	$eidManagementMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('index.php'))) {
	$dashBoardMenuAccess = true;
} else {
	$dashBoardMenuAccess = false;
}

// COVID-19 Menu start
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("covid-19-requests.php", "covid-19-add-request.php", "covid-19-edit-request.php", "addSamplesFromManifest.php", "covid-19-batches.php", "specimenReferralManifestList.php"))) {
	$covid19TestRequestMenuAccess = true;
} else {
	$covid19TestRequestMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('addImportResult.php', 'covid-19-manual-results.php', 'covid-19-confirmation-manifest.php', 'can-record-confirmatory-tests.php', 'covid-19-result-status.php'))) {
	$covid19TestResultMenuAccess = true;
} else {
	$covid19TestResultMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('covid-19-export-data.php', 'covid-19-sample-rejection-report.php', 'covid-19-sample-status.php', 'covid-19-print-results.php'))) {
	$covid19ManagementMenuAccess = true;
} else {
	$covid19ManagementMenuAccess = false;
}
// COVID-19 Menu end
// HEPATITIS Menu start
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("hepatitis-requests.php", "hepatitis-add-request.php", "hepatitis-edit-request.php", "add-Samples-from-manifest.php"))) {
	$hepatitisTestRequestMenuAccess = true;
} else {
	$hepatitisTestRequestMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("addImportResult.php", "hepatitis-manual-results.php", "hepatitis-result-status.php"))) {
	$hepatitisTestResultMenuAccess = true;
} else {
	$hepatitisTestResultMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("hepatitis-sample-status.php", "hepatitis-export-data.php", "hepatitis-print-results.php", "hepatitis-sample-rejection-report.php", "hepatitis-clinic-report.php", "hepatitisMonthlyThresholdReport"))) {
	$hepatitisManagementMenuAccess = true;
} else {
	$hepatitisManagementMenuAccess = false;
}
// HEPATITIS Menu end

// TB Menu start
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("tb-requests.php", "tb-add-request.php", "tb-edit-request.php", "add-Samples-from-manifest.php"))) {
	$tbTestRequestMenuAccess = true;
} else {
	$tbTestRequestMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("tb-manual-results.php", "tb-result-status.php"))) {
	$tbTestResultMenuAccess = true;
} else {
	$tbTestResultMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("tb-sample-status.php", "tb-export-data.php", "tb-print-results.php", "tb-sample-rejection-report.php", "tb-clinic-report.php"))) {
	$tbManagementMenuAccess = true;
} else {
	$tbManagementMenuAccess = false;
}
// TB Menu end


if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("view-requests.php", "add-request.php", "add-samples-from-manifest.php", "batch-code.php", "specimenReferralManifestList.php"))) {
	$genericTestRequestMenuAccess = true;
} else {
	$genericTestRequestMenuAccess = false;
}
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("generic-test-results.php", "update-generic-test-result.php", "generic-failed-results.php", "generic-result-approval.php"))) {
	$genericTestResultMenuAccess = true;
} else {
	$genericTestResultMenuAccess = false;
}
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array("generic-sample-status.php", "generic-export-data.php", "generic-print-result.php", "generic-weekly-report.php", "sample-rejection-report.php", "generic-monitoring-report.php", "generic-monthly-threshold-report.php"))) {
	$genericManagementMenuAccess = true;
} else {
	$genericManagementMenuAccess = false;
}
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['APP_LOCALE'] ?? 'en_US'; ?>">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo $shortName . " | " . ((isset($title) && $title != null && $title != "") ? $title : "VLSM"); ?></title>
	<!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<meta name="viewport" content="width=1024">

	<?php if (!empty($_SESSION['instanceType']) && $_SESSION['instanceType'] == 'remoteuser') { ?>
		<link rel="apple-touch-icon" sizes="180x180" href="/assets/vlsts-icons/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/assets/vlsts-icons/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/assets/vlsts-icons/favicon-16x16.png">
		<link rel="manifest" href="/assets/vlsts-icons/site.webmanifest">
	<?php } else { ?>
		<link rel="apple-touch-icon" sizes="180x180" href="/assets/vlsm-icons/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/assets/vlsm-icons/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/assets/vlsm-icons/favicon-16x16.png">
		<link rel="manifest" href="/assets/vlsm-icons/site.webmanifest">
	<?php } ?>


	<link rel="stylesheet" media="all" type="text/css" href="/assets/css/fonts.css" />

	<link rel="stylesheet" media="all" type="text/css" href="/assets/css/jquery-ui.min.css" />
	<link rel="stylesheet" media="all" type="text/css" href="/assets/css/jquery-ui-timepicker-addon.css" />

	<!-- Bootstrap 3.3.6 -->
	<link rel="stylesheet" href="/assets/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="/assets/css/font-awesome.min.css">

	<!-- Ionicons -->

	<!-- DataTables -->
	<link rel="stylesheet" href="/assets/plugins/datatables/dataTables.bootstrap.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="/assets/css/AdminLTE.min.css">
	<!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
	<link rel="stylesheet" href="/assets/css/skins/_all-skins.min.css">
	<!-- iCheck -->

	<link href="/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet" />

	<link href="/assets/css/select2.min.css" rel="stylesheet" />
	<link href="/assets/css/style.css" rel="stylesheet" />
	<link href="/assets/css/deforayModal.css" rel="stylesheet" />
	<link href="/assets/css/jquery.fastconfirm.css" rel="stylesheet" />

	<link rel="stylesheet" href="/assets/css/components-rounded.min.css">

	<!-- jQuery 2.2.3 -->

	<script type="text/javascript" src="/assets/js/jquery.min.js"></script>

	<!-- Latest compiled and minified JavaScript -->

	<script type="text/javascript" src="/assets/js/jquery-ui.min.js"></script>
	<script src="/assets/js/deforayModal.js"></script>
	<script src="/assets/js/jquery.fastconfirm.js"></script>
	<link href="/assets/css/select2.live.min.css" rel="stylesheet" />
	<!--<script type="text/javascript" src="/assets/js/jquery-ui-sliderAccess.js"></script>-->
	<style>
		.select2-selection--multiple {
			max-height: 100px;
			width: auto;
			overflow-y: scroll !important;
		}
		</style>

</head>

<body class="hold-transition <?php echo $skin; ?> sidebar-mini" id="capture">
	<div class="wrapper">
		<header class="main-header">
			<!-- Logo -->
			<a href="<?php echo ($dashBoardMenuAccess === true) ? '/dashboard/index.php' : '#'; ?>" class="logo">
				<!-- mini logo for sidebar mini 50x50 pixels -->
				<span class="logo-mini"><strong><?php echo $smallLogoName; ?></strong></span>
				<!-- logo for regular state and mobile devices -->
				<span class="logo-lg" style="font-weight:bold;"><?php echo $logoName; ?></span>
			</a>
			<!-- Header Navbar: style can be found in header.less -->
			<nav class="navbar navbar-static-top">
				<!-- Sidebar toggle button-->
				<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
				</a>
				<ul class="nav navbar-nav">
					<li>
						<a href="javascript:void(0);return false;"><span style="text-transform: uppercase;font-weight:600;"><?php echo $systemType; ?></span></a>
					</li>
				</ul>
				<div class="navbar-custom-menu">
					<ul class="nav navbar-nav">
						<?php if (!empty(SYSTEM_CONFIG['recency']['crosslogin']) && SYSTEM_CONFIG['recency']['crosslogin'] === true && !empty(SYSTEM_CONFIG['recency']['url'])) {
						?>
							<li class="user-menu">
								<a onclick="setCrossLogin();" href="<?php echo rtrim(SYSTEM_CONFIG['recency']['url'], "/") . '/login?u=' . base64_encode($_SESSION['loginId']) . '&t=' . ($_SESSION['crossLoginPass']) . '&name=' . base64_encode($_SESSION['userName']); ?>" class="btn btn-link"><span class="fa-solid fa-arrow-up-right-from-square"></span> Recency</a>
							</li>
						<?php } ?>

						<li class="dropdown user user-menu">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">

								<span class="fa-solid fa-hospital-user"></span>
								<span class="hidden-xs"><?php if (isset($_SESSION['userName'])) {
															echo $_SESSION['userName'];
														} ?></span>
								<?php if (!empty(SYSTEM_CONFIG['remoteURL']) && isset($_SESSION['userName']) && isset($_SESSION['instanceType']) && ($_SESSION['instanceType'] == 'vluser')) { ?>
									<span class="fa-solid fa-circle is-remote-server-reachable" style="font-size:1em;display:none;"></span>
								<?php } ?>
							</a>
							<ul class="dropdown-menu">
								<!-- Menu Footer-->
								<?php $alignRight = '';
								$showProfileBtn = "style=display:none;";
								if ($arr['edit_profile'] != 'no') {
									$alignRight = "pull-right-xxxxx";
									$showProfileBtn = "style=display:block;";
								} ?>
								<li class="user-footer" <?php echo $showProfileBtn; ?>>
									<a href="/users/editProfile.php" class=""><?php echo Translation::translate("Edit Profile"); ?></a>
								</li>
								<li class="user-footer <?php echo $alignRight; ?>">
									<a href="/login/logout.php"><?php echo Translation::translate("Sign out"); ?></a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</nav>
		</header>
		<!-- Left side column. contains the logo and sidebar -->
		<aside class="main-sidebar">
			<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">
				<!-- sidebar menu: : style can be found in sidebar.less -->
				<!-- Sidebar user panel -->
				<?php if (isset($arr['logo']) && trim($arr['logo']) != "" && file_exists('uploads' . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])) { ?>
					<div class="user-panel">
						<div align="center">
							<img src="/uploads/logo/<?php echo $arr['logo']; ?>" alt="Logo Image" style="max-width:120px;">
						</div>
					</div>
				<?php } ?>
				<ul class="sidebar-menu">
					<?php if ($dashBoardMenuAccess === true) { ?>
						<li class="allMenu dashboardMenu active">
							<a href="/dashboard/index.php">
								<span class="fa-solid fa-chart-pie"></span> <span><?php echo Translation::translate("Dashboard"); ?></span>
							</a>
						</li>
					<?php }
					if ($allAdminMenuAccess === true && array_intersect($_SESSION['module'], array('admin'))) { ?>
						<li class="treeview manage">
							<a href="#">
								<span class="fa-solid fa-shield"></span>
								<span><?php echo Translation::translate("Admin"); ?></span>
								<span class="pull-right-container">
									<span class="fa-solid fa-angle-left pull-right"></span>
								</span>
							</a>
							<ul class="treeview-menu">
								<?php /* if (isset($_SESSION['privileges']) && in_array("facilityMap.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] == 'remoteuser') { ?>
                                    <li class="allMenu facilityMapMenu">
                                        <a href="/facilities/facilityMap.php"><span class="fa-solid fa-caret-right"></span>Facility Map</a>
                                    </li>
                                <?php }
                                if (isset($_SESSION['privileges']) && in_array("testRequestEmailConfig.php", $_SESSION['privileges'])) { ?>
                                    <li class="allMenu requestEmailConfigMenu">
                                        <a href="/vl/request-mail/testRequestEmailConfig.php"><span class="fa-solid fa-caret-right"></span>Test Request Email/SMS <br>Configuration</a>
                                    </li>
                                <?php } */
								if (in_array("roles.php", $_SESSION['privileges']) || in_array("users.php", $_SESSION['privileges'])) { ?>
									<li class="treeview access-control-menu">
										<a href="#">
											<span class="fa-solid fa-user"></span>
											<span><?php echo Translation::translate("Access Control"); ?></span>
											<span class="pull-right-container">
												<span class="fa-solid fa-angle-left pull-right"></span>
											</span>
										</a>
										<ul class="treeview-menu">
											<?php if (isset($_SESSION['privileges']) && in_array("roles.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu roleMenu">
													<a href="/roles/roles.php" data-inner-pages="<?= base64_encode('/roles/addRole.php') . ';' . base64_encode('/roles/editRole.php'); ?>">
														<span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Roles"); ?>
													</a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("users.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu userMenu">
													<a href="/users/users.php" data-inner-pages="<?= base64_encode('/users/addUser.php') . ';' . base64_encode('/users/editUser.php'); ?>">
														<span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Users"); ?>
													</a>
												</li>
											<?php }
											?>
										</ul>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("facilities.php", $_SESSION['privileges'])) { ?>
									<li class="treeview facility-config-menu">
										<a href="/facilities/facilities.php" data-inner-pages="<?= base64_encode('/facilities/addFacility.php') . ';' . base64_encode('/facilities/editFacility.php') . ';' . base64_encode('/facilities/mapTestType.php'); ?>"><span class="fa-solid fa-hospital"></span> <?php echo Translation::translate("Facilities"); ?></a>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && ((in_array("audit-trail.php", $_SESSION['privileges']) || in_array("api-sync-history.php", $_SESSION['privileges'])) || (in_array("sources-of-requests.php", $_SESSION['privileges'])))) { ?>
									<li class="treeview monitoring-menu">
										<a href="#">
											<span class="fa-solid fa-bullseye"></span>
											<span><?php echo Translation::translate("Monitoring"); ?></span>
											<span class="pull-right-container">
												<span class="fa-solid fa-angle-left pull-right"></span>
											</span>
										</a>
										<ul class="treeview-menu">
											<?php if (isset($_SESSION['privileges']) && in_array("activity-log.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu treeview activity-log-menu">
													<a href="/admin/monitoring/activity-log.php"><span class="fa-solid fa-file-lines"></span> <?php echo Translation::translate("User Activity Log"); ?></a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("audit-trail.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu treeview audit-trail-menu">
													<a href="/admin/monitoring/audit-trail.php"><span class="fa-solid fa-clock-rotate-left"></span> <?php echo Translation::translate("Audit Trail"); ?></a>
												</li>
											<?php }

											if (isset($_SESSION['privileges']) && in_array("api-sync-history.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu treeview api-sync-history-menu">
													<a href="/admin/monitoring/api-sync-history.php"><span class="fa-solid fa-circle-nodes"></span> <?php echo Translation::translate("API History"); ?></a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("sources-of-requests.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu treeview sources-of-requests-report-menu">
													<a href="/admin/monitoring/sources-of-requests.php"><span class="fa-solid fa-circle-notch"></span> <?php echo Translation::translate("Source of Requests"); ?></a>
												</li>
											<?php }
											if ($_SESSION['instanceType'] == 'remoteuser' && isset($_SESSION['privileges']) && in_array("sync-status.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu treeview sync-status-menu">
													<a href="/admin/monitoring/sync-status.php"><span class="fa-solid fa-traffic-light"></span> <?php echo Translation::translate("Lab Sync Status"); ?></a>
												</li>
											<?php } ?>
										</ul>
									</li>
								<?php }
								if (in_array("roles.php", $_SESSION['privileges']) || in_array("users.php", $_SESSION['privileges'])) { ?>
									<li class="treeview system-config-menu">
										<a href="#">
											<span class="fa-solid fa-gears"></span>
											<span><?php echo Translation::translate("System Configuration"); ?></span>
											<span class="pull-right-container">
												<span class="fa-solid fa-angle-left pull-right"></span>
											</span>
										</a>
										<ul class="treeview-menu">
											<?php if (isset($_SESSION['privileges']) && in_array("globalConfig.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu globalConfigMenu">
													<a href="/global-config/editGlobalConfig.php" data-inner-pages="<?= base64_encode('/global-config/editGlobalConfig.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("General Configuration"); ?></a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("importConfig.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu importConfigMenu">
													<a href="/import-configs/importConfig.php" data-inner-pages="<?= base64_encode('/import-configs/addImportConfig.php') . ';' . base64_encode('/import-configs/editImportConfig.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Instruments"); ?></a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("geographical-divisions-details.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu geographicalMenu">
													<a href="/common/reference/geographical-divisions-details.php" data-inner-pages="<?= base64_encode('/common/reference/add-geographical-divisions.php') . ';' . base64_encode('/common/reference/edit-geographical-divisions.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Geographical Divisions"); ?></a>
												</li>
											<?php }
											?>
											<li class="allMenu common-reference-implementation-partners">
												<a href="/common/reference/implementation-partners.php" data-inner-pages="<?= base64_encode('/common/reference/add-implementation-partners.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Implementation Partners"); ?></a>
											</li>
											<li class="allMenu common-reference-funding-sources">
												<a href="/common/reference/funding-sources.php" data-inner-pages="<?= base64_encode('/common/reference/add-funding-sources.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Funding Sources"); ?></a>
											</li>
										</ul>
									</li>
								<?php }
								if (isset(SYSTEM_CONFIG['modules']['genericTests']) && SYSTEM_CONFIG['modules']['genericTests'] === true && isset($_SESSION['privileges']) && in_array("test-type.php", $_SESSION['privileges'])) { ?>
									<li class="treeview generic-reference-manage">
										<a href="#"><em class="fa-solid fa-vial-circle-check"></em><?php echo Translation::translate("Lab Tests Config"); ?>
											<span class="pull-right-container">
												<span class="fa-solid fa-angle-left pull-right"></span>
											</span>
										</a>

										<ul class="treeview-menu">
											<?php
											if (isset($_SESSION['privileges']) && in_array("generic-sample-type.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu genericSampleTypeMenu">
													<a href="/generic-tests/reference/sample-types/generic-sample-type.php" data-inner-pages="<?= base64_encode('/generic-tests/reference/sample-types/generic-add-sample-type.php') . ';' . base64_encode('/generic-tests/reference/sample-types/generic-edit-sample-type.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Sample Types"); ?></a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("generic-testing-reason.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu genericTestingReasonMenu">
													<a href="/generic-tests/reference/testing-reasons/generic-testing-reason.php" data-inner-pages="<?= base64_encode('/generic-tests/reference/testing-reasons/generic-add-testing-reason.php') . ';' . base64_encode('/generic-tests/reference/testing-reasons/generic-edit-testing-reason.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Testing Reasons"); ?></a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("generic-test-failure-reason.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu genericTestFailureReasonMenu">
													<a href="/generic-tests/reference/test-failure-reasons/generic-test-failure-reason.php" data-inner-pages="<?= base64_encode('/generic-tests/reference/test-failure-reasons/generic-add-test-failure-reason.php') . ';' . base64_encode('/generic-tests/reference/test-failure-reasons/generic-edit-test-failure-reason.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Test Failure Reasons"); ?></a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("generic-symptoms.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu genericSymptomsMenu">
													<a href="/generic-tests/reference/symptoms/generic-symptoms.php" data-inner-pages="<?= base64_encode('/generic-tests/reference/symptoms/generic-add-symptoms.php') . ';' . base64_encode('/generic-tests/reference/symptoms/generic-edit-symptoms.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Symptoms"); ?></a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("generic-sample-rejection-reasons.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu genericSampleRejectionReasonsMenu">
													<a href="/generic-tests/reference/sample-rejection-reasons/generic-sample-rejection-reasons.php" data-inner-pages="<?= base64_encode('/generic-tests/reference/sample-rejection-reasons/generic-edit-rejection-reasons.php') . ';' . base64_encode('/generic-tests/reference/sample-rejection-reasons/generic-add-rejection-reasons.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Sample Rejection Reasons"); ?></a>
												</li>
											<?php }
											if (isset($_SESSION['privileges']) && in_array("test-type.php", $_SESSION['privileges'])) { ?>
												<li class="allMenu testTypeConfigurationMenu">
													<a href="/generic-tests/configuration/test-type.php" data-inner-pages="<?= base64_encode('/generic-tests/configuration/add-test-type.php') . ';' . base64_encode('/generic-tests/configuration/edit-test-type.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Test Type Configuration"); ?></a>
												</li>
											<?php }
											?>
										</ul>
									</li>
								<?php }
								if (isset(SYSTEM_CONFIG['modules']['vl']) && SYSTEM_CONFIG['modules']['vl'] === true && isset($_SESSION['privileges']) && in_array("vl-art-code-details.php", $_SESSION['privileges'])) { ?>
									<li class="treeview vl-reference-manage">
										<a href="#"><span class="fa-solid fa-flask-vial"></span><?php echo Translation::translate("VL Config"); ?>
											<span class="pull-right-container">
												<span class="fa-solid fa-angle-left pull-right"></span>
											</span>
										</a>

										<ul class="treeview-menu">
											<li class="allMenu vl-art-code-details">
												<a href="/vl/reference/vl-art-code-details.php" data-inner-pages="<?= base64_encode('/vl/reference/add-vl-art-code-details.php') . ';' . base64_encode('/vl/reference/edit-vl-art-code-details.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("ART Regimen"); ?></a>
											</li>
											<li class="allMenu vl-sample-rejection-reasons">
												<a href="/vl/reference/vl-sample-rejection-reasons.php" data-inner-pages="<?= base64_encode('/vl/reference/add-vl-sample-rejection-reasons.php') . ';' . base64_encode('/vl/reference/edit-vl-sample-rejection-reasons.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Rejection Reasons"); ?></a>
											</li>
											<li class="allMenu vl-sample-type">
												<a href="/vl/reference/vl-sample-type.php" data-inner-pages="<?= base64_encode('/vl/reference/add-vl-sample-type.php') . ';' . base64_encode('/vl/reference/edit-vl-sample-type.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Sample Type"); ?></a>
											</li>
											<li class="allMenu vl-results">
												<a href="/vl/reference/vl-results.php" data-inner-pages="<?= base64_encode('/vl/reference/add-vl-results.php') . ';' . base64_encode('/vl/reference/edit-vl-results.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Results"); ?></a>
											</li>
											<li class="allMenu vl-test-reasons">
												<a href="/vl/reference/vl-test-reasons.php" data-inner-pages="<?= base64_encode('/vl/reference/add-vl-test-reasons.php') . ';' . base64_encode('/vl/reference/edit-vl-test-reasons.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Test Reasons"); ?></a>
											</li>
											<li class="allMenu vl-test-failure-reasons">
												<a href="/vl/reference/vl-test-failure-reasons.php" data-inner-pages="<?= base64_encode('/vl/reference/add-vl-test-failure-reason.php') . ';' . base64_encode('/vl/reference/edit-vl-test-failure-reason.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Test Failure Reasons"); ?></a>
											</li>
										</ul>
									</li>
								<?php }
								if (isset(SYSTEM_CONFIG['modules']['eid']) && SYSTEM_CONFIG['modules']['eid'] === true && isset($_SESSION['privileges']) && in_array("eid-sample-type.php", $_SESSION['privileges'])) { ?>
									<li class="treeview eid-reference-manage">
										<a href="#"><span class="fa-solid fa-child"></span><?php echo Translation::translate("EID Config"); ?>
											<span class="pull-right-container">
												<span class="fa-solid fa-angle-left pull-right"></span>
											</span>
										</a>

										<ul class="treeview-menu">
											<li class="allMenu eid-sample-rejection-reasons">
												<a href="/eid/reference/eid-sample-rejection-reasons.php" data-inner-pages="<?= base64_encode('/eid/reference/add-eid-sample-rejection-reasons.php') . ';' . base64_encode('/eid/reference/edit-eid-sample-rejection-reasons.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Rejection Reasons"); ?></a>
											</li>
											<li class="allMenu eid-sample-type">
												<a href="/eid/reference/eid-sample-type.php" data-inner-pages="<?= base64_encode('/eid/reference/add-eid-sample-type.php') . ';' . base64_encode('/eid/reference/edit-eid-sample-type.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Sample Type"); ?></a>
											</li>
											<li class="allMenu eid-test-reasons">
												<a href="/eid/reference/eid-test-reasons.php" data-inner-pages="<?= base64_encode('/eid/reference/add-eid-test-reasons.php') . ';' . base64_encode('/eid/reference/edit-eid-test-reasons.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Test Reasons"); ?></a>
											</li>
											<li class="allMenu eid-results">
												<a href="/eid/reference/eid-results.php" data-inner-pages="<?= base64_encode('/eid/reference/add-eid-results.php') . ';' . base64_encode('/eid/reference/edit-eid-results.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Results"); ?></a>
											</li>
										</ul>
									</li>
								<?php }
								if (isset(SYSTEM_CONFIG['modules']['covid19']) && SYSTEM_CONFIG['modules']['covid19'] === true && isset($_SESSION['privileges']) && in_array("covid19-sample-type.php", $_SESSION['privileges'])) { ?>
									<li class="treeview covid19-reference-manage">
										<a href="#"><span class="fa-solid fa-virus-covid"></span>
											<?php echo Translation::translate("Covid-19 Config"); ?>
											<span class="pull-right-container">
												<span class="fa-solid fa-angle-left pull-right"></span>
											</span>
										</a>

										<ul class="treeview-menu">
											<li class="allMenu covid19-comorbidities">
												<a href="/covid-19/reference/covid19-comorbidities.php" data-inner-pages="<?= base64_encode('/covid-19/reference/add-covid19-comorbidities.php') . ';' . base64_encode('/covid-19/reference/edit-covid19-comorbidities.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Co-morbidities"); ?></a>
											</li>
											<li class="allMenu covid19-sample-rejection-reasons">
												<a href="/covid-19/reference/covid19-sample-rejection-reasons.php" data-inner-pages="<?= base64_encode('/covid-19/reference/add-covid19-sample-rejection-reason.php') . ';' . base64_encode('/covid-19/reference/edit-covid19-sample-rejection-reason.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Rejection Reasons"); ?></a>
											</li>
											<li class="allMenu covid19-sample-type">
												<a href="/covid-19/reference/covid19-sample-type.php" data-inner-pages="<?= base64_encode('/covid-19/reference/add-covid19-sample-type.php') . ';' . base64_encode('/covid-19/reference/edit-covid19-sample-type.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Sample Type"); ?></a>
											</li>
											<li class="allMenu covid19-symptoms">
												<a href="/covid-19/reference/covid19-symptoms.php" data-inner-pages="<?= base64_encode('/covid-19/reference/add-covid19-symptoms.php') . ';' . base64_encode('/covid-19/reference/edit-covid19-symptoms.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Symptom"); ?></a>
											</li>
											<li class="allMenu covid19-test-reasons">
												<a href="/covid-19/reference/covid19-test-reasons.php" data-inner-pages="<?= base64_encode('/covid-19/reference/add-covid19-test-reasons.php') . ';' . base64_encode('/covid-19/reference/edit-covid19-test-reasons.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Test-Reasons"); ?></a>
											</li>
											<li class="allMenu covid19-results">
												<a href="/covid-19/reference/covid19-results.php" data-inner-pages="<?= base64_encode('/covid-19/reference/add-covid19-results.php') . ';' . base64_encode('/covid-19/reference/edit-covid19-results.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Results"); ?></a>
											</li>
											<li class="allMenu covid19-qc-test-kits">
												<a href="/covid-19/reference/covid19-qc-test-kits.php" data-inner-pages="<?= base64_encode('/covid-19/reference/add-covid19-qc-test-kit.php') . ';' . base64_encode('/covid-19/reference/edit-covid19-qc-test-kit.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("QC Test Kits"); ?></a>
											</li>
										</ul>
									</li>
								<?php }
								if (isset(SYSTEM_CONFIG['modules']['hepatitis']) && SYSTEM_CONFIG['modules']['hepatitis'] === true && isset($_SESSION['privileges']) && in_array("hepatitis-sample-type.php", $_SESSION['privileges'])) { ?>
									<li class="treeview hepatitis-reference-manage">
										<a href="#"><span class="fa-solid fa-square-h"></span>
											<?php echo Translation::translate("Hepatitis Config"); ?>
											<span class="pull-right-container">
												<span class="fa-solid fa-angle-left pull-right"></span>
											</span>
										</a>

										<ul class="treeview-menu">
											<li class="allMenu hepatitis-comorbidities">
												<a href="/hepatitis/reference/hepatitis-comorbidities.php" data-inner-pages="<?= base64_encode('/hepatitis/reference/add-hepatitis-comorbidities.php') . ';' . base64_encode('/hepatitis/reference/edit-hepatitis-comorbidities.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Co-morbidities"); ?></a>
											</li>
											<li class="allMenu hepatitis-risk-factors">
												<a href="/hepatitis/reference/hepatitis-risk-factors.php" data-inner-pages="<?= base64_encode('/hepatitis/reference/add-hepatitis-risk-factors.php') . ';' . base64_encode('/hepatitis/reference/edit-hepatitis-risk-factors.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Risk Factors"); ?></a>
											</li>
											<li class="allMenu hepatitis-sample-rejection-reasons">
												<a href="/hepatitis/reference/hepatitis-sample-rejection-reasons.php" data-inner-pages="<?= base64_encode('/hepatitis/reference/add-hepatitis-sample-rejection-reasons.php') . ';' . base64_encode('/hepatitis/reference/edit-hepatitis-sample-rejection-reasons.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Rejection Reasons"); ?></a>
											</li>
											<li class="allMenu hepatitis-sample-type">
												<a href="/hepatitis/reference/hepatitis-sample-type.php" data-inner-pages="<?= base64_encode('/hepatitis/reference/add-hepatitis-sample-type.php') . ';' . base64_encode('/hepatitis/reference/edit-hepatitis-sample-type.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Sample Type"); ?></a>
											</li>
											<li class="allMenu hepatitis-results">
												<a href="/hepatitis/reference/hepatitis-results.php" data-inner-pages="<?= base64_encode('/hepatitis/reference/add-hepatitis-results.php') . ';' . base64_encode('/hepatitis/reference/edit-hepatitis-results.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Results"); ?></a>
											</li>
											<li class="allMenu hepatitis-test-reasons">
												<a href="/hepatitis/reference/hepatitis-test-reasons.php" data-inner-pages="<?= base64_encode('/hepatitis/reference/add-hepatitis-test-reasons.php') . ';' . base64_encode('/hepatitis/reference/edit-hepatitis-test-reasons.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Test-Reasons"); ?></a>
											</li>
										</ul>
									</li>
								<?php }
								if (isset(SYSTEM_CONFIG['modules']['tb']) && SYSTEM_CONFIG['modules']['tb'] === true && isset($_SESSION['privileges']) && in_array("tb-sample-type.php", $_SESSION['privileges'])) { ?>
									<li class="treeview tb-reference-manage">
										<a href="#"><span class="fa-solid fa-heart-pulse"></span>
											<?php echo Translation::translate("TB Config"); ?>
											<span class="pull-right-container">
												<span class="fa-solid fa-angle-left pull-right"></span>
											</span>
										</a>

										<ul class="treeview-menu">
											<li class="allMenu tb-sample-rejection-reasons">
												<a href="/tb/reference/tb-sample-rejection-reasons.php" data-inner-pages="<?= base64_encode('/tb/reference/add-tb-sample-rejection-reason.php') . ';' . base64_encode('/tb/reference/edit-tb-sample-rejection-reason.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Rejection Reasons"); ?></a>
											</li>
											<li class="allMenu tb-reference">
												<a href="/tb/reference/tb-sample-type.php" data-inner-pages="<?= base64_encode('/tb/reference/add-tb-sample-type.php') . ';' . base64_encode('/tb/reference/edit-tb-sample-type.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Sample Type"); ?></a>
											</li>
											<li class="allMenu tb-test-reasons">
												<a href="/tb/reference/tb-test-reasons.php" data-inner-pages="<?= base64_encode('/tb/reference/add-tb-test-reasons.php') . ';' . base64_encode('/tb/reference/edit-tb-test-reasons.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Test-Reasons"); ?></a>
											</li>
											<li class="allMenu tb-results">
												<a href="/tb/reference/tb-results.php" data-inner-pages="<?= base64_encode('/tb/reference/add-tb-results.php') . ';' . base64_encode('/tb/reference/edit-tb-results.php'); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Results"); ?></a>
											</li>
										</ul>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && ($_SESSION['instanceType'] == 'remoteuser') && in_array("move-samples.php", $_SESSION['privileges'])) { ?>
									<li class="treeview facility-config-menu">
										<a href="/move-samples/move-samples.php"><span class="fa-solid fa-hospital"></span> <?php echo Translation::translate("Move Samples"); ?></a>
									</li>
								<?php } ?>

							</ul>
						</li>
					<?php }
					if (isset(SYSTEM_CONFIG['modules']['genericTests']) && SYSTEM_CONFIG['modules']['genericTests'] === true) {
					?>
						<li class="header"><?php echo Translation::translate("LAB TESTS"); ?></li>
						<?php if ($genericTestRequestMenuAccess) { ?>
							<li class="treeview allMenu generic-test-request-menu">
								<a href="#">
									<span class="fa-solid fa-pen-to-square"></span>
									<span><?php echo Translation::translate("Request Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php
									if (isset($_SESSION['privileges']) && in_array("view-requests.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu genericRequestMenu">
											<a href="/generic-tests/requests/view-requests.php" data-inner-pages="<?= base64_encode('/generic-tests/requests/edit-request.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("View Test Requests"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("add-request.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu addGenericRequestMenu">
											<a href="/generic-tests/requests/add-request.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add New Request"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("add-samples-from-manifest.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] != 'remoteuser')) { ?>
										<li class="allMenu addGenericSamplesFromManifestMenu">
											<a href="/generic-tests/requests/add-samples-from-manifest.php"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Add Samples from Manifest"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("batch-code.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu batchGenericCodeMenu">
											<a href="/generic-tests/requests/batch/batch-code.php" data-inner-pages="<?= base64_encode('/generic-tests/requests/batch/addBatch.php') . ';' . base64_encode('/generic-tests/requests/batch/editBatch.php') . ';' . base64_encode('/generic-tests/requests/batch/addBatchControlsPosition.php') . ';' . base64_encode('/generic-tests/requests/batch/editBatchControlsPosition.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Batch"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("specimen-referral-manifest-list.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] == 'remoteuser')) { ?>
										<li class="allMenu specimenGenericReferralManifestListMenu">
											<a href="/specimen-referral-manifest/specimenReferralManifestList.php?t=<?php echo base64_encode('generic-tests'); ?>" data-inner-pages="<?= base64_encode('/specimen-referral-manifest/addSpecimenReferralManifest.php?t=' . base64_encode('generic-tests')) . ';' . base64_encode('/specimen-referral-manifest/editSpecimenReferralManifest.php?t=' . base64_encode('generic-tests')) . ';' . base64_encode('/specimen-referral-manifest/move-manifest.php?t=' . base64_encode('generic-tests')); ?>"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Specimen Manifest"); ?></a>
										</li>
									<?php }
									?>
								</ul>
							</li>
						<?php }
						if ($genericTestResultMenuAccess) { ?>
							<li class="treeview allMenu generic-test-results-menu">
								<a href="#">
									<span class="fa-solid fa-list-check"></span>
									<span><?php echo Translation::translate("Test Result Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("addImportResult.php", $_SESSION['privileges'])) { ?>
										<!-- <li class="allMenu importGenericResultMenu"><a href="/import-result/addImportResult.php?t=<?php echo base64_encode('generic-tests'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Import Result From File"); ?></a></li> -->
									<?php }
									if (isset($_SESSION['privileges']) && in_array("generic-test-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu genericTestResultMenu"><a href="/generic-tests/results/generic-test-results.php" data-inner-pages="<?= base64_encode('/generic-tests/results/update-generic-test-result.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Enter Result Manually"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("generic-test-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu genericFailedResultMenu"><a href="/generic-tests/results/generic-failed-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Failed/Hold Samples"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("generic-result-approval.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu genericResultApprovalMenu"><a href="/generic-tests/results/generic-result-approval.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Results Status"); ?></a></li>
									<?php } ?>
								</ul>
							</li>
						<?php }
						if ($genericManagementMenuAccess === true) { ?>
							<li class="treeview allMenu generic-program-menu">
								<a href="#">
									<span class="fa-solid fa-book"></span>
									<span><?php echo Translation::translate("Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("generic-sample-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu genericStatusReportMenu"><a href="/generic-tests/program-management/generic-sample-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Status Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("generic-export-data.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu genericExportMenu"><a href="/generic-tests/program-management/generic-export-data.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Export Results"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("generic-print-result.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu genericPrintResultMenu"><a href="/generic-tests/results/generic-print-result.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Print Result"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("sample-rejection-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu genericSampleRejectionReport"><a href="/generic-tests/program-management/sample-rejection-report.php"><span class="fa-solid fa-caret-right"></span> Sample Rejection <?php echo Translation::translate("Report"); ?></a></li>
									<?php } ?>
								</ul>
							</li>
						<?php }
					}
					if (isset(SYSTEM_CONFIG['modules']['vl']) && SYSTEM_CONFIG['modules']['vl'] === true && array_intersect($_SESSION['module'], array('vl'))) { ?>
						<li class="header"><?php echo Translation::translate("VIRAL LOAD"); ?></li>
						<?php if ($vlRequestMenuAccess === true) { ?>
							<li class="treeview request">
								<a href="#">
									<span class="fa-solid fa-pen-to-square"></span>
									<span><?php echo Translation::translate("Request Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php
									if (isset($_SESSION['privileges']) && in_array("vlRequest.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlRequestMenu">
											<a href="/vl/requests/vlRequest.php" data-inner-pages="<?= base64_encode('/vl/requests/editVlRequest.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("View Test Requests"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("addVlRequest.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu addVlRequestMenu">
											<a href="/vl/requests/addVlRequest.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add New Request"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("addVlRequest.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu addVlRequestFromDAMAMenu">
											<a href="/vl/requests/addVlRequestFromDama.php"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Request From DAMA"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("addSamplesFromManifest.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] != 'remoteuser')) { ?>
										<li class="allMenu addSamplesFromManifestMenu">
											<a href="/vl/requests/addSamplesFromManifest.php"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Add Samples from Manifest"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("batchcode.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu batchCodeMenu">
											<a href="/vl/batch/batchcode.php" data-inner-pages="<?= base64_encode('/vl/batch/addBatch.php') . ';' . base64_encode('/vl/batch/editBatch.php') . ';' . base64_encode('/vl/batch/addBatchControlsPosition.php') . ';' . base64_encode('/vl/batch/editBatchControlsPosition.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Batch"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("specimenReferralManifestList.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] == 'remoteuser')) { ?>
										<li class="allMenu specimenReferralManifestListVLMenu">
											<a href="/specimen-referral-manifest/specimenReferralManifestList.php?t=<?php echo base64_encode('vl'); ?>" data-inner-pages="<?= base64_encode('/specimen-referral-manifest/addSpecimenReferralManifest.php?t=' . base64_encode('vl')) . ';' . base64_encode('/specimen-referral-manifest/editSpecimenReferralManifest.php?t=' . base64_encode('vl')) . ';' . base64_encode('/specimen-referral-manifest/move-manifest.php?t=' . base64_encode('vl')); ?>"><span class="fa-solid fa-caret-right"></span> VL <?php echo Translation::translate("Specimen Manifest"); ?></a>
										</li>
									<?php }
									?>
								</ul>
							</li>
						<?php }
						if ($vlTestResultMenuAccess === true) { ?>
							<li class="treeview test">
								<a href="#">
									<span class="fa-solid fa-list-check"></span>
									<span><?php echo Translation::translate("Test Result Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("addImportResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu importResultMenu"><a href="/import-result/addImportResult.php?t=<?php echo base64_encode('vl'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Import Result From File"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlTestResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlTestResultMenu"><a href="/vl/results/vlTestResult.php" data-inner-pages="<?= base64_encode('/vl/results/updateVlTestResult.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Enter Result Manually"); ?></a></li>
										<li class="allMenu vlResultsToDamaMenu"><a href="/vl/results/uploadVlResultToDama.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Upload Results To Dama"); ?></a></li>
										<li class="allMenu vlFailedResultMenu"><a href="/vl/results/vl-failed-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Failed/Hold Samples"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlResultApproval.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlResultApprovalMenu"><a href="/vl/results/vlResultApproval.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Results Status"); ?></a></li>
									<?php }
									//if (isset($_SESSION['privileges']) && in_array("vlResultMail.php", $_SESSION['privileges'])) {
									?>
									<!-- <li class="allMenu vlResultMailMenu"><a href="/mail/vlResultMail.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("E-mail Test Result"); ?></a></li> -->
									<?php
									// }
									?>
								</ul>
							</li>
						<?php }
						if ($vlManagementMenuAccess === true) { ?>
							<li class="treeview program">
								<a href="#">
									<span class="fa-solid fa-book"></span>
									<span><?php echo Translation::translate("Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("vl-sample-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu missingResultMenu"><a href="/vl/program-management/vl-sample-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Status Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlControlReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlControlReport"><a href="/vl/program-management/vlControlReport.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Control Report"); ?></a></li>
									<?php } ?>
									<!--<li><a href="#"><span class="fa-solid fa-caret-right"></span> TOT Report</a></li>
                                <li><a href="#"><span class="fa-solid fa-caret-right"></span> VL Suppression Report</a></li>-->
									<?php if (isset($_SESSION['privileges']) && in_array("vl-export-data.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlResultMenu"><a href="/vl/program-management/vl-export-data.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Export Results"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlPrintResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlPrintResultMenu"><a href="/vl/results/vlPrintResult.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Print Result"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("highViralLoad.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlHighMenu"><a href="/vl/program-management/highViralLoad.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Clinic Reports"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("patientList.php", $_SESSION['privileges'])) { ?>
										<!--<li class="allMenu patientList"><a href="patientList.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Export Patient List"); ?></a></li>-->
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlWeeklyReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlWeeklyReport"><a href="/vl/program-management/vlWeeklyReport.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("VL Lab Weekly Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("sampleRejectionReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu sampleRejectionReport"><a href="/vl/program-management/sampleRejectionReport.php"><span class="fa-solid fa-caret-right"></span> Sample Rejection <?php echo Translation::translate("Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlMonitoringReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlMonitoringReport"><a href="/vl/program-management/vlMonitoringReport.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Monitoring Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && isset($arr['vl_monthly_target']) && $arr['vl_monthly_target'] == 'yes' && in_array("vlMonthlyThresholdReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlMonthlyThresholdReport"><a href="/vl/program-management/vlTestingTargetReport.php"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("VL Testing Target Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && isset($arr['vl_monthly_target']) && $arr['vl_monthly_target'] == 'yes' && in_array("vlSuppressedTargetReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlSuppressedMonthlyThresholdReport"><a href="/vl/program-management/vlSuppressedTargetReport.php"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("VL Suppression Target Report"); ?></a></li>
									<?php } ?>
								</ul>
							</li>
						<?php
						}
					}

					if (isset(SYSTEM_CONFIG['modules']['eid']) && SYSTEM_CONFIG['modules']['eid'] === true && array_intersect($_SESSION['module'], array('eid'))) {  ?>
						<li class="header"><?php echo Translation::translate("EARLY INFANT DIAGNOSIS (EID)"); ?></li>
						<?php if ($eidTestRequestMenuAccess === true) { ?>
							<li class="treeview eidRequest">
								<a href="#">
									<span class="fa-solid fa-pen-to-square"></span>
									<span><?php echo Translation::translate("Request Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("eid-requests.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidRequestMenu">
											<a href="/eid/requests/eid-requests.php" data-inner-pages="<?= base64_encode('/eid/requests/eid-edit-request.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("View Test Requests"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("eid-add-request.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu addEidRequestMenu">
											<a href="/eid/requests/eid-add-request.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add New Request"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("addSamplesFromManifest.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] != 'remoteuser')) { ?>
										<li class="allMenu addSamplesFromManifestEidMenu">
											<a href="/eid/requests/addSamplesFromManifest.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add Samples from Manifest"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("eid-batches.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidBatchCodeMenu">
											<a href="/eid/batch/eid-batches.php" data-inner-pages="<?= base64_encode('/eid/batch/eid-add-batch.php') . ';' . base64_encode('/eid/batch/eid-edit-batch.php') . ';' . base64_encode('/eid/batch/eid-add-batch-position.php') . ';' . base64_encode('/eid/batch/eid-edit-batch-position.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Batch"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("specimenReferralManifestList.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] == 'remoteuser')) { ?>
										<li class="allMenu specimenReferralManifestListEIDMenu">
											<a href="/specimen-referral-manifest/specimenReferralManifestList.php?t=<?php echo base64_encode('eid'); ?>" data-inner-pages="<?= base64_encode('/specimen-referral-manifest/addSpecimenReferralManifest.php?t=' . base64_encode('eid')) . ';' . base64_encode('/specimen-referral-manifest/editSpecimenReferralManifest.php?t=' . base64_encode('eid')) . ';' . base64_encode('/specimen-referral-manifest/move-manifest.php?t=' . base64_encode('eid')); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("EID Specimen Manifest"); ?></a>
										</li>
									<?php } ?>
								</ul>
							</li>
						<?php }
						if (isset(SYSTEM_CONFIG['modules']['eid']) && SYSTEM_CONFIG['modules']['eid'] === true && $eidTestResultMenuAccess === true) { ?>
							<li class="treeview eidResults">
								<a href="#">
									<span class="fa-solid fa-list-check"></span>
									<span><?php echo Translation::translate("Test Result Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("addImportResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidImportResultMenu"><a href="/import-result/addImportResult.php?t=<?php echo base64_encode('eid'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Import Result From File"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("eid-manual-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidResultsMenu"><a href="/eid/results/eid-manual-results.php" data-inner-pages="<?= base64_encode('/eid/results/eid-update-result.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Enter Result Manually"); ?></a></li>
										<li class="allMenu eidFailedResultsMenu"><a href="/eid/results/eid-failed-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Failed/Hold Samples"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("eid-result-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidResultStatus"><a href="/eid/results/eid-result-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Results Status"); ?></a></li>
									<?php } ?>
								</ul>
							</li>
						<?php }
						if (isset(SYSTEM_CONFIG['modules']['eid']) && SYSTEM_CONFIG['modules']['eid'] === true && $eidManagementMenuAccess === true) { ?>
							<li class="treeview eidProgramMenu">
								<a href="#">
									<span class="fa-solid fa-book"></span>
									<span><?php echo Translation::translate("Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("eid-sample-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidSampleStatus"><a href="/eid/management/eid-sample-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Status Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("eid-export-data.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidExportResult"><a href="/eid/management/eid-export-data.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Export Results"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("eid-print-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidPrintResults"><a href="/eid/results/eid-print-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Print Result"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("eid-sample-rejection-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidSampleRejectionReport"><a href="/eid/management/eid-sample-rejection-report.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Rejection Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("eid-clinic-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidClinicReport"><a href="/eid/management/eid-clinic-report.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Clinic Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("eidMonthlyThresholdReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu eidMonthlyThresholdReport"><a href="/eid/management/eidTestingTargetReport.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("EID Testing Target Report"); ?></a></li>
									<?php } ?>
								</ul>
							</li>
					<?php }
					} ?>

					<!-- COVID-19 START -->
					<?php if (isset(SYSTEM_CONFIG['modules']['covid19']) && SYSTEM_CONFIG['modules']['covid19'] === true && array_intersect($_SESSION['module'], array('covid19'))) {  ?>
						<li class="header"><?php echo Translation::translate("COVID-19"); ?></li>
						<?php if ($covid19TestRequestMenuAccess === true) { ?>
							<li class="treeview covid19Request">
								<a href="#">
									<span class="fa-solid fa-pen-to-square"></span>
									<span><?php echo Translation::translate("Request Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("covid-19-requests.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19RequestMenu">
											<a href="/covid-19/requests/covid-19-requests.php" data-inner-pages="<?= base64_encode('/covid-19/requests/covid-19-edit-request.php') . ';' . base64_encode('/covid-19/requests/covid-19-bulk-import-request.php') . ';' . base64_encode('/covid-19/requests/covid-19-quick-add.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("View Test Requests"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("covid-19-add-request.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu addCovid19RequestMenu">
											<a href="/covid-19/requests/covid-19-add-request.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add New Request"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("addSamplesFromManifest.php", $_SESSION['privileges'])  && ($_SESSION['instanceType'] != 'remoteuser')) { ?>
										<li class="allMenu addSamplesFromManifestCovid19Menu">
											<a href="/covid-19/requests/addSamplesFromManifest.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add Samples from Manifest"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("covid-19-batches.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19BatchCodeMenu">
											<a href="/covid-19/batch/covid-19-batches.php" data-inner-pages="<?= base64_encode('/covid-19/batch/covid-19-add-batch.php') . ';' . base64_encode('/covid-19/batch/covid-19-edit-batch.php') . ';' . base64_encode('/covid-19/batch/covid-19-add-batch-position.php') . ';' . base64_encode('/covid-19/batch/covid-19-edit-batch-position.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Batch"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("specimenReferralManifestList.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] == 'remoteuser')) { ?>
										<li class="allMenu specimenReferralManifestListC19Menu">
											<a href="/specimen-referral-manifest/specimenReferralManifestList.php?t=<?php echo base64_encode('covid19'); ?>" data-inner-pages="<?= base64_encode('/specimen-referral-manifest/addSpecimenReferralManifest.php?t=' . base64_encode('covid19')) . ';' . base64_encode('/specimen-referral-manifest/editSpecimenReferralManifest.php?t=' . base64_encode('covid19')) . ';' . base64_encode('/specimen-referral-manifest/move-manifest.php?t=' . base64_encode('covid19')); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Covid-19 Specimen Manifest"); ?></a>
										</li>
									<?php } ?>
								</ul>
							</li>
						<?php }
						if (isset(SYSTEM_CONFIG['modules']['covid19']) && SYSTEM_CONFIG['modules']['covid19'] === true && $covid19TestResultMenuAccess === true) { ?>
							<li class="treeview covid19Results">
								<a href="#">
									<span class="fa-solid fa-list-check"></span>
									<span><?php echo Translation::translate("Test Result Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("addImportResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19ImportResultMenu"><a href="/import-result/addImportResult.php?t=<?php echo base64_encode('covid19'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Import Result From File"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("covid-19-manual-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19ResultsMenu"><a href="/covid-19/results/covid-19-manual-results.php" data-inner-pages="<?= base64_encode('/covid-19/batch/covid-19-update-result.php'); ?>"><span class=" fa-solid fa-caret-right"></span> <?php echo Translation::translate("Enter Result Manually"); ?></a></li>
										<li class="allMenu covid19FailedResultsMenu"><a href="/covid-19/results/covid-19-failed-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Failed/Hold Samples"); ?></a></li>
									<?php }
									if ($arr['covid19_positive_confirmatory_tests_required_by_central_lab'] == 'yes' && isset($_SESSION['privileges']) && in_array("covid-19-confirmation-manifest.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19ResultsConfirmationMenu"><a href="/covid-19/results/covid-19-confirmation-manifest.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Confirmation Manifest"); ?></a></li>
									<?php }
									if ($arr['covid19_positive_confirmatory_tests_required_by_central_lab'] == 'yes' && isset($_SESSION['privileges']) && in_array("can-record-confirmatory-tests.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] != 'remoteuser')) { ?>
										<li class="allMenu canRecordConfirmatoryTestsCovid19Menu"><a href="/covid-19/results/can-record-confirmatory-tests.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Record Confirmatory Tests"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("covid-19-result-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19ResultStatus"><a href="/covid-19/results/covid-19-result-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Results Status"); ?></a></li>
									<?php }
									//if (isset($_SESSION['privileges']) && in_array("mail-covid-19-results.php", $_SESSION['privileges'])) {
									?>
									<!-- <li class="allMenu covid19ResultMailMenu"><a href="/covid-19/mail/mail-covid-19-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("E-mail Test Result"); ?></a></li> -->
									<?php
									//}
									if (isset($_SESSION['privileges']) && in_array("covid-19-qc-data.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19QcDataMenu"><a href="/covid-19/results/covid-19-qc-data.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Covid-19 QC Data"); ?></a></li>
									<?php }  ?>
								</ul>
							</li>
						<?php }
						if (isset(SYSTEM_CONFIG['modules']['covid19']) && SYSTEM_CONFIG['modules']['covid19'] === true && $covid19ManagementMenuAccess === true) { ?>
							<li class="treeview covid19ProgramMenu">
								<a href="#">
									<span class="fa-solid fa-book"></span>
									<span><?php echo Translation::translate("Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("covid-19-sample-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19SampleStatus"><a href="/covid-19/management/covid-19-sample-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Status Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("covid-19-export-data.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19ExportResult"><a href="/covid-19/management/covid-19-export-data.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Export Results"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("covid-19-print-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19PrintResults"><a href="/covid-19/results/covid-19-print-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Print Result"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("covid-19-sample-rejection-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19SampleRejectionReport"><a href="/covid-19/management/covid-19-sample-rejection-report.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Rejection Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("covid-19-clinic-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19ClinicReportMenu"><a href="/covid-19/management/covid-19-clinic-report.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Clinic Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("covid19MonthlyThresholdReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu covid19MonthlyThresholdReport"><a href="/covid-19/management/covid19TestingTargetReport.php"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("COVID-19 Testing Target Report"); ?></a></li>
									<?php } ?>
								</ul>
							</li>
					<?php }
					} ?>
					<!-- COVID-19 END -->

					<!-- HEPATITIS START -->
					<?php if (isset(SYSTEM_CONFIG['modules']['hepatitis']) && SYSTEM_CONFIG['modules']['hepatitis'] === true && array_intersect($_SESSION['module'], array('hepatitis'))) {  ?>
						<li class="header"><?php echo Translation::translate("Hepatitis"); ?></li>
						<?php if ($hepatitisTestRequestMenuAccess === true) { ?>
							<li class="treeview hepatitisRequest">
								<a href="#">
									<span class="fa-solid fa-pen-to-square"></span>
									<span><?php echo Translation::translate("Request Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("hepatitis-requests.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisRequestMenu">
											<a href="/hepatitis/requests/hepatitis-requests.php" data-inner-pages="<?= base64_encode('/hepatitis/requests/hepatitis-edit-request.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("View Test Requests"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("hepatitis-add-request.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu addHepatitisRequestMenu">
											<a href="/hepatitis/requests/hepatitis-add-request.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add New Request"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("add-samples-from-manifest.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] != 'remoteuser')) { ?>
										<li class="allMenu addSamplesFromManifestHepatitisMenu">
											<a href="/hepatitis/requests/add-samples-from-manifest.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add Samples from Manifest"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("hepatitis-batches.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisBatchCodeMenu">
											<a href="/hepatitis/batch/hepatitis-batches.php" data-inner-pages="<?= base64_encode('/hepatitis/batch/hepatitis-add-batch.php') . ';' . base64_encode('/hepatitis/batch/hepatitis-edit-batch.php') . ';' . base64_encode('/hepatitis/batch/hepatitis-add-batch-position.php') . ';' . base64_encode('/hepatitis/batch/hepatitis-edit-batch-position.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Batch"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("specimenReferralManifestList.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] == 'remoteuser')) { ?>
										<li class="allMenu specimenReferralManifestListHepMenu">
											<a href="/specimen-referral-manifest/specimenReferralManifestList.php?t=<?php echo base64_encode('hepatitis'); ?>" data-inner-pages="<?= base64_encode('/specimen-referral-manifest/addSpecimenReferralManifest.php?t=' . base64_encode('hepatitis')) . ';' . base64_encode('/specimen-referral-manifest/editSpecimenReferralManifest.php?t=' . base64_encode('hepatitis')) . ';' . base64_encode('/specimen-referral-manifest/move-manifest.php?t=' . base64_encode('hepatitis')); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Hepatitis Specimen Manifest"); ?></a>
										</li>
									<?php } ?>
								</ul>
							</li>
						<?php }
						if (isset(SYSTEM_CONFIG['modules']['hepatitis']) && SYSTEM_CONFIG['modules']['hepatitis'] === true && $hepatitisTestResultMenuAccess === true) { ?>
							<li class="treeview hepatitisResults">
								<a href="#">
									<span class="fa-solid fa-list-check"></span>
									<span><?php echo Translation::translate("Test Result Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("addImportResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisImportResultMenu"><a href="/import-result/addImportResult.php?t=<?php echo base64_encode('hepatitis'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Import Result From File"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("hepatitis-manual-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisResultsMenu"><a href="/hepatitis/results/hepatitis-manual-results.php" data-inner-pages="<?= base64_encode('/hepatitis/results/hepatitis-update-result.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Enter Result Manually"); ?></a></li>
										<li class="allMenu hepatitisFailedResultsMenu"><a href="/hepatitis/results/hepatitis-failed-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Failed/Hold Samples"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("hepatitis-result-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisResultStatus"><a href="/hepatitis/results/hepatitis-result-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Results Status"); ?></a></li>
									<?php }
									//    if (isset($_SESSION['privileges']) && in_array("mail-hepatitis-results.php", $_SESSION['privileges'])) {
									?>
									<!-- <li class="allMenu hepatitisResultMailMenu"><a href="/hepatitis/mail/mail-hepatitis-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("E-mail Test Result"); ?></a></li> -->
									<?php
									// }
									?>
								</ul>
							</li>
						<?php }
						if (isset(SYSTEM_CONFIG['modules']['hepatitis']) && SYSTEM_CONFIG['modules']['hepatitis'] === true && $hepatitisManagementMenuAccess === true) { ?>
							<li class="treeview hepatitisProgramMenu">
								<a href="#">
									<span class="fa-solid fa-book"></span>
									<span><?php echo Translation::translate("Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("hepatitis-sample-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisSampleStatus"><a href="/hepatitis/management/hepatitis-sample-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Status Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("hepatitis-export-data.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisExportResult"><a href="/hepatitis/management/hepatitis-export-data.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Export Results"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("hepatitis-print-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisPrintResults"><a href="/hepatitis/results/hepatitis-print-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Print Result"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("hepatitis-sample-rejection-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisSampleRejectionReport"><a href="/hepatitis/management/hepatitis-sample-rejection-report.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Rejection Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("hepatitis-clinic-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisClinicReportMenu"><a href="/hepatitis/management/hepatitis-clinic-report.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Clinic Report"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("hepatitis-testing-target-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu hepatitisMonthlyThresholdReport"><a href="/hepatitis/management/hepatitis-testing-target-report.php"><span class="fa-solid fa-caret-right"></span><?php echo Translation::translate("Hepatitis Testing Target Report"); ?></a></li>
									<?php } ?>
								</ul>
							</li>
					<?php }
					} ?>
					<!-- HEPATITIS END -->

					<!-- TB START -->
					<?php if (isset(SYSTEM_CONFIG['modules']['tb']) && SYSTEM_CONFIG['modules']['tb'] === true && array_intersect($_SESSION['module'], array('tb'))) {  ?>
						<li class="header"><?php echo Translation::translate("TB"); ?></li>
						<?php if ($tbTestRequestMenuAccess === true) { ?>
							<li class="treeview tbRequest">
								<a href="#">
									<span class="fa-solid fa-pen-to-square"></span>
									<span><?php echo Translation::translate("Request Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("tb-requests.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbRequestMenu">
											<a href="/tb/requests/tb-requests.php" data-inner-pages="<?= base64_encode('/tb/requests/tb-edit-request.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("View Test Requests"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("tb-add-request.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu addTbRequestMenu">
											<a href="/tb/requests/tb-add-request.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add New Request"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("addSamplesFromManifest.php", $_SESSION['privileges'])  && ($_SESSION['instanceType'] != 'remoteuser')) { ?>
										<li class="allMenu addSamplesFromManifestTbMenu">
											<a href="/tb/requests/addSamplesFromManifest.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Add Samples from Manifest"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("tb-batches.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbBatchCodeMenu">
											<a href="/tb/batch/tb-batches.php" data-inner-pages="<?= base64_encode('/tb/batch/tb-add-batch.php') . ';' . base64_encode('/tb/batch/tb-edit-batch.php') . ';' . base64_encode('/tb/batch/tb-add-batch-position.php') . ';' . base64_encode('/tb/batch/tb-edit-batch-position.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Batch"); ?></a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("specimenReferralManifestList.php", $_SESSION['privileges']) && ($_SESSION['instanceType'] == 'remoteuser')) { ?>
										<li class="allMenu specimenReferralManifestListC19Menu">
											<a href="/specimen-referral-manifest/specimenReferralManifestList.php?t=<?php echo base64_encode('tb'); ?>" data-inner-pages="<?= base64_encode('/specimen-referral-manifest/addSpecimenReferralManifest.php?t=' . base64_encode('tb')) . ';' . base64_encode('/specimen-referral-manifest/editSpecimenReferralManifest.php?t=' . base64_encode('tb')) . ';' . base64_encode('/specimen-referral-manifest/move-manifest.php?t=' . base64_encode('tb')); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("TB Specimen Manifest"); ?></a>
										</li>
									<?php } ?>
								</ul>
							</li>
						<?php }
						if (isset(SYSTEM_CONFIG['modules']['tb']) && SYSTEM_CONFIG['modules']['tb'] === true && $tbTestResultMenuAccess === true) { ?>
							<li class="treeview tbResults">
								<a href="#">
									<span class="fa-solid fa-list-check"></span>
									<span><?php echo Translation::translate("Test Result Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("addImportResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbImportResultMenu"><a href="/import-result/addImportResult.php?t=<?php echo base64_encode('tb'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Import Result From File"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("tb-manual-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbResultsMenu"><a href="/tb/results/tb-manual-results.php" data-inner-pages="<?= base64_encode('/tb/results/tb-update-result.php'); ?>"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Enter Result Manually"); ?></a></li>
										<li class="allMenu tbFailedResultsMenu"><a href="/tb/results/tb-failed-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Failed/Hold Samples"); ?></a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("tb-result-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbResultStatus"><a href="/tb/results/tb-result-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Manage Results Status"); ?></a></li>
									<?php }
									//if (isset($_SESSION['privileges']) && in_array("mail-tb-results.php", $_SESSION['privileges'])) {
									?>
									<!-- <li class="allMenu tbResultMailMenu"><a href="/tb/mail/mail-tb-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("E-mail Test Result"); ?></a></li> -->
									<?php
									// }
									?>
								</ul>
							</li>
						<?php }
						if (isset(SYSTEM_CONFIG['modules']['tb']) && SYSTEM_CONFIG['modules']['tb'] === true && $tbManagementMenuAccess === true) { ?>
							<li class="treeview tbProgramMenu">
								<a href="#">
									<span class="fa-solid fa-book"></span>
									<span><?php echo Translation::translate("Management"); ?></span>
									<span class="pull-right-container">
										<span class="fa-solid fa-angle-left pull-right"></span>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("tb-sample-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbSampleStatus"><a href="/tb/management/tb-sample-status.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Status Report"); ?></a></li>
									<?php } ?>
									<?php if (isset($_SESSION['privileges']) && in_array("tb-print-results.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbPrintResults"><a href="/tb/results/tb-print-results.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Print Result"); ?></a></li>
									<?php } ?>
									<?php if (isset($_SESSION['privileges']) && in_array("tb-export-data.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbExportResult"><a href="/tb/management/tb-export-data.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Export Results"); ?></a></li>
									<?php } ?>
									<?php if (isset($_SESSION['privileges']) && in_array("tb-sample-rejection-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbSampleRejectionReport"><a href="/tb/management/tb-sample-rejection-report.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Sample Rejection Report"); ?></a></li>
									<?php } ?>
									<?php if (isset($_SESSION['privileges']) && in_array("tb-clinic-report.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu tbClinicReport"><a href="/tb/management/tb-clinic-report.php"><span class="fa-solid fa-caret-right"></span> <?php echo Translation::translate("Clinic Report"); ?></a></li>
									<?php } ?>
								</ul>
							</li>
					<?php }
					} ?>
					<!-- TB END -->
				</ul>
			</section>
			<!-- /.sidebar -->
		</aside>
		<!-- content-wrapper -->
		<div id="dDiv" class="dialog">
			<div style="text-align:center"><span onclick="closeModal();" style="float:right;clear:both;" class="closeModal"></span></div>
			<iframe id="dFrame" src="" title="LIS Content" style="border:none;" scrolling="yes" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0">
				<?= _("Unable to load this page or resource"); ?>
			</iframe>
		</div>