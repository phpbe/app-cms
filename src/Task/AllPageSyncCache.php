<?php
namespace Be\App\Cms\Task;

use Be\Be;
use Be\Task\Task;

/**
 * @BeTask("自定义页面全量同步到Cache")
 */
class AllPageSyncCache extends Task
{


    public function execute()
    {
        $service = Be::getService('App.Cms.Admin.TaskPage');

        $db = Be::newDb();
        $sql = 'SELECT * FROM cms_page';
        $pages = $db->getYieldObjects($sql);

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

    }

}
