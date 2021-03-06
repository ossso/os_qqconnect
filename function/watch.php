<?php
/**
 * 监听路由
 */
function os_qqconnect_Watch($url) {
    global $zbp;
    $status = strripos($url, '/os_qqconnect');
    if ($status == -1) {
        return false;
    }
    // 匹配路由
    $regexp = "/\/os_qqconnect\/([a-z0-9\-\_]*)/";
    $routes = array();
    preg_match_all($regexp, $url, $routes);

    $type = null;
    if (isset($routes[1]) && count($routes[1]) > 0) {
        $type = $routes[1][0];
    }

    $status = os_qqconnect_WatchHandler($type);

    if (!$status) return false;

    // 阻断后面内容
    $GLOBALS['hooks']['Filter_Plugin_ViewAuto_Begin']['os_qqconnect_Watch'] = 'return';
}

/**
 * 监听cmd接口
 */
function os_qqconnect_WatchCmd() {
    global $zbp;
    $action = GetVars('act','GET');
    if ($action != "os_qqconnect") {
        return false;
    }

    $type = GetVars('type','GET');

    os_qqconnect_WatchHandler($type);
}

/**
 * 处理相关事件
 */
function os_qqconnect_WatchHandler($type) {
    global $zbp;
    switch ($type) {
        case 'login':
            include ZBP_PATH . 'zb_users/plugin/os_qqconnect/libs/qq_connect/index.php';
            return true;
        case 'callback':
            include ZBP_PATH . 'zb_users/plugin/os_qqconnect/libs/qq_connect/callback.php';
            return true;
        case 'bind':
            if ($zbp->Config('os_qqconnect')->active == '1') {
                include ZBP_PATH . 'zb_users/plugin/os_qqconnect/page/bind.php';
            } else {
                return false;
            }
            /**
             * 不可删除版权声明，否则视为不尊重版权，不再提供任何服务支持
             */
            echo "<!--本插件由橙色阳光提供，https://www.os369.com/-->\r\n";
            return true;
        case 'bind_account':
            os_qqconnect_Event_ThirdBindLogin();
            return true;
        case 'create_account':
            os_qqconnect_Event_ThirdBindCreate();
            return true;
        case 'manage':
            os_qqconnect_Event_ManageUser();
            return true;
    }
    return false;
}

/**
 * 处理用户头像输出
 */
function os_qqconnect_WatchAvatar($member) {
    global $zbp;
    $s = $zbp->usersdir . 'avatar/' . $member->ID . '.png';
    if (is_readable($s)) {
        return $zbp->host . 'zb_users/avatar/' . $member->ID . '.png';
    } else if ($member->Metas->os_qqconnect_avatar_qq) {
        // 强制HTTPS输出头像
        $avatar = str_replace("http://","https://",$member->Metas->os_qqconnect_avatar_qq);
        return $avatar;
    }
}
