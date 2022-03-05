<?php
namespace Be\App\Cms\Task;

use Be\Be;
use Be\Task\Task;

/**
 * @BeTask("全量同步文章，文章分类，自定义页面到Redis")
 */
class SyncRedis extends Task
{


    public function execute()
    {
        $configRedis = Be::getConfig('App.Cms.Redis');
        if (!$configRedis->enable) {
            return;
        }

        $serviceArticle = Be::getService('App.Cms.Admin.Article');
        $db = Be::newDb();
        $sql = 'SELECT id FROM cms_article';
        $articleIds = $db->getYieldValues($sql);
        $batch = [];
        $i = 0;
        foreach ($articleIds as $articleId) {
            $batch[] = $articleId;

            $i++;
            if ($i >= 500) {
                $serviceArticle->syncRedis($batch);
                $batch = [];
                $i = 0;
            }
        }

        if ($i > 0) {
            $serviceArticle->syncRedis($batch);
        }


        $servicePage = Be::getService('App.Cms.Admin.Page');
        $db = Be::newDb();
        $sql = 'SELECT id FROM cms_page';
        $pageIds = $db->getYieldValues($sql);
        $batch = [];
        $i = 0;
        foreach ($pageIds as $pageId) {
            $batch[] = $pageId;

            $i++;
            if ($i >= 500) {
                $servicePage->syncRedis($batch);
                $batch = [];
                $i = 0;
            }
        }

        if ($i > 0) {
            $servicePage->syncRedis($batch);
        }

        $serviceCategory = Be::getService('App.Cms.Admin.Category');
        $db = Be::newDb();
        $sql = 'SELECT id FROM cms_category';
        $pageIds = $db->getYieldValues($sql);
        $batch = [];
        $i = 0;
        foreach ($pageIds as $pageId) {
            $batch[] = $pageId;

            $i++;
            if ($i >= 500) {
                $serviceCategory->syncRedis($batch);
                $batch = [];
                $i = 0;
            }
        }

        if ($i > 0) {
            $serviceCategory->syncRedis($batch);
        }
    }

}
