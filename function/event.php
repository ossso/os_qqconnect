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
        case 'manage':
            $third_url .= 'manage';
        break;
    }

    return $third_url;
}

/**
 * 社交账户绑定
 */
function os_qqconnect_Event_ThirdBind($openid, $token) {
    global $zbp;

    if ($zbp->Config('os_qqconnect')->active != "1") {
        return false;
    }

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

    if ($zbp->Config('os_qqconnect')->active != "1") {
        return false;
    }

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

    if ($zbp->Config('os_qqconnect')->active != "1") {
        return false;
    }

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

    if ($zbp->Config('os_qqconnect')->active != "1") {
        return false;
    }

    $t = new OS_QQConnect;
    $status = $t->LoadInfoByOpenID($openid, 0);
    if (!$status) {
        echo 'Login Error 1, 登录异常';
        exit;
    }

    $m = new Member;
    $status = $m->LoadInfoByID($t->UID);
    if (!$status) {
        echo 'Login Error 2, 登录异常';
        exit;
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

/**
 * 第三方绑定登录
 */
function os_qqconnect_Event_ThirdBindLogin() {
    global $zbp;
    if ($zbp->Config('os_qqconnect')->active != "1") {
        return false;
    }
    $json = array();
    $username = trim(GetVars("username", "POST"));
    $password = trim(GetVars("password", "POST"));
    if ($zbp->Verify_MD5(GetVars('username', 'POST'), GetVars('password', 'POST'), $m)) {
        $zbp->user = $m;
        $un = $m->Name;
        $ps = $m->PassWord_MD5Path;
        if ($zbp->user->Status != 0) {
            $json['code'] = 200100;
            $json['message'] = "已被限制登录";
        } else {
            setcookie("username", $un, 0, $zbp->cookiespath);
        	setcookie("password", $ps, 0, $zbp->cookiespath);
            if (!session_id()) {
                session_start();
            }
            $access_token = $_SESSION['qq_token']; // 用户识别
    		$openid = $_SESSION['qq_openid']; // 用户ID
            if (empty($openid) || empty($access_token)) {
                $json['code'] = 200101;
                $json['message'] = "绑定失败，授权信息遗失";
            } else {
                // 执行绑定
                os_qqconnect_Event_ThirdBind($openid, $access_token);
                $json['code'] = 100000;
                $json['message'] = "绑定成功";
            }
        }
    } else {
        $json['code'] = 200000;
        $json['message'] = "用户名或密码错误";
    }

    echo json_encode($json);
    exit;
}

/**
 * 绑定自动生成的账户
 */
function os_qqconnect_Event_ThirdBindCreate() {
    global $zbp;
    if ($zbp->Config('os_qqconnect')->active != "1") {
        return false;
    }
    if ($zbp->Config('os_qqconnect')->user_auto_create != "1") {
        return false;
    }
    if (!session_id()) {
        session_start();
    }
    $access_token = $_SESSION['qq_token']; // 用户识别
    $openid = $_SESSION['qq_openid']; // 用户ID
    if (empty($openid) || empty($access_token)) {
        return false;
    }
    // 生成唯一Name
    $md5ID = md5($openid);
    $md5ID = substr($md5ID, 8, 16);

    $level = 6;
    if ($zbp->Config('os_qqconnect')->user_reg_level) {
        $level = $zbp->Config('os_qqconnect')->user_reg_level;
    }

    $mem = new Member;
    $mem->Name = "third_qq_".$md5ID;
    $mem->Level = $level;
    $mem->IP = GetGuestIP();
    $mem->Guid = GetGuid();
    $mem->PostTime = time();
    $mem->Password = Member::GetPassWordByGuid($access_token, $mem->Guid);
    // 自动同步昵称
    $mem->Metas->os_qqconnect_third_info = "1";
    $mem->Save();

    CountMember($mem, array(null, null, null, null));

    $zbp->user = $mem;
    $un = $mem->Name;
    $ps = $mem->PassWord_MD5Path;
    setcookie("username", $un, 0, $zbp->cookiespath);
	setcookie("password", $ps, 0, $zbp->cookiespath);

    // 执行绑定
    os_qqconnect_Event_ThirdBind($openid, $access_token);

    // 方法执行完毕后 回到对应页面
    $sourceUrl = GetVars('sourceUrl', 'COOKIE');
    if (empty($sourceUrl)) {
        $sourceUrl = $zbp->host;
    }
    Redirect($sourceUrl);
}

/**
 * 显示绑定用户列表
 */
function os_qqconnect_Event_GetUserList() {
    global $zbp;
    $page = GetVars("page", "GET");
    $page = (int)$page>0?(int)$page:1;
    $pagebar = new Pagebar('{%host%}zb_users/plugin/os_qqconnect/user-list.php?page={%page%}', false);
    $pagebar->PageCount = 20;
    $pagebar->PageNow = $page;
    $pagebar->PageBarCount = $zbp->pagebarcount;
    $pagebar->UrlRule->Rules['{%page%}'] = $page;

    $w = array();
    $w = array("=", "third_Type", "0");

    $limit = array(($pagebar->PageNow - 1) * $pagebar->PageCount, $pagebar->PageCount);
    $option = array('pagebar' => $pagebar);

    $sql = $zbp->db->sql->Select(
        $zbp->table['os_qqconnect'],
        array("*"),
        $w,
        null,
        $limit,
        $option
    );
    $result = $zbp->GetListType('OS_QQConnect', $sql);

    return array(
        "list"     => $result,
        "pagebar"  => $pagebar,
    );
}

/**
 * 管理操作
 */
function os_qqconnect_Event_ManageUser() {
    global $zbp;
    $json = array();

    if ($zbp->user->Level > 1) {
        $json['code'] = 200200;
        $json['message'] = "您的权限不足";
        echo json_encode($json);
        exit;
    }

    $id = GetVars('id', "POST");
    $type = GetVars('type', "POST");
    $t = new OS_QQConnect;
    $t->LoadInfoByID($id);

    if ($type == "unbind") {
        $t->Del();
        $json['code'] = 100000;
        $json['message'] = "操作成功";
    } elseif ($type == "lock") {
        $t->User->Status = $t->User->Status==1?0:1;
        $t->User->Save();
        $json['code'] = 100000;
        $json['message'] = "操作成功";
        $json['result'] = $t->User->Status;
    }

    echo json_encode($json);
    exit;
}


/**
 * 前台插入cookie来源
 */
function os_qqconnect_Event_FrontOutput() {
    global $zbp;
    if ($zbp->Config('os_qqconnect')->source_switch != "1") {
        return null;
    }
    echo "\r\n".'!function() {$(document).on("click", ".os-qqconnect-link", function() { zbp.cookie.set("sourceUrl", window.location.href); })};'."\r\n";
}
