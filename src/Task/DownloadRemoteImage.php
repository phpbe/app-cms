<?php
namespace Be\App\Cms\Task;

use Be\Be;
use Be\Task\Task;

/**
 * @BeTask("自动下载远程图片", timeout="1800", schedule="40 * * * *")
 */
class DownloadRemoteImage extends Task
{

    protected $parallel = false;

    public function execute()
    {
        $timeout = $this->timeout;
        if ($timeout <= 0) {
            $timeout = 60;
        }

        $service = Be::getService('App.Cms.Admin.TaskArticle');
        $t0 = time();
        do {
            $sql = 'SELECT * FROM cms_article WHERE download_remote_image = 1';
            $article = Be::getDb()->getObject($sql);
            if (!$article) {
                break;
            }

            $service->downloadRemoteImages($article);

            $this->taskLog->update_time = date('Y-m-d H:i:s');
            $this->updateTaskLog();

            $t1 = time();
        } while($t1 - $t0 < $timeout);
    }



}
