<?php

namespace Be\App\Cms\Service\Admin;

use Be\Be;
use Be\Config\ConfigHelper;
use Be\Util\Crypt\Random;

class CollectLocoy
{

    public function getConfig()
    {
        $config = Be::getConfig('App.Cms.Locoy');
        if ($config->token === '') {
            $config->token = Random::simple(32);
            ConfigHelper::update('App.Cms.Locoy', $config);
            if (Be::getRuntime()->isSwooleMode()) {
                Be::getRuntime()->reload();
            }
        }
        return $config;
    }

    /**
     * 火车采集器配置 - 切换启用状态
     *
     * @return int
     */
    public function toggleEnable(): int
    {
        $config = Be::getConfig('App.Cms.Locoy');
        $config->enable = (int)(!$config->enable);
        ConfigHelper::update('App.Cms.Locoy', $config);
        if (Be::getRuntime()->isSwooleMode()) {
            Be::getRuntime()->reload();
        }
        return $config->enable;
    }

    /**
     * 火车采集器配置 - 重置 Token
     *
     * @return string
     */
    public function resetToken(): string
    {
        $config = Be::getConfig('App.Cms.Locoy');
        $config->token = Random::simple(32);
        ConfigHelper::update('App.Cms.Locoy', $config);

        if (Be::getRuntime()->isSwooleMode()) {
            Be::getRuntime()->reload();
        }

        return $config->token;
    }

}
