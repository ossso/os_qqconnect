<?php
/**
 * 获取链接地址
 */
function os_qqconnect_Event_GetURL($type) {
    global $zbp;

    if ($zbp->option['ZC_STATIC_MODE'] == 'REWRITE') {
        $third_url = $zbp->host . 'os_qqconnect/';
    } else {
        $third_url = $zbp->host . 'zb_system/cmd.php?act=os_qqconnect&type=';
    }

    switch ($type) {
        case 'login':
            $third_url .= 'login';
        break;
        case 'callback':
            $third_url .= 'callback';
        break;
        case 'bind':
            $third_url .= 'bind';
        break;
        case 'bind-account':
            $third_url .= 'bind_account';
        break;
        case 'create-account':
            $third_url .= 'create_account';
        break;
    }

    return $third_url;
}

/**
 * 社交账户绑定
 */
function os_qqconnect_Event_ThirdBind($openid, $token) {
    global $zbp;

    $t = new OS_QQConnect;
    $t->Type = 0;
    $t->OpenID = $openid;
    $t->Token = $token;
    $t->UID = $zbp->user->ID;
    $t->Save();

    os_qqconnect_Event_ThirdSyncInfoByQQ($openid, $token);

    return true;
}

/**
 * 查询是否绑定
 */
function os_qqconnect_Event_GetThirdInfo($openid) {
    global $zbp;
    $t = new OS_QQConnect;
    $status = $t->LoadInfoByOpenID($openid, 0);
    if (!$status) {
        return false;
    }

    $m = new Member;
    $status = $m->LoadInfoByID($t->UID);
    if (!$status) {
        return false;
    }

    return true;
}

/**
 * os_qqconnect_Event_ThirdSyncInfoByQQ
 * 同步用户的QQ信息回来
 */
function os_qqconnect_Event_ThirdSyncInfoByQQ($openid, $token) {
    global $zbp;
    $t = new OS_QQConnect;
    $status = $t->LoadInfoByOpenID($openid, 0);
    if (!$status) {
        return false;
    }

    $result = file_get_contents('https://graph.qq.com/user/get_user_info?access_token='.$token.'&oauth_consumer_key='.$zbp->Config('os_qqconnect')->appid.'&openid='.$openid);
    $result = json_decode($result);

    if ($result->ret != '0') {
        return false;
    }

    // 保存资料
    $t->Nickname = $result->nickname;
    $t->Avatar = empty($result->figureurl_qq_2)?$result->figureurl_2:$result->figureurl_qq_2;
    $t->Save();

    // 确认是否需要同步资料
    $m = new Member();
    $status = $m->LoadInfoByID($t->UID);
    if (!$status) {
        return false;
    }
    $update_status = false;
    // 同步头像 -> QQ
    $m->Metas->os_qqconnect_avatar_qq = $t->Avatar;
    // 判断用户是否需要同步昵称
    if ($m->Metas->os_qqconnect_third_info == '1') {
        $m->Alias = $t->Nickname;
        $m->Metas->Del('os_qqconnect_third_info');
    }
    $m->Save();
    return true;
}

/**
 * 第三方的登录方法
 */
function os_qqconnect_Event_ThirdLogin($openid, $token, $thirdClass = null) {
    global $zbp;
    $t = new OS_QQConnect;
    $status = $t->LoadInfoByOpenID($openid, 0);
    if (!$status) {
        echo 'Login Error 1, 登录异常';
        die();
    }

    $m = new Member;
    $status = $m->LoadInfoByID($t->UID);
    if (!$status) {
        echo 'Login Error 2, 登录异常';
        die();
    }

    // 将用户信息载入$zbp中
    $zbp->user = $m;
    $un = $m->Name;
    $ps = $m->PassWord_MD5Path;
    setcookie("username", $un, 0, $zbp->cookiespath);
	setcookie("password", $ps, 0, $zbp->cookiespath);

    // 挂载上接口 会传入third
    if(isset($GLOBALS['hooks']['Filter_Plugin_VerifyLogin_Succeed'])){
        foreach ($GLOBALS['hooks']['Filter_Plugin_VerifyLogin_Succeed'] as $fpname => &$fpsignal) {
            $fpname('third');
        }
    }

    os_qqconnect_Event_ThirdSyncInfoByQQ($openid, $token);

    return true;
}

/**
 * os_qqconnect_Event_GetUserThird 社交信息查询
 */
function os_qqconnect_Event_GetUserThird($uid = false) {
    global $zbp;
    $w = array();
    $w[] = array('=','third_Type', 0);
    if (!$uid) {
        $uid = $zbp->user->ID;
    }
    $w[] = array('=','third_UID', $uid);
    $sql = $zbp->db->sql->Select($zbp->table['os_qqconnect'], '*', $w);
    $result = $zbp->GetListType('OS_QQConnect', $sql);
    return $result;
}
