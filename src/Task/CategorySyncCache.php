<?php
namespace Be\App\Cms\Task;

use Be\Be;
use Be\Task\TaskInterval;

/**
 * @BeTask("文章分类增量同步到Cache", schedule="* * * * *")
 */
class CategorySyncCache extends TaskInterval
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

        $service = Be::getService('App.Cms.Admin.TaskCategory');
        $db = Be::getDb();
        $sql = 'SELECT * FROM cms_category WHERE update_time >= ? AND update_time < ?';
        $categories = $db->getYieldObjects($sql, [$d1, $d2]);

        $batch = [];
        $i = 0;
        foreach ($categories as $category) {
            $batch[] = $category;

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
