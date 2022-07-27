<?php

namespace Be\App\Cms\Service;

use Be\App\ServiceException;
use Be\Be;

class Article
{

    /**
     * 获取文章
     *
     * @param string $articleId 文章ID
     * @return object 文章对象
     * @throws ServiceException
     */
    public function getArticle(string $articleId): object
    {
        $cache = Be::getCache();

        $key = 'Cms:Article:' . $articleId;
        $article = $cache->get($key);
        if (!$article) {
            throw new ServiceException('文章不存在！');
        }

        return $article;
    }

    /**
     * 获取文章
     *
     * @param string $articleId 文章ID
     * @return object 文章对象
     * @throws ServiceException
     */
    public function getArticleFromDb(string $articleId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM `cms_article` WHERE id=?';
        $article = $db->getObject($sql, [$articleId]);
        if (!$article) {
            throw new ServiceException('文章不存在！');
        }

        $article->url_custom = (int)$article->url_custom;
        $article->seo_title_custom = (int)$article->seo_title_custom;
        $article->seo_description_custom = (int)$article->seo_description_custom;
        $article->ordering = (int)$article->ordering;
        $article->is_push_home = (int)$article->is_push_home;
        $article->is_on_top = (int)$article->is_on_top;
        $article->is_enable = (int)$article->is_enable;
        $article->is_delete = (int)$article->is_delete;

        $sql = 'SELECT category_id FROM cms_article_category WHERE article_id = ?';
        $category_ids = $db->getValues($sql, [$articleId]);
        if (count($category_ids) > 0) {
            $article->category_ids = $category_ids;

            $sql = 'SELECT * FROM cms_category WHERE id IN (?)';
            $categories = $db->getObjects($sql, ['\'' . implode('\',\'', $category_ids) . '\'']);
            foreach ($categories as $category) {
                $category->ordering = (int)$category->ordering;
            }
            $article->categories = $categories;
        } else {
            $article->category_ids = [];
            $article->categories = [];
        }

        $sql = 'SELECT tag FROM cms_article_tag WHERE article_id = ?';
        $article->tags = $db->getValues($sql, [$articleId]);

        return $article;
    }

    /**
     * 查看文章并更新点击
     *
     * @param string $articleId 文章ID
     * @return object
     */
    public function hit(string $articleId): object
    {
        $my = Be::getUser();
        $cache = Be::getCache();

        $configArticle = Be::getConfig('App.Cms.Article');

        $article = $this->getArticle($articleId);

        $historyKey = 'Cms:Article:History:' . $my->id;
        $history = $cache->get($historyKey);

        if (!$history || !is_array($history)) {
            $history = [];
        }

        $history[] = $article->title;

        $viewHistory = $configArticle->viewHistory > 0 ? $configArticle->viewHistory : 20;
        if (count($history) > $viewHistory) {
            $history = array_slice($history, -$viewHistory);
        }

        // 最近浏览的文章标题存入缓存，有效期 30 天
        $cache->set($historyKey, $history, 86400 * 30);

        // 点击量 使用缓存 存放
        $hits = (int)$article->hits;
        $hitsKey = 'Cms:Article:hits:' . $articleId;
        $cacheHits = $cache->get($hitsKey);
        if ($cacheHits !== false) {
            if (is_numeric($cacheHits)) {
                $cacheHits = (int)$cacheHits;
                if ($cacheHits > $article->hits) {
                    $hits = $cacheHits;
                }
            }
        }

        $hits++;

        $cache->set($hitsKey, $hits);

        // 每 100 次访问，更新到数据库
        if ($hits % 100 === 0) {
            $sql = 'UPDATE cms_article SET hits=?, update_time=? WHERE id=?';
            Be::getDb()->query($sql, [$hits, date('Y-m-d H:i:s'), $articleId]);
        }

        $article->hits = $hits;

        return $article;
    }

    /**
     * 按关銉词搜索
     *
     * @param string $keywords 关銉词
     * @param array $params
     * @return array
     */
    public function search(string $keywords, array $params = []): array
    {
        $configArticle = Be::getConfig('App.Cms.Article');
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return $this->searchFromDb($keywords, $params);
        }

        $cache = Be::getCache();
        $es = Be::getEs();

        $keywords = trim($keywords);
        if ($keywords !== '') {
            // 将本用户搜索的关键词写入ES search_history
            $counterKey = 'Cms:Article:SearchHistory';
            $counter = (int)$cache->get($counterKey);
            $query = [
                'index' => $configEs->indexArticleSearchHistory,
                'id' => $counter,
                'body' => [
                    'keyword' => $keywords,
                ]
            ];
            $es->index($query);

            // 累计写入1千个
            $counter++;
            if ($counter >= $configArticle->searchHistory) {
                $counter = 0;
            }

            $cache->set($counterKey, $counter);
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'min_score' => 0.01,
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'term' => [
                                    'is_enable' => true,
                                ],
                            ],
                            [
                                'term' => [
                                    'is_delete' => false,
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        ];

        if ($keywords === '') {
            $query['body']['min_score'] = 0;
        } else {
            $query['body']['query']['bool']['should'] = [
                [
                    'match' => [
                        'title' => $keywords
                    ],
                ],
            ];
        }

        if (isset($params['isPushHome']) && in_array($params['isPushHome'], [0, 1])) {
            $query['body']['query']['bool']['filter'][] = [
                'term' => [
                    'is_push_home' => (bool)$params['isPushHome'],
                ]
            ];
        }

        if (isset($params['categoryId']) && $params['categoryId']) {
            $query['body']['query']['bool']['filter'][] = [
                'nested' => [
                    'path' => 'categories',
                    'query' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'categories.id' => $params['categoryId'],
                                    ],
                                ],
                            ]
                        ],
                    ],
                ]
            ];
        }

        if (isset($params['orderBy'])) {
            if (is_array($params['orderBy'])) {
                $len1 = count($params['orderBy']);
                if ($len1 > 0 && is_array($params['orderByDir'])) {
                    $len2 = count($params['orderByDir']);
                    if ($len1 === $len2) {
                        $query['body']['sort'] = [];
                        for ($i = 0; $i< $len1; $i++) {
                            $orderByDir = 'desc';
                            if (in_array($params['orderByDir'][$i], ['asc', 'desc'])) {
                                $orderByDir = $params['orderByDir'][$i];
                            }
                            $query['body']['sort'][] = [
                                $params['orderBy'][$i] => [
                                    'order' => $orderByDir
                                ]
                            ];
                        }
                    }
                }
            } elseif (is_string($params['orderBy']) && in_array($params['orderBy'], ['hits', 'publish_time'])) {
                $orderByDir = 'desc';
                if (in_array($params['orderByDir'], ['asc', 'desc'])) {
                    $orderByDir = $params['orderByDir'];
                }

                $query['body']['sort'] = [
                    [
                        $params['orderBy'] => [
                            'order' => $orderByDir
                        ]
                    ],
                ];
            }
        }

        // 分页
        $pageSize = null;
        if (isset($params['pageSize']) && is_numeric($params['pageSize']) && $params['pageSize'] > 0) {
            $pageSize = $params['pageSize'];
        } else {
            $pageSize = $configArticle->pageSize;
        }

        if ($pageSize > 200) {
            $pageSize = 200;
        }

        $page = null;
        if (isset($params['page']) && is_numeric($params['page']) && $params['page'] > 0) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        $query['body']['size'] = $pageSize;
        $query['body']['from'] = ($page - 1) * $pageSize;

        $results = $es->search($query);

        $total = 0;
        if (isset($results['hits']['total']['value'])) {
            $total = $results['hits']['total']['value'];
        }

        $rows = [];
        foreach ($results['hits']['hits'] as $x) {
            $rows[] = $this->formatEsArticle($x['_source']);
        }

        return [
            'total' => $total,
            'pageSize' => $pageSize,
            'page' => $page,
            'rows' => $rows,
        ];
    }

    /**
     * 按关銉词搜索
     *
     * @param string $keywords 关銉词
     * @param array $params
     * @return array
     */
    public function searchFromDb(string $keywords, array $params = []): array
    {
        $configArticle = Be::getConfig('App.Cms.Article');
        $tableArticle = Be::getTable('cms_article');

        $tableArticle->where('is_enable', 1);
        $tableArticle->where('is_delete', 0);

        if ($keywords !== '') {
            $tableArticle->where('title', 'like', '%' . $keywords . '%');
        }

        if (isset($params['isPushHome']) && in_array($params['isPushHome'], [0, 1])) {
            $tableArticle->where('is_push_home', $params['isPushHome']);
        }

        $db = Be::getDb();
        if (isset($params['categoryId']) && $params['categoryId']) {
            $sql = 'SELECT article_id FROM cms_article_category WHERE category_id = ?';
            $productIds = $db->getValues($sql, [$params['categoryId']]);
            if (count($productIds) > 0) {
                $tableArticle->where('id', 'IN', $productIds);
            } else {
                $tableArticle->where('id', '');
            }
        }

        $total = $tableArticle->count();

        if (isset($params['orderBy']) && in_array($params['orderBy'], ['hits', 'publish_time'])) {
            $orderByDir = 'desc';
            if (isset($params['orderByDir']) && in_array($params['orderByDir'], ['asc', 'desc'])) {
                $orderByDir = $params['orderByDir'];
            }

            $tableArticle->orderBy($params['orderBy'], $orderByDir);
        } else {
            $tableArticle->orderBy('is_on_top DESC, publish_time DESC');
        }


        if (isset($params['orderBy'])) {
            if (is_array($params['orderBy'])) {
                $len1 = count($params['orderBy']);
                if ($len1 > 0 && is_array($params['orderByDir'])) {
                    $len2 = count($params['orderByDir']);
                    if ($len1 === $len2) {
                        $orderByStrings = [];
                        for ($i = 0; $i< $len1; $i++) {
                            $orderByDir = 'desc';
                            if (in_array($params['orderByDir'][$i], ['asc', 'desc'])) {
                                $orderByDir = $params['orderByDir'][$i];
                            }
                            $orderByStrings[] = $params['orderBy'][$i] . ' ' . strtoupper($orderByDir);
                        }

                        $tableArticle->orderBy(implode(', ', $orderByStrings));
                    }
                }
            } elseif (is_string($params['orderBy']) && in_array($params['orderBy'], ['hits', 'publish_time'])) {
                $orderByDir = 'desc';
                if (in_array($params['orderByDir'], ['asc', 'desc'])) {
                    $orderByDir = $params['orderByDir'];
                }

                $tableArticle->orderBy($params['orderBy'], strtoupper($orderByDir));
            }
        }


        // 分页
        $pageSize = null;
        if (isset($params['pageSize']) && is_numeric($params['pageSize']) && $params['pageSize'] > 0) {
            $pageSize = $params['pageSize'];
        } else {
            $pageSize = $configArticle->pageSize;
        }

        if ($pageSize > 200) {
            $pageSize = 200;
        }
        $tableArticle->limit($pageSize);

        $page = null;
        if (isset($params['page']) && is_numeric($params['page']) && $params['page'] > 0) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        $tableArticle->offset(($page - 1) * $pageSize);

        $rows = $tableArticle->getObjects();

        return [
            'total' => $total,
            'pageSize' => $pageSize,
            'page' => $page,
            'rows' => $rows,
        ];
    }

    /**
     * 获取按指定排序的前N个文章
     *
     * @param int $n
     * @param string $orderBy
     * @param string $orderByDir
     * @return array
     * @throws \Be\Runtime\RuntimeException
     */
    public function getTopArticles(int $n, string $orderBy, string $orderByDir = 'desc'): array
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return $this->getTopArticlesFromDb($n, $orderBy, $orderByDir);
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'size' => $n,
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'term' => [
                                    'is_enable' => true,
                                ],
                            ],
                            [
                                'term' => [
                                    'is_delete' => false,
                                ],
                            ],
                        ]
                    ]
                ],
                'sort' => [
                    $orderBy => [
                        'order' => $orderByDir
                    ]
                ]
            ]
        ];

        $es = Be::getEs();
        $results = $es->search($query);

        if (!isset($results['hits']['hits'])) {
            return [];
        }

        $return = [];
        foreach ($results['hits']['hits'] as $x) {
            $return[] = $this->formatEsArticle($x['_source']);
        }

        return $return;
    }

    /**
     * 获取按指定排序的前N个文章
     *
     * @param int $n
     * @param string $orderBy
     * @param string $orderByDir
     * @return array
     * @throws \Be\Runtime\RuntimeException
     */
    public function getTopArticlesFromDb(int $n, string $orderBy, string $orderByDir = 'desc'): array
    {
        return Be::getTable('cms_article')
            ->where('is_enable', 1)
            ->where('is_delete', 0)
            ->orderBy($orderBy, $orderByDir)
            ->limit($n)
            ->getObjects();
    }

    /**
     * 最新文章
     *
     * @param int $n 结果数量
     * @return array
     */
    public function getLatestArticles(int $n = 10): array
    {
        return $this->getTopArticles($n, 'publish_time', 'desc');
    }

    /**
     * 热门文章
     *
     * @param int $n 结果数量
     * @return array
     */
    public function getHottestArticles(int $n = 10): array
    {
        return $this->getTopArticles($n, 'hits', 'desc');
    }

    /**
     * 热门搜索
     *
     * @param int $n 结果数量
     * @return array
     */
    public function getTopSearchArticles(int $n = 10): array
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return [];
        }

        $keywords = $this->getTopSearchKeywords(5);
        if (!$keywords) {
            return [];
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'size' => $n,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'title' => implode(', ', $keywords)
                            ]
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'is_enable' => true,
                                ],
                            ],
                            [
                                'term' => [
                                    'is_delete' => false,
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        ];

        $es = Be::getEs();
        $results = $es->search($query);

        if (!isset($results['hits']['hits'])) {
            return [];
        }

        $return = [];
        foreach ($results['hits']['hits'] as $x) {
            $return[] = $this->formatEsArticle($x['_source']);
        }

        return $return;
    }

    /**
     * 跟据文章名称，获取相似文章
     *
     * @param string $articleId 文章ID
     * @param string $articleTitle 文章标题
     * @param int $n
     * @return array
     */
    public function getSimilarArticles(string $articleId, string $articleTitle, int $n = 12): array
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return $this->getSimilarArticlesFromDb($articleId, $articleTitle, $n);
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'size' => $n,
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'term' => [
                                '_id' => $articleId
                            ]
                        ],
                        'must' => [
                            'match' => [
                                'title' => $articleTitle
                            ]
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'is_enable' => true,
                                ],
                            ],
                            [
                                'term' => [
                                    'is_delete' => false,
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        ];

        $es = Be::getEs();
        $results = $es->search($query);

        if (!isset($results['hits']['hits'])) {
            return [];
        }

        $return = [];
        foreach ($results['hits']['hits'] as $x) {
            $return[] = $this->formatEsArticle($x['_source']);
        }

        return $return;
    }

    /**
     * 跟据文章名称，获取相似文章
     *
     * @param string $articleId 文章ID
     * @param string $articleTitle 文章标题
     * @param int $n
     * @return array
     */
    public function getSimilarArticlesFromDb(string $articleId, string $articleTitle, int $n = 12): array
    {
        $tableArticle = Be::getTable('cms_article');
        $tableArticle->where('is_enable', 1)
            ->where('is_delete', 0)
            ->where('id', '!=', $articleId);

        if ($articleTitle !== '') {
            $tableArticle->where('title', 'like', '%' . $articleTitle . '%');
        }

        $tableArticle->limit($n);

        return $tableArticle->getObjects();
    }

    /**
     * 狙你喜欢文章
     *
     * @param int $n 结果数量
     * @param string $excludeArticleId 排除拽定的文章
     * @return array
     */
    public function getGuessYouLikeArticles(int $n = 40, string $excludeArticleId = null): array
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return [];
        }

        $my = Be::getUser();
        $es = Be::getEs();
        $cache = Be::getCache();

        $historyKey = 'Cms:Article:History:' . $my->id;
        $history = $cache->get($historyKey);

        $keywords = [];
        if ($history && is_array($history) && count($history) > 0) {
            $keywords = $history;
        }

        if (!$keywords) {
            $keywords = $this->getTopSearchKeywords(10);
        }

        if (!$keywords) {
            return [];
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'size' => $n,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'title' => implode(', ', $keywords)
                            ]
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'is_enable' => true,
                                ],
                            ],
                            [
                                'term' => [
                                    'is_delete' => false,
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        ];

        if ($excludeArticleId !== null) {
            $query['body']['query']['bool']['must_not'] = [
                'term' => [
                    '_id' => $excludeArticleId
                ]
            ];
        }

        $results = $es->search($query);

        if (!isset($results['hits']['hits'])) {
            return [];
        }

        $return = [];
        foreach ($results['hits']['hits'] as $x) {
            $return[] = $this->formatEsArticle($x['_source']);
        }

        return $return;
    }


    /**
     * 获取指定分类按指定排序的前N个文章
     *
     * @param string $categoryId 分类ID
     * @param int $n
     * @param string $orderBy
     * @param string $orderByDir
     * @return array
     * @throws \Be\Runtime\RuntimeException
     */
    public function getCategoryTopArticles(string $categoryId, int $n, string $orderBy, string $orderByDir = 'desc'): array
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return $this->getCategoryTopArticlesFromDb($categoryId, $n, $orderBy, $orderByDir);
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'size' => $n,
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'term' => [
                                    'is_enable' => true,
                                ],
                            ],
                            [
                                'term' => [
                                    'is_delete' => false,
                                ],
                            ],
                            [
                                'nested' => [
                                    'path' => 'categories',
                                    'query' => [
                                        'bool' => [
                                            'filter' => [
                                                [
                                                    'term' => [
                                                        'categories.id' => $categoryId,
                                                    ],
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ]
                        ]
                    ]
                ],
                'sort' => [
                    $orderBy => [
                        'order' => $orderByDir
                    ]
                ]
            ]
        ];

        $es = Be::getEs();
        $results = $es->search($query);

        if (!isset($results['hits']['hits'])) {
            return [];
        }

        $return = [];
        foreach ($results['hits']['hits'] as $x) {
            $return[] = $this->formatEsArticle($x['_source']);
        }

        return $return;
    }

    /**
     * 获取指定分类按指定排序的前N个文章
     *
     * @param string $categoryId 分类ID
     * @param int $n
     * @param string $orderBy
     * @param string $orderByDir
     * @return array
     * @throws \Be\Runtime\RuntimeException
     */
    public function getCategoryTopArticlesFromDb(string $categoryId, int $n, string $orderBy, string $orderByDir = 'desc'): array
    {
        $tableArticle = Be::getTable('cms_article');

        $tableArticle->where('is_enable', 1)
            ->where('is_delete', 0)
            ->orderBy($orderBy, $orderByDir)
            ->limit($n);

        $productIds = Be::getTable('cms_article_category')->where('category_id', $categoryId)->getValues('article_id');
        if (count($productIds) > 0) {
            $tableArticle->where('id', 'IN', $productIds);
        } else {
            $tableArticle->where('id', '');
        }

        return $tableArticle->getObjects();
    }

    /**
     * 指定分类下的热门文章
     *
     * @param string $categoryId 分类ID
     * @param int $n 结果数量
     * @return array
     */
    public function getCategoryHottestArticles(string $categoryId, int $n = 10): array
    {
        return $this->getCategoryTopArticles($categoryId, $n, 'hits', 'desc');
    }

    /**
     * 指定分类下的热门文章
     *
     * @param string $categoryId 分类ID
     * @param int $n 结果数量
     * @return array
     */
    public function getCategoryTopSearchArticles(string $categoryId, int $n = 10): array
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return [];
        }

        $keywords = $this->getTopSearchKeywords(10);
        if (!$keywords) {
            return [];
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'size' => $n,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'title' => implode(', ', $keywords)
                            ],
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'is_enable' => true,
                                ],
                            ],
                            [
                                'term' => [
                                    'is_delete' => false,
                                ],
                            ],
                            [
                                'nested' => [
                                    'path' => 'categories',
                                    'query' => [
                                        'bool' => [
                                            'filter' => [
                                                [
                                                    'term' => [
                                                        'categories.id' => $categoryId,
                                                    ],
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        ];

        $es = Be::getEs();
        $results = $es->search($query);

        if (!isset($results['hits']['hits'])) {
            return [];
        }

        $return = [];
        foreach ($results['hits']['hits'] as $x) {
            $return[] = $this->formatEsArticle($x['_source']);
        }

        return $return;
    }

    /**
     * 格式化ES查询出来的文章
     *
     * @param array $rows
     * @return object
     */
    private function formatEsArticle(array $row): object
    {
        $article = (object)$row;

        $categories = [];
        if (is_array($article->categories) && count($article->categories) > 0) {
            foreach ($article->categories as $category) {
                $categories[] = (object)$category;
            }
        }
        $article->categories = $categories;

        return $article;
    }


    /**
     * 从搜索历史出提取热门搜索词
     *
     * @param int $n
     * @return array
     */
    public function getTopSearchKeywords(int $n = 6): array
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return [];
        }

        $cache = Be::getCache();
        $historyKey = 'Cms:TopSearchKeywords';
        $topSearchKeywords = $cache->get($historyKey);
        if ($topSearchKeywords) {
            return $topSearchKeywords;
        }

        $es = Be::getEs();
        $query = [
            'index' => $configEs->indexArticleSearchHistory,
            'body' => [
                'size' => 0,
                'query' => [
                    'bool' => [
                        'filter' => [
                            [

                            ],
                        ]
                    ]
                ],
                'aggs' => [
                    'topN' => [
                        'terms' => [
                            'field' => 'keyword',
                            'size' => $n
                        ]
                    ]
                ]
            ]
        ];

        $result = $es->search($query);

        $hotKeywords = [];
        if (isset($result['aggregations']['topN']['buckets']) &&
            is_array($result['aggregations']['topN']['buckets']) &&
            count($result['aggregations']['topN']['buckets']) > 0
        ) {
            foreach ($result['aggregations']['topN']['buckets'] as $v) {
                $hotKeywords[] = $v['key'];
            }
        }

        $cache->set($historyKey, $hotKeywords, 600);

        return $hotKeywords;
    }

    /**
     * 获取文章伪静态页网址
     *
     * @param array $params
     * @return string
     * @throws ServiceException
     */
    public function getArticleUrl(array $params = []): string
    {
        $page = $this->getArticle($params['id']);
        return '/article/' . $page->url;
    }

    /**
     * 顶
     *
     * @param string $articleId 文章ID
     * @param int $value 值
     *
     * @throws \Exception
     */
    public function like($articleId, int $value = 1)
    {
        $my = Be::getUser();
        if ($my->isGuest()) {
            throw new \Exception('请先登陆！');
        }

        $tupleArticle = Be::getTuple('cms_article');
        try {
            $tupleArticle->load($articleId);
        } catch (\Throwable $t) {
            throw new \Exception('文章不存在！');
        }

        $tupleArticleVoteLog = Be::getTuple('cms_article_vote_log');
        try {
            $tupleArticleVoteLog->loadBy([
                'article_id' => $articleId,
                'user_id' => $my->id
            ]);
        } catch (\Throwable $t) {
        }

        if ($tupleArticleVoteLog->isLoaded()) {
            throw new \Exception('您已经表过态啦！');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $tupleArticleVoteLog->article_id = $articleId;
            $tupleArticleVoteLog->user_id = $my->id;
            $tupleArticleVoteLog->value = $value;
            $tupleArticleVoteLog->insert();

            if ($value === 1) {
                $tupleArticle->increment('like', $value);
            } elseif ($value === -1) {
                $tupleArticle->decrement('like', $value);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

    /**
     * 踩
     *
     * @param string $articleId 文章编号
     * @throws \Exception
     */
    public function dislike($articleId)
    {
        $this->like($articleId, -1);
    }

}
