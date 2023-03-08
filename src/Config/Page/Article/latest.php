<?php

namespace Be\App\Cms\Config\Page\Article;

/**
 * @BeConfig("最新文章")
 */
class latest
{

    public int $west = 0;
    public int $center = 75;
    public int $east = 25;

    public array $centerSections = [
        [
            'name' => 'App.Cms.Latest',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.SearchForm',
        ],
        [
            'name' => 'App.Cms.SideHottest',
        ],
        [
            'name' => 'App.Cms.SideTopSearch',
        ],
        [
            'name' => 'App.Cms.SideGuessYouLike',
        ],
        [
            'name' => 'App.Cms.SideTopTags',
        ],
    ];

    /**
     * @BeConfigItem("HEAD头标题",
     *     description="HEAD头标题，用于SEO",
     *     driver = "FormItemInput"
     * )
     */
    public string $title = '最新文章';

    /**
     * @BeConfigItem("Meta描述",
     *     description="填写页面内容的简单描述，用于SEO",
     *     driver = "FormItemInputTextArea"
     * )
     */
    public string $metaDescription = '最新文章';

    /**
     * @BeConfigItem("Meta关键词",
     *     description="填写页面内容的关键词，用于SEO",
     *     driver = "FormItemInput"
     * )
     */
    public string $metaKeywords = '最新文章';

    /**
     * @BeConfigItem("页面标题",
     *     description="展示在页面内容中的标题，一般与HEAD头标题一致，两者相同时可不填写此项",
     *     driver = "FormItemInput"
     * )
     */
    public string $pageTitle = '';

}
