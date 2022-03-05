<?php
namespace Be\App\Cms\Task;

use Be\Be;
use Be\Task\Task;
use Be\Task\TaskException;

/**
 * 全量量同步到ES
 *
 * @BeTask("全量同步文章到ES")
 */
class SyncEs extends Task
{

    public function execute()
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
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
                $serviceArticle->syncEs($batch);
                $batch = [];
                $i = 0;
            }
        }

        if ($i > 0) {
            $serviceArticle->syncEs($batch);
        }
    }


}
