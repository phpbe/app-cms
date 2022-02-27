<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("通用")
 */
class Cms
{

    /**
     * @BeConfigItem("数据库",
     *     driver="FormItemSelect",
     *     keyValues = "return \Be\Db\DbHelper::getConfigKeyValues();"
     * )
     */
    public string $db = 'master';

}

