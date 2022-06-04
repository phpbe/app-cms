<?php
namespace Be\App\Cms\Task;

use Be\Be;
use Be\Task\TaskInterval;

/**
 * @BeTask("自定义页面增量同步到Cache", schedule="* * * * *")
 */
class PageSyncCache extends TaskInterval
{

    // 默认断点
    protected $breakpoint = '2022-05-01 00:00:00';

    // 时间间隔：1天
    protected $step = 86400;

    public function execute()
    {
        $t0 = time();
        $t1 = strtotime($this->breakpoint);
        $t2 = $t1 + $this->step;

        if ($t1 >= $t0) return;
        if ($t2 > $t0) {
            $t2 = $t0;
        }

        $d1 = date('Y-m-d H:i:s', $t1 - 60);
        $d2 = date('Y-m-d H:i:s', $t2);

        $service = Be::newService('App.Cms.Admin.TaskPage');

        $db = Be::newDb();
        $sql = 'SELECT * FROM cms_page WHERE update_time >= ? AND update_time < ?';
        $pages = $db->getYieldObjects($sql, [$d1, $d2]);

        $batch = [];
        $i = 0;
        foreach ($pages as $page) {
            $batch[] = $page;

            $i++;
            if ($i >= 100) {
                $service->syncCache($batch);

                $batch = [];
                $i = 0;
            }
        }

        if ($i > 0) {
            $service->syncCache($batch);
        }

        $this->breakpoint = $d2;
    }


}
