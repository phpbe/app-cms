<?php
namespace Be\App\Cms\Controller\Admin;


use Be\Be;

/**
 * @BeMenuGroup("采集")
 * @BePermissionGroup("采集")
 */
class CollectLocoy
{

    /**
     * 火车采集器接口
     *
     * @BeMenu("火车采集器接口", icon="el-icon-fa fa-train", ordering="2.3")
     * @BePermission("火车采集器接口", ordering="2.3")
     */
    public function detail()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $configLogoy = Be::getConfig('App.Cms.Locoy');
        $response->set('configLogoy', $configLogoy);
        $response->set('title', '火车采集器接口');
        $response->display();
    }

}
