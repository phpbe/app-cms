<?php
namespace Be\App\Cms;

use Be\Be;

/**
 * 应用卸载器
 */
class UnInstaller extends \Be\App\UnInstaller
{

    /**
     * 卸载时需要执行的操作，如删除数据库表
     */
	public function uninstall()
	{
        $db = Be::getDb();

        $sql = file_get_contents(__DIR__ . '/UnInstaller.sql');
        $sqls = preg_split('/; *[\r\n]+/', $sql);
        foreach ($sqls as $sql) {
            $sql = trim($sql);
            if ($sql) {
                $db->query($sql);
            }
        }
	}

}
