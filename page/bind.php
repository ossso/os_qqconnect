<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge,chrome=1" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0"/>
    <meta name="renderer" content="webkit" />
	<meta name="robots" content="none" />
    <link rel="stylesheet" href="<?php echo $zbp->host ?>zb_users/plugin/os_qqconnect/static/bind.css" type="text/css" />
	<script src="<?php echo $zbp->host ?>zb_system/script/common.js" type="text/javascript"></script>
	<script src="<?php echo $zbp->host ?>zb_system/script/md5.js" type="text/javascript"></script>
	<script src="<?php echo $zbp->host ?>zb_system/script/c_admin_js_add.php" type="text/javascript"></script>
	<title>QQ互联用户绑定 - <?php echo $zbp->name ?></title>
</head>
<body>
<div class="bg">
    <span class="logo"><?php echo $zbp->name ?></span>
</div>
<div class="login-group">
    <form action="<?php echo os_qqconnect_Event_GetURL('bind-account') ?>">
        <div class="login-item">
            <label for="username">账号</label>
            <input type="text" name="username" id="username" class="login-item-input" />
        </div>
        <div class="login-item">
            <label for="password">密码</label>
            <input type="text" name="password" id="password" class="login-item-input" />
        </div>
    </form>
</div>
</body>
</html>
<?php
RunTime();
?>
