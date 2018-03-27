<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
if (!$zbp->CheckPlugin('os_qqconnect')) {$zbp->ShowError(48);die();}

include ZBP_PATH . 'zb_users/plugin/os_qqconnect/libs/qq_connect/callback.php';
