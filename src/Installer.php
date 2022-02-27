<?php
namespace Be\App\Cms;

use Be\Runtime\RuntimeException;
use Be\Be;

/**
 * 应用安装器
 */
class Installer extends \Be\App\Installer
{

    /**
     * 安装时需要执行的操作，如创建数据库表
     */
	public function install()
	{
        $db = Be::getDb();
        $tableNames = $db->getTableNames();
        if (in_array('cms_', $tableNames)) {
            if (in_array('cms_', $tableNames)) {
                return;
            } else {
                throw new RuntimeException('剑测到部分数据表已存在，请检查数据库！');
            }
        }

        $sql = file_get_contents(__DIR__ . '/Installer.sql');
        $sqls = preg_split('/; *[\r\n]+/', $sql);
        foreach ($sqls as $sql) {
            $sql = trim($sql);
            if ($sql) {
                $db->query($sql);
            }
        }
	}

}
