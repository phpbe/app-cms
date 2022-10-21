<?php

namespace Be\App\Cms\Controller;

use Be\Be;

/**
 * 首页
 */
class Home
{

    /**
     * 首页
     *
     * @BeMenu("首页")
     * @BeRoute("/cms/home")
     */
    public function index()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $response->set('title', $pageConfig->title ?: '');
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $pageConfig->pageTitle ?: ($pageConfig->title ?: ''));

        $page = $request->get('page', 1);
        $result = Be::getService('App.Cms.Article')->search('', [
            'isPushHome' => 1,
            'orderBy' => ['is_on_top', 'publish_time'],
            'orderByDir' => ['desc', 'desc'],
            'page' => $page,
        ]);
        $response->set('result', $result);

        $paginationUrl = beUrl('Cms.Home.index');
        $response->set('paginationUrl', $paginationUrl);

        $response->display('App.Cms.Article.articles');
    }



}
