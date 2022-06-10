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

    // 默认断点
    protected $breakpoint = '2022-05-01 00:00:00';

    // 时间间隔：1天
    protected $step = 86400;

    public function execute()
    {
        $configEs = Be::getConfig('App.Cms.Es');

        $t0 = time();
        $t1 = strtotime($this->breakpoint);
        $t2 = $t1 + $this->step;

        if ($t1 >= $t0) return;
        if ($t2 > $t0) {
            $t2 = $t0;
        }

        $d1 = date('Y-m-d H:i:s', $t1 - 60);
        $d2 = date('Y-m-d H:i:s', $t2);

        $service = Be::getService('App.Cms.Admin.TaskArticle');
        $db = Be::getDb();
        $sql = 'SELECT * FROM cms_article WHERE is_enable != -1 AND update_time >= ? AND update_time < ?';
        $blogs = $db->getYieldObjects($sql, [$d1, $d2]);

        $batch = [];
        $i = 0;
        foreach ($blogs as $blog) {
            $batch[] = $blog;

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
