<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("文章")
 */
class Article
{

    /**
     * @BeConfigItem("默认分页条数", driver="FormItemInputNumberInt")
     */
    public int $pageSize = 15;

    /**
     * @BeConfigItem("是否允许评论", driver="FormItemSwitch")
     */
    public int $comment = 1;

    /**
     * @BeConfigItem("评论是否默认公开", description="不公开时，需要管理员审核", driver="FormItemSwitch")
     */
    public int $commentPublic = 1;

    /**
     * @BeConfigItem("评论列表查询缓存时间（秒）", driver="FormItemInputNumberInt")
     */
    public int $commentsCacheExpire = 600;

    /**
     * @BeConfigItem("网址前缀", driver="FormItemInput", description="以 / 开头，谨慎改动。")
     */
    public string $urlPrefix = '/article/';

    /**
     * @BeConfigItem("TinyMCE 默认风格",
     *     description="用来控制 TinyMCE 启用的插件和功能",
     *     driver="FormItemSelect",
     *     keyValues = "return ['basic' => '基本', 'simple' => '简洁', 'full' => '所有功能'];")
     * )
     */
    public string $tinymceLayout = 'simple';

    /**
     * @BeConfigItem("TinyMCE 自定义配置项",
     *     description="可以添加或修改 TinyMCE 支持的所有配置项",
     *     driver="FormItemCode",
     *     language="json",,
     *     valueType = "mixed"
     * )
     */
    public array $tinymceOption = [
    ];

    /**
     * @BeConfigItem("记录最近访问的文章数（个）",
     *     description="用于 猜你喜欢 等",
     *     driver="FormItemSlider",
     *     ui="return [':min' => 1, ':max' => 10];")
     * )
     */
    public int $viewHistory = 10;

    /**
     * @BeConfigItem("记录最近搜索（次）",
     *     description="用于 执门搜索 等",
     *     driver="FormItemInputNumberInt",
     *     ui="return [':min' => 1];")
     * )
     */
    public int $searchHistory = 1000;

}

