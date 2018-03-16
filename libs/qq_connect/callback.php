<?php
require "API/qqConnectAPI.php";
try {
    $qc = new QC();
    $access_token = $qc->qq_callback();
    $openid = $qc->get_openid();
} catch (Exception $e) {
    echo '系统异常<br/>';
    die();
}

// 第一步 查询绑定状态
$status = os_qqconnect_Event_GetThirdInfo($openid);
// 已绑定
if ($status) {
    // 执行第三方登录
    os_qqconnect_Event_ThirdLogin($openid, $access_token);
} else {
    // 未绑定 再判断是否登录 如果登录就直接绑定
    if ($zbp->user->ID > 0) {
        // 执行绑定方法
        os_qqconnect_Event_ThirdBind($openid, $access_token);
    } else {
        if (!session_id()) {
            session_start();
        }
        $_SESSION['qq_token'] = $access_token; // 用户识别
		$_SESSION['qq_openid'] = $openid; // 用户ID
        Redirect(os_qqconnect_Event_GetURL('bind'));
    }
}

// 方法执行完毕后 回到对应页面
$sourceUrl = GetVars('sourceUrl', 'COOKIE');
if (empty($sourceUrl)) {
    $sourceUrl = $zbp->host;
}
Redirect($sourceUrl);
