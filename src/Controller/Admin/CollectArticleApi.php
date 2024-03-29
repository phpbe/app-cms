<?php

namespace Be\App\Cms\Controller\Admin;

use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("采集")
 * @BePermissionGroup("采集")
 */
class CollectArticleApi extends Auth
{

    /**
     * 采集接口
     *
     * @BeMenu("接收器", icon="bi-bounding-box", ordering="2.3")
     * @BePermission("接收器", ordering="2.3")
     */
    public function config()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $config = Be::getService('App.Cms.Admin.CollectArticleApi')->getConfig();
        $response->set('config', $config);
        $response->set('title', '接收器');
        $response->display();
    }

    /**
     * 采集接口 切换启用状态
     *
     * @BePermission("接收器", ordering="2.3")
     */
    public function toggleEnable()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $enable = Be::getService('App.Cms.Admin.CollectArticleApi')->toggleEnable();
            $response->set('success', true);
            $response->set('message', '接口开关' . ($enable ? '启用' : '停用') . '成功！');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

    /**
     * 采集接口 重设Token
     *
     * @BePermission("接收器", ordering="2.3")
     */
    public function resetToken()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            Be::getService('App.Cms.Admin.CollectArticleApi')->resetToken();
            $response->redirect(beAdminUrl('Cms.CollectArticleApi.config'));
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }


}
