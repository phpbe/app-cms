<?php

namespace Be\App\Cms\Controller\Admin;

use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("控制台")
 * @BePermissionGroup("控制台")
 */
class Config extends Auth
{

    /**
     * @BeMenu("参数", icon="el-icon-setting", ordering="3.3")
     * @BePermission("参数", ordering="3.3")
     */
    public function dashboard()
    {
        Be::getAdminPlugin('Config')->setting(['appName' => 'Cms', 'title' => '参数'])->execute();
    }


}