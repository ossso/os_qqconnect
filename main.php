<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action='root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('os_qqconnect')) {$zbp->ShowError(48);die();}

$blogtitle='QQ互联设置';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<style>
.edit-input {
    display: block;
    width: 100%;
    height: 40px;
    line-height: 24px;
    font-size: 14px;
    padding: 8px;
    box-sizing: border-box;
}
</style>
<div id="divMain">
    <div class="divHeader"><?php echo $blogtitle;?></div>
    <div class="SubMenu"><?php os_qqconnect_SubMenu(0);?></div>
    <div id="divMain2">
        <form action="./save.php?type=base" method="post">
            <table border="1" class="tableFull tableBorder tableBorder-thcenter" style="max-width: 1000px">
                <thead>
                    <tr>
                        <th width="200px">配置名称</th>
                        <th>配置内容</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>启用开关</td>
                        <td>
                            <input name="active" type="text" class="checkbox" style="display:none;" value="<?php echo $zbp->Config('os_qqconnect')->active; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>APP ID</td>
                        <td>
                            <input name="appid" type="text" class="edit-input" value="<?php echo $zbp->Config('os_qqconnect')->appid; ?>" placeholder="请填写QQ互联应用的APP ID" />
                        </td>
                    </tr>
                    <tr>
                        <td>APP Key</td>
                        <td>
                            <input name="appkey" type="text" class="edit-input" value="<?php echo $zbp->Config('os_qqconnect')->appkey; ?>" placeholder="请填写QQ互联应用的APP Key" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type="submit" value="保存配置" style="margin: 0; font-size: 1em;" />
        </form>
        <style>
            .readme {
                max-width: 1000px;
                padding: 10px;
                margin-bottom: 10px;
                background: #f9f9f9;
            }
            .readme h3 {
                font-size: 16px;
                font-weight: normal;
                color: #000;
            }
            .readme ul li {
                margin-bottom: 5px;
                line-height: 30px;
            }
            .readme a {
                color: #333 !important;
                text-decoration: underline;
            }
            .readme code {
                display: inline-block;
                margin: 0 5px;
                padding: 0 8px;
                line-height: 25px;
                font-size: 12px;
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                color: #1a1a1a;
                border-radius: 4px;
                background: #eee;
            }
            .readme code.copy {
                cursor: pointer;
            }
        </style>
        <div class="readme">
            <h3>插件配置说明</h3>
            <ul>
                <li>- 如果您没有appid和appkey，请前往<a href="https://connect.qq.com/" target="_blank">https://connect.qq.com/</a>申请</li>
                <li>- 您用于应用填写网站回调域的地址是<code class="copy" title="点击复制"><?php echo os_qqconnect_Event_GetURL('callback'); ?></code></li>
                <li>- 关于应用接口，目前仅需默认的<code>get_user_info</code></li>
                <li>- 获取最新的教程文档支持<a href="https://www.os369.com/app/item/os_qqconnect" target="_blank">https://www.os369.com/app/item/os_qqconnect</a></li>
            </ul>
        </div>
    </div>
</div>
<script src="<?php echo $zbp->host ?>zb_users/plugin/os_qqconnect/static/clipboard/clipboard-polyfill.js"></script>
<script>
$('.readme code.copy').on('click', function() {
    var str = $.trim($(this).html())
    clipboard.writeText(str)
    alert("已复制"+str)
})
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
