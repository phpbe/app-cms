<?php
$db = \Be\Be::getDb();
$sql = file_get_contents(__DIR__ . '/uninstall.sql');
$sqls = preg_split('/; *[\r\n]+/', $sql);
foreach ($sqls as $sql) {
    $sql = trim($sql);
    if ($sql) {
        $db->query($sql);
    }
}

