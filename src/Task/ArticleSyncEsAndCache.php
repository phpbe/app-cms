<?php
namespace Be\App\Cms\Task;

use Be\Be;
use Be\Task\TaskInterval;

/**
 * 间隔一段时间晨，定时执行 文章同步到ES和Cache
 *
 * @BeTask("文章境量同步到ES和Cache", schedule="* * * * *")
 */
class ArticleSyncEsAndCache extends TaskInterval
{

    // 时间间隔：1天
    protected $step = 86400;

    public function execute()
    {

        if (!$this->breakpoint) {
            $this->breakpoint = date('Y-m-d h:i:s', time() - $this->step);
        }

        $t0 = time();
        $t1 = strtotime($this->breakpoint);
        $t2 = $t1 + $this->step;

        if ($t1 >= $t0) return;
        if ($t2 > $t0) {
            $t2 = $t0;
        }

        $d1 = date('Y-m-d H:i:s', $t1 - 60);
        $d2 = date('Y-m-d H:i:s', $t2);

        $configEs = Be::getConfig('App.Cms.Es');
        $service = Be::getService('App.Cms.Admin.TaskArticle');
        $db = Be::getDb();
        $sql = 'SELECT * FROM cms_article WHERE is_enable != -1 AND update_time >= ? AND update_time <= ?';
        $objs = $db->getYieldObjects($sql, [$d1, $d2]);

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
        $sql = 'SELECT * FROM cms_article_comment WHERE is_enable != -1 AND update_time >= ? AND update_time < ?';
        $objs = $db->getYieldObjects($sql, [$d1, $d2]);

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


        $this->breakpoint = $d2;
    }


}
