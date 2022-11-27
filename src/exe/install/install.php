<?php
$installed = false;

$db = \Be\Be::getDb();
$tableNames = $db->getTableNames();
if (in_array('cms_article', $tableNames)) {
    if (in_array('cms_page', $tableNames)) {
        $installed = true;
    } else {
        throw new \Be\Runtime\RuntimeException('剑测到部分数据表已存在，请检查数据库！');
    }
}

if (!$installed) {
    $sql = file_get_contents(__DIR__ . '/install.sql');
    $sqls = preg_split('/; *[\r\n]+/', $sql);
    foreach ($sqls as $sql) {
        $sql = trim($sql);
        if ($sql) {
            $db->query($sql);
        }
    }
}


