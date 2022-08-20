<?php
namespace Be\App\Cms\Controller;

use Be\App\ControllerException;
use Be\Be;

/**
 * 自定义页面
 */
class Page
{

    /**
     * 详情
     *
     * @BeMenu("自定义页面", picker="return \Be\Be::getService('App.Cms.Admin.Page')->getPageMenuPicker()")
     * @BeRoute("\Be\Be::getService('App.Cms.Page')->getPageUrl($params)")
     */
    public function detail()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $pageId = $request->get('id', '');
            if (!$pageId) {
                throw new ControllerException('页面不存在！');
            }

            $servicePage = Be::getService('App.Cms.Page');
            $page = $servicePage->getPage($pageId);
            $response->set('title', $page->seo_title);
            $response->set('metaKeywords', $page->seo_keywords);
            $response->set('metaDescription', $page->seo_description);
            $response->set('pageTitle', $page->title);

            $response->set('page', $page);

            $configPage = Be::getConfig('App.Cms.Page');
            $response->set('configPage', $configPage);

            $response->display();
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }

}
