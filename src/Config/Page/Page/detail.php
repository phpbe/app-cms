<?php

namespace Be\App\Cms\Config\Page\Page;


/**
 * @BeConfig("自定义页面详情")
 */
class detail
{
    public int $middle = 1;
    public array $middleSections = [
        [
            'name' => 'Theme.System.PageTitle',
        ],
        [
            'name' => 'Theme.System.PageContent',
        ],
    ];

    /**
     * @BeConfigItem("HEAD头标题",
     *     description="HEAD头标题，用于SEO",
     *     driver = "FormItemInput"
     * )
     */
    public string $title = '新建页面';

    /**
     * @BeConfigItem("Meta描述",
     *     description="填写页面内容的简单描述，用于SEO",
     *     driver = "FormItemInputTextArea"
     * )
     */
    public string $metaDescription = '';

    /**
     * @BeConfigItem("Meta关键词",
     *     description="填写页面内容的关键词，用于SEO",
     *     driver = "FormItemInput"
     * )
     */
    public string $metaKeywords = '';

    /**
     * @BeConfigItem("页面标题",
     *     description="展示在页面内容中的标题，一般与HEAD头标题一致，两者相同时可不填写此项",
     *     driver = "FormItemInput"
     * )
     */
    public string $pageTitle = '';

    /**
     * @BeConfigItem("网址",
     *     description="配置此页面的访问网址，将生成形如 https://www.domain.com/page/[自定义页面网址] 的实际网址",
     *     driver = "FormItemInput"
     * )
     */
    public string $url = '';

}
