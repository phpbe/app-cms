<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class Es
{

    /**
     * 安装
     * @return void
     */
    public function install()
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configEs->enable) {
            $es = Be::getEs();
            $params = [
                'index' => $configEs->article_index,
            ];

            if ($es->indices()->exists($params)) {
                throw new ServiceException('索引（' . $configEs->article_index . '）已存在');
            }

            $params = [
                'index' => $configEs->article_index,
                'body' => [
                    'settings' => [
                        'number_of_shards' => $configEs->article_index_shards,
                        'number_of_replicas' => $configEs->article_index_replicas
                    ],
                    'mappings' => [
                        '_source' => [
                            'enabled' => true
                        ],
                        'properties' => [
                            'image' => [
                                'type' => 'keyword',
                            ],
                            'title' => [
                                'type' => 'text',
                                'analyzer' => $configEs->article_index_analyzer,
                            ],
                            'summary' => [
                                'type' => 'text',
                                'analyzer' => $configEs->article_index_analyzer,
                            ],
                            'description' => [
                                'type' => 'text',
                                'analyzer' => $configEs->article_index_analyzer,
                            ],
                            'url' => [
                                'type' => 'keyword',
                            ],
                            'author' => [
                                'type' => 'keyword',
                            ],
                            'seo' => [
                                'type' => 'integer'
                            ],
                            'seo_title' => [
                                'type' => 'keyword'
                            ],
                            'seo_description' => [
                                'type' => 'keyword'
                            ],
                            'seo_keywords' => [
                                'type' => 'keyword'
                            ],
                            'ordering' => [
                                'type' => 'integer'
                            ],
                            'is_enable' => [
                                'type' => 'integer'
                            ],
                            'is_delete' => [
                                'type' => 'integer'
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
                    ]
                ]
            ];

            $es->indices()->create($params);
        }
    }

    /**
     *
     * @return void
     */
    public function uninstall()
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configEs->enable) {
            $es = Be::getEs();
            $params = [
                'index' => $configEs->article_index,
            ];
            if ($es->indices()->exists($params)) {
                $es->indices()->delete($params);
            }
        }
    }


}
