<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("自定义页面")
 */
class Page
{

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
     * @BeConfigItem("页面文字大小",
     *     driver="FormItemSelect",
     *     keyValues = "return ['100' => '1rem', '110' => '1.1rem', '120' => '1.2rem', '125' => '1.25rem', '150' => '1.5rem', '175' => '1.75rem', '200' => '2rem'];")
     * )
     */
    public string $pageFontSize = '110';

    /**
     * @BeConfigItem("页面行高",
     *     driver="FormItemSelect",
     *     keyValues = "return ['150' => '1.5rem', '175' => '1.75rem', '200' => '2rem', '250' => '2.5rem', '300' => '3rem', '400' => '4rem'];")
     * )
     */
    public string $pageLineHeight = '200';


}

