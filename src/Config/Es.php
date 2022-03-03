<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("搜索引擎")
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
    public string $article_index = 'article';

    /**
     * @BeConfigItem("文章索引的分片数",
     *     description="此配置仅在ES索引创建或重建时生效",
     *     driver="FormItemInputNumberInt",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public int $article_index_shards = 5;

    /**
     * @BeConfigItem("文章索引的副本数",
     *     description="此配置仅在ES索引创建或重建时生效", driver="FormItemInputNumberInt",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public int $article_index_replicas = 2;

    /**
     * @BeConfigItem("文章索引的分词器",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $article_index_analyzer = 'ik_max_word';


}

