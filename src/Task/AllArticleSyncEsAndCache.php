<?php
namespace Be\App\Cms\Task;

use Be\Be;
use Be\Task\Task;

/**
 * 文章全量量同步到ES和Cache
 *
 * @BeTask("文章全量量同步到ES和Cache")
 */
class AllArticleSyncEsAndCache extends Task
{

    public function execute()
    {
        $configEs = Be::getConfig('App.Cms.Es');

        $service = Be::getService('App.Cms.Admin.TaskArticle');

        $db = Be::getDb();
        $sql = 'SELECT * FROM cms_article WHERE is_enable != -1';
        $objs = $db->getYieldObjects($sql);

        $batch = [];
        $i = 0;
        foreach ($objs as $obj) {
            $batch[] = $obj;

            $i++;
            if ($i >= 100) {
                if ($configEs->enable) {
                    $service->syncEs($batch);
                }

                $service->syncCache($batch);

                $batch = [];
                $i = 0;
            }
        }

        if ($i > 0) {
            if ($configEs->enable) {
                $service->syncEs($batch);
            }

            $service->syncCache($batch);
        }


        $service = Be::getService('App.Cms.Admin.TaskArticleComment');

        $db = Be::getDb();
        $sql = 'SELECT * FROM cms_article_comment WHERE is_enable != -1';
        $objs = $db->getYieldObjects($sql);

        $batch = [];
        $i = 0;
        foreach ($objs as $obj) {
            $batch[] = $obj;

            $i++;
            if ($i >= 100) {
                if ($configEs->enable) {
                    $service->syncEs($batch);
                }

                $service->syncCache($batch);

                $batch = [];
                $i = 0;
            }
        }

        if ($i > 0) {
            if ($configEs->enable) {
                $service->syncEs($batch);
            }

            $service->syncCache($batch);
        }

    }


}
