<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("文章")
 */
class Article
{

    /**
     * @BeConfigItem("默认分页条数", driver="FormItemSwitch")
     */
    public int $pageSize = 5;

    /**
     * @BeConfigItem("是否允许评论", driver="FormItemSwitch")
     */
    public int $comment = 1;

    /**
     * @BeConfigItem("评论是否默认公开", description="不公开时，需要管理员审核", driver="FormItemSwitch")
     */
    public int $commentPublic = 1;

    /**
     * @BeConfigItem("TinyMCE 默认风格",
     *     description="用来控制 TinyMCE 启用的插件和功能",
     *     driver="FormItemSelect",
     *     keyValues = "return ['basic' => '基本', 'simple' => '简洁', 'full' => '所有功能'];")
     * )
     */
    public string $tinymce_layout = 'simple';

    /**
     * @BeConfigItem("TinyMCE 自定义配置项",
     *     description="可以添加或修改 TinyMCE 支持的所有配置项",
     *     driver="FormItemCode",
     *     language="json",,
     *     valueType = "mixed"
     * )
     */
    public array $tinymce_option = [
    ];


}

