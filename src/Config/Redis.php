<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("Redis缓存")
 */
class Redis
{

    /**
     * @BeConfigItem("是否启用Redis缓存", description="启用后，相关内容变更时将同步到Redis缓存", driver="FormItemSwitch")
     */
    public int $enable = 0;

    /**
     * @BeConfigItem("REDIS库",
     *     driver="FormItemSelect",
     *     keyValues = "return \Be\Redis\RedisHelper::getConfigKeyValues();",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];")
     */
    public $db = 'master';

}

