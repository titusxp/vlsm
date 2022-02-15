<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$adminCount = $db->rawQuery("SELECT * FROM system_admin as ud");
    if(count($adminCount) != 0) {
        header("location:/system-admin/login/login.php");
    }

$path = '/assets/img/remote-bg.jpg';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>VLSM | New User Registration</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="/assets/css/fonts.css">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="/dist/css/AdminLTE.min.css">
    <link href="/assets/css/deforayModal.css" rel="stylesheet" />
    <!-- iCheck -->
    <style>
        body {
            background: #F6F6F6;
            background: #000;

            background: url("<?php echo $path; ?>") center;
            background-size: cover;
            background-repeat: no-repeat;
        }
    </style>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
    <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
</head>

<body class="">
<div class="container-fluid">
    <div id="loginbox" style="margin-top:140px;margin-bottom:70px;float:right;margin-right:509px;" class="mainbox col-md-3 col-sm-8 ">
      <div class="panel panel-default" style="opacity: 0.93;">
        <div class="panel-heading">
                    <div class="panel-title">VLSM New User Register</div>
                </div>

                <div style="padding-top:10px;" class="panel-body">
                    <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>
                    <form id="registerForm" name="registerForm" class="form-horizontal" role="form" method="post" action="registerProcess.php" onsubmit="validateNow();return false;">
            <div style="margin-bottom: 5px" class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
              <input id="login-username" type="text" class="form-control isRequired" name="username" value="" placeholder="User Name" title="Please enter the user name">
            </div>
            <div style="margin-bottom: 5px" class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
              <input id="login-email" type="text" class="form-control isRequired" name="email" value="" placeholder="Email Id" title="Please enter the email id">
            </div>
            <div style="margin-bottom: 5px" class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-log-in"></i></span>
              <input id="login-id" type="text" class="form-control isRequired" name="loginid" value="" placeholder="Login Id" title="Please enter the login id">
            </div>
            <div style="margin-bottom: 5px" class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
              <input type="password" class="form-control ppwd isRequired" id="confirmPassword" name="password" placeholder="Password" title="Please enter the password" />
            </div>
            <div style="margin-bottom: 5px" class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
              <input type="password" class="form-control cpwd confirmPassword" id="confirmPassword" name="password" placeholder="Confirm Password" title="" />
            </div>
                        <div style="margin-top:10px" class="form-group">
                            <!-- Button -->
                            <div class="col-sm-12 controls">
                                <button class="btn btn-lg btn-primary btn-block" onclick="validateNow();return false;">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="/assets/js/deforayValidation.js"></script>
    <script src="/assets/js/jquery.blockUI.js"></script>
    <script type="text/javascript">
  pwdflag = true;

function validateNow() {
  flag = deforayValidator.init({
    formId: 'registerForm'
  });

  if (flag) {
    if ($('.ppwd').val() != '') {
      pwdflag = checkPasswordLength();
    }
    if (pwdflag) {
      $.blockUI();
      document.getElementById('registerForm').submit();
    }
  }
}
function checkPasswordLength() {
    var pwd = $('#confirmPassword').val();
    var regex = /^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9!@#\$%\^\&*\)\(+=. _-]+){8,}$/;
    if (regex.test(pwd) == false) {
      alert("<?= _("Password must be at least 8 characters long and must include AT LEAST one number, one alphabet and may have special characters.") ?>");
      $('.ppwd').focus();
    }
    return regex.test(pwd);
  }
        $(document).ready(function() {
            <?php
            if (isset($_SESSION['alertMsg']) && trim($_SESSION['alertMsg']) != "") {
            ?>
                alert("<?php echo $_SESSION['alertMsg']; ?>");
            <?php
                $_SESSION['alertMsg'] = '';
                unset($_SESSION['alertMsg']);
            }
            ?>
        });
    </script>
</body>

</html>