<?php
/* PHP SDK
 * @version 2.0.0 --->官方提示的2.1版本
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

/**
 * 橙色阳光 修改为适应ZBlogPHP的版本 支持1.3+
 * 开发时间 2017年7月27日
 * 开发目标版本 izbp
 * 如果需要增加请求的接口 修改下方的scope即可
 */

// 原版开始
require_once(CLASS_PATH."ErrorCase.class.php");
class Recorder{
    private static $data;
    private $inc;
    private $error;

    public function __construct(){
        global $zbp;
        $this->error = new ErrorCase();

        //-------读取配置文件
        // $incFileContents = file(ROOT."comm/inc.php");
        // $incFileContents = $incFileContents[1];
        // $this->inc = json_decode($incFileContents);

        // ---- os_qqconnect 修改版
        $this->inc = (object) array(
            'appid'     => $zbp->Config('os_qqconnect')->appid,
            'appkey'    => $zbp->Config('os_qqconnect')->appkey,
            'callback'  => os_qqconnect_Event_GetURL('callback'),
            'scope'     => "get_user_info",
        );

        if(empty($this->inc)){
            $this->error->showError("20001");
        }

        if(empty($_SESSION['QC_userData'])){
            self::$data = array();
        }else{
            self::$data = $_SESSION['QC_userData'];
        }
    }

    public function write($name,$value){
        self::$data[$name] = $value;
    }

    public function read($name){
        if(empty(self::$data[$name])){
            return null;
        }else{
            return self::$data[$name];
        }
    }

    public function readInc($name){
        if(empty($this->inc->$name)){
            return null;
        }else{
            return $this->inc->$name;
        }
    }

    public function delete($name){
        unset(self::$data[$name]);
    }

    function __destruct(){
        $_SESSION['QC_userData'] = self::$data;
    }
}
