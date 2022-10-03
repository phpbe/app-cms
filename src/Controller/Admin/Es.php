<?php

namespace Be\App\Cms\Controller\Admin;

use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("控制台", icon="el-icon-monitor", ordering="4")
 * @BePermissionGroup("控制台", icon="el-icon-monitor", ordering="4")
 */
class Es extends Auth
{

    /**
     * @BeMenu("ES搜索引擎", icon="el-icon-search", ordering="4.1")
     * @BePermission("ES搜索引擎 - 查看", ordering="4.1")
     */
    public function dashboard()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $configEs = Be::getConfig('App.Cms.Es');
        $response->set('configEs', $configEs);

        $indexes = Be::getService('App.Cms.Admin.Es')->getIndexes();
        $response->set('indexes', $indexes);

        $response->set('title', 'ES搜索引擎');
        $response->display();
    }

    /**
     * 创建索引
     *
     * @BePermission("ES搜索引擎 - 创建索引", ordering="4.11")
     */
    public function createIndex()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $formData = $request->json('formData');
        $indexName = $formData['name'] ?? '';
        try {
            Be::getService('App.Cms.Admin.Es')->createIndex($indexName, $formData);
            $response->success('创建成功！');
        } catch (\Throwable $t) {
            $response->error('创建失败：' . $t->getMessage());
        }
    }

    /**
     * 删除索引
     *
     * @BePermission("ES搜索引擎 - 删除索引", ordering="4.12")
     */
    public function deleteIndex()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $formData = $request->json('formData');
        $indexName = $formData['name'] ?? '';
        try {
            Be::getService('App.Cms.Admin.Es')->deleteIndex($indexName);
            $response->success('删除成功！');
        } catch (\Throwable $t) {
            $response->error('删除失败：' . $t->getMessage());
        }
    }

}