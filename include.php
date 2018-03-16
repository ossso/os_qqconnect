<?php
include_once __DIR__.'/database/index.php';
include_once __DIR__.'/function/main.php';
#注册插件
RegisterPlugin("os_qqconnect","ActivePlugin_os_qqconnect");

/**
 * 注册接收处理指令
 */
$GLOBALS['actions']['os_qqconnect'] = 6;
function ActivePlugin_os_qqconnect() {
    Add_Filter_Plugin('Filter_Plugin_ViewAuto_Begin','os_qqconnect_Watch');
    Add_Filter_Plugin('Filter_Plugin_Cmd_Begin','os_qqconnect_WatchCmd');
}

function os_qqconnect_SubMenu($id){
	$arySubMenu = array(
		0 => array('应用设置', 'main', 'left', false),
		1 => array('用户列表', 'user-list', 'left', false),
	);

	foreach($arySubMenu as $k => $v){
		echo '<a href="./'.$v[1].'.php" '.($v[3]==true?'target="_blank"':'').'><span class="m-'.$v[2].' '.($id==$k?'m-now':'').'">'.$v[0].'</span></a>';
	}
}

function InstallPlugin_os_qqconnect() {
    os_qqconnect_CreateTable();
}

function UninstallPlugin_os_qqconnect() {

}

/**
 * 返回时间天数
 */
function os_qqconnect_AgoTime($ptime) {
    $ptime = strtotime($ptime);
    $etime = time() - $ptime;
    if($etime < 10) return '刚刚';
    $nowYear = date('Y');
    $setYear = date('Y',$ptime);
    if ($nowYear != $setYear) {
        return date('Y/m/d H:i', $ptime);
    }
    $nowMonth = date('m');
    $setMonth = date('m',$ptime);
    if ($nowMonth != $setMonth) {
        return date('m/d H:i', $ptime);
    }
    $interval = array (
        24 * 60 * 60            =>  '天前',
        60 * 60                 =>  '小时前',
        60                      =>  '分钟前',
        1                       =>  '秒前'
    );
    foreach ($interval as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . $str;
        }
    };
}
