<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("自定义页面")
 */
class Page
{

    /**
     * @BeConfigItem("网址前缀", driver="FormItemInput", description="以 / 开头，以 / 结尾，谨慎改动。")
     */
    public string $pageUrlPrefix = '/page/';

}

