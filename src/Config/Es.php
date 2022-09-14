<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("ES搜索引擎")
 */
class Es
{

    /**
     * @BeConfigItem("是否启用ES搜索引擎",
     *     description="启用后，文音变更将同步到ES搜索引擎，检索相关的功能将由ES接管",
     *     driver="FormItemSwitch"
     * )
     */
    public int $enable = 0;

    /**
     * @BeConfigItem("存储文章的索引名",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $indexArticle = 'cms.article';

    /**
     * @BeConfigItem("文章搜索记录索引",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $indexArticleSearchHistory = 'cms.article_search_history';

    /**
     * @BeConfigItem("存储文章评论的索引名",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $indexArticleComment = 'cms.article_comment';

}

