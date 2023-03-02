<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("分类")
 */
class Category
{

    /**
     * @BeConfigItem("网址前缀", driver="FormItemInput", description="以 / 开头，以 / 结尾，谨慎改动。")
     */
    public string $urlPrefix = '/articles/';


}

