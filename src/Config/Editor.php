<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("编辑器")
 */
class Editor
{

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
        'height' => 600,
    ];


}

