<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class Es
{

    public function getIndexes()
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return false;
        }

        $indexes = [];

        $es = Be::getEs();
        foreach ([
                     [
                         'name' => 'article',
                         'label' => '文章索引',
                         'value' => $configEs->indexArticle,
                     ],
                     [
                         'name' => 'articleSearchHistory',
                         'label' => '文章搜索记录索引',
                         'value' => $configEs->indexArticleSearchHistory,
                     ],
                     [
                         'name' => 'articleComment',
                         'label' => '文章评论',
                         'value' => $configEs->indexArticleComment,
                     ]
                 ] as $index) {
            $params = [
                'index' => $index['value'],
            ];
            if ($es->indices()->exists($params)) {
                $index['exists'] = true;

                $mapping = $es->indices()->getMapping($params);
                $index['mapping'] = $mapping[$index['value']]['mappings'] ?? [];

                $settings = $es->indices()->getSettings($params);
                $index['settings'] = $settings[$index['value']]['settings'] ?? [];

                $count = $es->count($params);
                $index['count'] = $count['count'] ?? 0;
            } else {
                $index['exists'] = false;
            }
            $indexes[] = $index;
        }

        return $indexes;
    }

    /**
     * 创建索引
     *
     * @param string $indexName 索引名
     * @param array $options 参数
     * @return void
     */
    public function createIndex(string $indexName, array $options = [])
    {
        $number_of_shards = $options['number_of_shards'] ?? 2;
        $number_of_replicas = $options['number_of_replicas'] ?? 1;

        $configEs = Be::getConfig('App.Cms.Es');
        if ($configEs->enable) {
            $es = Be::getEs();

            $configField = 'index' . ucfirst($indexName);

            $params = [
                'index' => $configEs->$configField,
            ];

            if ($es->indices()->exists($params)) {
                throw new ServiceException('索引（' . $configEs->$configField . '）已存在');
            }

            switch ($indexName) {
                case 'article':
                    $mapping = [
                        'properties' => [
                            'id' => [
                                'type' => 'keyword',
                            ],
                            'image' => [
                                'type' => 'keyword',
                            ],
                            'title' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                            ],
                            'summary' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                            ],
                            'description' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                            ],
                            'url' => [
                                'type' => 'keyword',
                            ],
                            'author' => [
                                'type' => 'keyword',
                            ],
                            'hits' => [
                                'type' => 'integer'
                            ],
                            'publish_time' => [
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd HH:mm:ss',
                            ],
                            'is_push_home' => [
                                'type' => 'boolean'
                            ],
                            'is_on_top' => [
                                'type' => 'boolean'
                            ],
                            /*
                            'is_enable' => [
                                'type' => 'boolean'
                            ],
                            'is_delete' => [
                                'type' => 'boolean'
                            ],
                            'create_time' => [
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd HH:mm:ss',
                            ],
                            'update_time' => [
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd HH:mm:ss',
                            ],
                            */
                            'categories' => [
                                'type' => 'nested',
                                'properties' => [
                                    'id' => [
                                        'type' => 'keyword'
                                    ],
                                    'name' => [
                                        'type' => 'keyword'
                                    ],
                                ],
                            ],
                            'tags' => [
                                'type' => 'keyword'
                            ],
                        ]
                    ];
                    break;
                case 'articleSearchHistory':
                    $mapping = [
                        'properties' => [
                            'keyword' => [
                                'type' => 'keyword',
                            ],
                        ]
                    ];
                    break;
                case 'articleComment':
                    $mapping = [
                        'properties' => [
                            'id' => [
                                'type' => 'keyword',
                            ],
                            'article_id' => [
                                'type' => 'keyword',
                            ],
                            'name' => [
                                'type' => 'keyword',
                            ],
                            'email' => [
                                'type' => 'keyword',
                            ],
                            'content' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                            ],
                            'is_enable' => [
                                'type' => 'boolean'
                            ],
                            'is_delete' => [
                                'type' => 'boolean'
                            ],
                            'create_time' => [
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd HH:mm:ss',
                            ],
                            'update_time' => [
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd HH:mm:ss',
                            ],
                        ]
                    ];

                    break;
            }

            $params = [
                'index' => $configEs->$configField,
                'body' => [
                    'settings' => [
                        'number_of_shards' => $number_of_shards,
                        'number_of_replicas' => $number_of_replicas
                    ],
                    'mappings' => $mapping,
                ]
            ];

            $es->indices()->create($params);
        }
    }

    /**
     * 删除索引
     *
     * @param string $indexName 索引名
     * @return void
     */
    public function deleteIndex(string $indexName)
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configEs->enable) {
            $es = Be::getEs();

            $configField = 'index' . ucfirst($indexName);

            $params = [
                'index' => $configEs->$configField,
            ];

            if ($es->indices()->exists($params)) {
                $es->indices()->delete($params);
            }
        }
    }

}
