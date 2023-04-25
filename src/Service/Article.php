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
        $key = 'App:Cms:Article:' . $articleId;
        if (Be::hasContext($key)) {
            $article = Be::getContext($key);
        } else {
            $cache = Be::getCache();
            $article = $cache->get($key);
            if ($article === false) {
                try {
                    $article = $this->getArticleFromDb($articleId);
                } catch (\Throwable $t) {
                    $article = '-1';
                }

                $configCache = Be::getConfig('App.Cms.Cache');
                $cache->set($key, $article, $configCache->article);
            }

            Be::setContext($key, $article);
        }

        if ($article === '-1') {
            throw new ServiceException(beLang('App.Cms', 'ARTICLE.NOT_EXIST'));
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
            throw new ServiceException(beLang('App.Cms', 'ARTICLE.NOT_EXIST'));
        }

        $article->is_enable = (int)$article->is_enable;
        $article->is_delete = (int)$article->is_delete;
        if ($article->is_enable !== 1 || $article->is_delete !== 0) {
            throw new ServiceException(beLang('App.Cms', 'ARTICLE.NOT_EXIST'));
        }

        $categories = [];
        $sql = 'SELECT category_id FROM cms_article_category WHERE article_id = ?';
        $categoryIds = $db->getValues($sql, [$articleId]);
        if (count($categoryIds) > 0) {
            $sql = 'SELECT id, `name` FROM cms_category WHERE is_delete=0 AND id IN (\'' . implode('\',\'', $categoryIds) . '\') ORDER BY ordering ASC';
            $categories = $db->getObjects($sql);
        }
        $article->categories = $categories;
        $article->category_ids = array_column($categories, 'id');

        $sql = 'SELECT tag FROM cms_article_tag WHERE article_id = ?';
        $article->tags = $db->getValues($sql, [$articleId]);

        $newArticle = new \stdClass();
        $newArticle->id = $article->id;
        $newArticle->image = $article->image;
        $newArticle->title = $article->title;
        $newArticle->summary = $article->summary;
        $newArticle->description = $article->description;
        $newArticle->url = $article->url;
        //$newArticle->url_custom = (int)$article->url_custom;
        $newArticle->author = $article->author;
        $newArticle->publish_time = $article->publish_time;
        $newArticle->seo_title = $article->seo_title;
        //$newArticle->seo_title_custom = (int)$article->seo_title_custom;
        $newArticle->seo_description = $article->seo_description;
        //$newArticle->seo_description_custom = (int)$article->seo_description_custom;
        $newArticle->seo_keywords = $article->seo_keywords;
        //$newArticle->ordering = (int)$article->ordering;
        $newArticle->hits = $article->hits;
        //$newArticle->is_push_home = (int)$article->is_push_home;
        // $newArticle->is_on_top = (int)$article->is_on_top;

        $newArticle->categories = $article->categories;
        $newArticle->category_ids = $article->category_ids;
        $newArticle->tags = $article->tags;

        return $newArticle;
    }

    /**
     * 从缓存获取多个文章数据
     *
     * @param array $articleIds 多个商品ID
     * @param bool $throwException 不存在的文章是否抛出异常
     * @return array
     */
    public function getArticles(array $articleIds = [], bool $throwException = true): array
    {
        $configCache = Be::getConfig('App.Cms.Cache');
        $cache = Be::getCache();

        $keys = [];
        foreach ($articleIds as $articleId) {
            $keys[] = 'App:Cms:Article:' . $articleId;
        }

        $articles = $cache->getMany($keys);

        $noArticles = true;
        foreach ($articles as $article) {
            if ($article) {
                $noArticles = false;
            }
        }

        // 缓存中没有任何商品，全部从数据库中读取并缓存
        if ($noArticles) {

            $newArticles = [];
            foreach ($articleIds as $articleId) {

                $key = 'App:Cms:Article:' . $articleId;
                try {
                    $article = $this->getArticleFromDb($articleId);
                } catch (\Throwable $t) {
                    $article = '-1';
                }

                $cache->set($key, $article, $configCache->article);

                if ($article === '-1') {
                    if ($throwException) {
                        throw new ServiceException(beLang('App.Cms', 'ARTICLE.NOT_EXIST'));
                    } else {
                        continue;
                    }
                }

                $newArticles[] = $article;
            }

        } else {

            $newArticles = [];
            $i = 0;
            foreach ($articles as $article) {
                if ($article === false || $article === '-1') {
                    if ($throwException) {
                        throw new ServiceException(beLang('App.Cms', 'ARTICLE.NOT_EXIST'));
                    } else {
                        continue;
                    }
                }

                $newArticles[] = $article;
                $i++;
            }
        }

        return $newArticles;
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

        $historyKey = 'App:Cms:Article:History:' . $my->id;
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
        $hitsKey = 'App:Cms:Article:hits:' . $articleId;
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
        $configSystemEs = Be::getConfig('App.System.Es');
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configSystemEs->enable === 0 || $configEs->enable === 0) {
            return $this->searchFromDb($keywords, $params);
        }

        $configArticle = Be::getConfig('App.Cms.Article');

        $cache = Be::getCache();
        $es = Be::getEs();

        $keywords = trim($keywords);
        if ($keywords !== '') {
            // 将本用户搜索的关键词写入ES search_history
            $counterKey = 'App:Cms:Article:Article:searchHistory';
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

        $cacheKey = 'App:Cms:Article:search';
        if ($keywords !== '') {
            $cacheKey .= ':' . $keywords;
        }
        $cacheKey .= ':' . md5(serialize($params));

        $results = $cache->get($cacheKey);
        if ($results !== false) {
            return $results;
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
            ]
        ];

        if ($keywords === '') {
            $query['body']['min_score'] = 0;
        } else {
            $query['body']['min_score'] = 0.01;

            if (!isset($query['body']['query'])) {
                $query['body']['query'] = [];
            }

            if (!isset($query['body']['query']['bool'])) {
                $query['body']['query']['bool'] = [];
            }

            $query['body']['query']['bool']['should'] = [
                [
                    'match' => [
                        'title' => [
                            'query' => $keywords,
                            'boost' => 2,
                        ]
                    ],
                ],
                [
                    'match' => [
                        'summary' => [
                            'query' => $keywords,
                            'boost' => 1,
                        ]
                    ],
                ],
                [
                    'match' => [
                        'description' => [
                            'query' => $keywords,
                            'boost' => 1,
                        ]
                    ],
                ],
            ];
        }

        if (isset($params['isPushHome']) && in_array($params['isPushHome'], [0, 1])) {

            if (!isset($query['body']['query'])) {
                $query['body']['query'] = [];
            }

            if (!isset($query['body']['query']['bool'])) {
                $query['body']['query']['bool'] = [];
            }

            if (!isset($query['body']['query']['bool']['filter'])) {
                $query['body']['query']['bool']['filter'] = [];
            }

            $query['body']['query']['bool']['filter'][] = [
                'term' => [
                    'is_push_home' => (bool)$params['isPushHome'],
                ]
            ];
        }

        if (isset($params['categoryId']) && $params['categoryId']) {

            if (!isset($query['body']['query'])) {
                $query['body']['query'] = [];
            }

            if (!isset($query['body']['query']['bool'])) {
                $query['body']['query']['bool'] = [];
            }

            if (!isset($query['body']['query']['bool']['filter'])) {
                $query['body']['query']['bool']['filter'] = [];
            }

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

        if (isset($params['tag']) && $params['tag']) {

            if (!isset($query['body']['query'])) {
                $query['body']['query'] = [];
            }

            if (!isset($query['body']['query']['bool'])) {
                $query['body']['query']['bool'] = [];
            }

            if (!isset($query['body']['query']['bool']['filter'])) {
                $query['body']['query']['bool']['filter'] = [];
            }

            $query['body']['query']['bool']['filter'][] = [
                'term' => [
                    'tags' => $params['tag'],
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
                        for ($i = 0; $i < $len1; $i++) {
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
            $article = (object)$x['_source'];
            try {
                $article->absolute_url = beUrl('Cms.Article.detail', ['id' => $article->id]);
            } catch (\Throwable $t) {
                continue;
            }

            $rows[] = $this->formatEsArticle($article);
        }

        $result = [
            'total' => $total,
            'pageSize' => $pageSize,
            'page' => $page,
            'rows' => $rows,
        ];

        $configCache = Be::getConfig('App.Cms.Cache');
        $cache->set($cacheKey, $result, $configCache->articles);

        return $result;
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
        $cache = Be::getCache();
        $cacheKey = 'App:Cms:Article:searchFromDb';
        if ($keywords !== '') {
            $cacheKey .= ':' . $keywords;
        }
        $cacheKey .= ':' . md5(serialize($params));

        $results = $cache->get($cacheKey);
        if ($results !== false) {
            return $results;
        }

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
            $articleIds = $db->getValues($sql, [$params['categoryId']]);
            if (count($articleIds) > 0) {
                $tableArticle->where('id', 'IN', $articleIds);
            } else {
                $tableArticle->where('id', '');
            }
        }

        if (isset($params['tag']) && $params['tag']) {
            $sql = 'SELECT article_id FROM cms_article_tag WHERE tag = ?';
            $articleIds = $db->getValues($sql, [$params['tag']]);
            if (count($articleIds) > 0) {
                $tableArticle->where('id', 'IN', $articleIds);
            } else {
                $tableArticle->where('id', '');
            }
        }

        $total = $tableArticle->count();

        if (isset($params['orderBy'])) {
            if (is_array($params['orderBy'])) {
                $len1 = count($params['orderBy']);
                if ($len1 > 0 && is_array($params['orderByDir'])) {
                    $len2 = count($params['orderByDir']);
                    if ($len1 === $len2) {
                        $orderByStrings = [];
                        for ($i = 0; $i < $len1; $i++) {
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
                if (isset($params['orderByDir']) && in_array($params['orderByDir'], ['asc', 'desc'])) {
                    $orderByDir = $params['orderByDir'];
                }

                $tableArticle->orderBy($params['orderBy'], strtoupper($orderByDir));
            }
        } else {
            $tableArticle->orderBy('is_on_top DESC, publish_time DESC');
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

        $articleIds = $tableArticle->getValues('id');

        $rows = $this->getArticles($articleIds, false);

        $result = [
            'total' => $total,
            'pageSize' => $pageSize,
            'page' => $page,
            'rows' => $rows,
        ];

        $configCache = Be::getConfig('App.Cms.Cache');
        $cache->set($cacheKey, $result, $configCache->articles);

        return $result;
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
        $configSystemEs = Be::getConfig('App.System.Es');
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configSystemEs->enable === 0 || $configEs->enable === 0) {
            return $this->getSimilarArticlesFromDb($articleId, $articleTitle, $n);
        }

        $cache = Be::getCache();
        $cacheKey = 'App:Cms:Article:SimilarArticles:' . $articleId . ':' . $n;
        $results = $cache->get($cacheKey);
        if ($results !== false) {
            return $results;
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
                    ]
                ]
            ]
        ];

        $es = Be::getEs();
        $results = $es->search($query);

        $return = [];
        foreach ($results['hits']['hits'] as $x) {
            $article = (object)$x['_source'];
            try {
                $article->absolute_url = beUrl('Cms.Article.detail', ['id' => $article->id]);
            } catch (\Throwable $t) {
                continue;
            }

            $return[] = $this->formatEsArticle($article);
        }

        $configCache = Be::getConfig('App.Cms.Cache');
        $cache->set($cacheKey, $return, $configCache->articles);

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
        $cache = Be::getCache();
        $cacheKey = 'App:Cms:Article:SimilarArticlesFromDb:' . $articleId . ':' . $n;
        $results = $cache->get($cacheKey);
        if ($results !== false) {
            return $results;
        }

        $tableArticle = Be::getTable('cms_article');
        $tableArticle->where('is_enable', 1)
            ->where('is_delete', 0)
            ->where('id', '!=', $articleId);

        if ($articleTitle !== '') {
            $tableArticle->where('title', 'like', '%' . $articleTitle . '%');
        }

        $tableArticle->limit($n);

        $articleIds = $tableArticle->getValues('id');

        $result = $this->getArticles($articleIds, false);

        $configCache = Be::getConfig('App.Cms.Cache');
        $cache->set($cacheKey, $result, $configCache->articles);

        return $result;
    }

    /**
     * 获取按指定排序的前N个文章
     *
     * @param array $params 查询参数
     */
    public function getTopNArticles(array $params = []): array
    {
        $configSystemEs = Be::getConfig('App.System.Es');
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configSystemEs->enable === 0 || $configEs->enable === 0) {
            return $this->getTopArticlesFromDb($params);
        }

        $cache = Be::getCache();
        $cacheKey = 'App:Cms:Article:TopArticles:' . md5(serialize($params));
        $results = $cache->get($cacheKey);
        if ($results !== false) {
            return $results;
        }


        $orderBy = $params['orderBy'];

        $orderByDir = 'desc';
        if (isset($params['orderByDir']) && in_array($params['orderByDir'], ['asc', 'desc'])) {
            $orderByDir = $params['orderByDir'];
        }

        // 分页
        if (isset($params['pageSize']) && is_numeric($params['pageSize']) && $params['pageSize'] > 0) {
            $pageSize = $params['pageSize'];
        } else {
            $pageSize = 12;
        }

        if ($pageSize > 200) {
            $pageSize = 200;
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'size' => $pageSize,
                'sort' => [
                    $orderBy => [
                        'order' => $orderByDir
                    ]
                ]
            ]
        ];

        $es = Be::getEs();
        $results = $es->search($query);

        $return = [];
        foreach ($results['hits']['hits'] as $x) {
            $article = (object)$x['_source'];
            try {
                $article->absolute_url = beUrl('Cms.Article.detail', ['id' => $article->id]);
            } catch (\Throwable $t) {
                continue;
            }

            $return[] = $this->formatEsArticle($article);
        }

        $configCache = Be::getConfig('App.Cms.Cache');
        $cache->set($cacheKey, $return, $configCache->articles);

        return $return;
    }

    /**
     * 获取按指定排序的前N个文章
     *
     * @param array $params
     * @return array
     */
    public function getTopArticlesFromDb(array $params = []): array
    {
        $cache = Be::getCache();
        $cacheKey = 'App:Cms:Article:TopArticlesFromDb:' . md5(serialize($params));
        $result = $cache->get($cacheKey);
        if ($result !== false) {
            return $result;
        }

        $orderBy = $params['orderBy'];

        $orderByDir = 'desc';
        if (isset($params['orderByDir']) && in_array($params['orderByDir'], ['asc', 'desc'])) {
            $orderByDir = $params['orderByDir'];
        }

        // 分页
        if (isset($params['pageSize']) && is_numeric($params['pageSize']) && $params['pageSize'] > 0) {
            $pageSize = $params['pageSize'];
        } else {
            $pageSize = 12;
        }

        if ($pageSize > 200) {
            $pageSize = 200;
        }

        $articleIds = Be::getTable('cms_article')
            ->where('is_enable', 1)
            ->where('is_delete', 0)
            ->orderBy($orderBy, $orderByDir)
            ->limit($pageSize)
            ->getValues('id');

        $result = $this->getArticles($articleIds, false);

        $configCache = Be::getConfig('App.Cms.Cache');
        $cache->set($cacheKey, $result, $configCache->articles);

        return $result;
    }

    /**
     * 最新文章
     *
     * @param int $n 结果数量
     * @return array
     */
    public function getLatestTopNArticles(int $n = 10): array
    {
        return $this->getTopNArticles([
            'orderBy' => 'publish_time',
            'orderByDir' => 'desc',
            'pageSize' => $n,
        ]);
    }

    /**
     * 热门文章
     *
     * @param int $n 结果数量
     * @return array
     */
    public function getHottestTopNArticles(int $n = 10): array
    {
        return $this->getTopNArticles([
            'orderBy' => 'hits',
            'orderByDir' => 'desc',
            'pageSize' => $n,
        ]);
    }

    /**
     * 指下究类的最新文章
     *
     * @param string $categoryId 分类ID
     * @param int $n 结果数量
     * @return array
     */
    public function getCategoryLatestTopNArticles(string $categoryId, int $n = 10): array
    {
        return $this->getTopNArticles([
            'categoryId' => $categoryId,
            'orderBy' => 'publish_time',
            'orderByDir' => 'desc',
            'pageSize' => $n,
        ]);
    }

    /**
     * 指下究类的热门文章
     *
     * @param string $categoryId 分类ID
     * @param int $n 结果数量
     * @return array
     */
    public function getCategoryHottestTopNArticles(string $categoryId, int $n = 10): array
    {
        return $this->getTopNArticles([
            'categoryId' => $categoryId,
            'orderBy' => 'hits',
            'orderByDir' => 'desc',
            'pageSize' => $n,
        ]);
    }


    /**
     * 热搜文章
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getHotSearchArticles(array $params = []): array
    {
        // 分页
        if (isset($params['pageSize']) && is_numeric($params['pageSize']) && $params['pageSize'] > 0) {
            $pageSize = $params['pageSize'];
        } else {
            $pageSize = 12;
        }

        if ($pageSize > 200) {
            $pageSize = 200;
        }

        if (isset($params['page']) && is_numeric($params['page']) && $params['page'] > 0) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        $configSystemEs = Be::getConfig('App.System.Es');
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configSystemEs->enable === 0 || $configEs->enable === 0) {
            return [
                'total' => 0,
                'pageSize' => $pageSize,
                'page' => $page,
                'rows' => [],
            ];
        }

        $keywords = $this->getHotSearchKeywords(5);
        if (!$keywords) {
            return [
                'total' => 0,
                'pageSize' => $pageSize,
                'page' => $page,
                'rows' => [],
            ];
        }

        $cache = Be::getCache();
        $cacheKey = 'App:Cms:Article:HotSearchArticles:' . md5(serialize($params));
        $results = $cache->get($cacheKey);
        if ($results !== false) {
            return $results;
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'size' => $pageSize,
                'from' => ($page - 1) * $pageSize,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'title' => implode(', ', $keywords)
                            ]
                        ],
                    ]
                ]
            ]
        ];

        if (isset($params['categoryId']) && $params['categoryId'] !== '') {
            $query['body']['query']['bool']['filter'] = [
                [
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
                    ],
                ],
            ];
        }
        $es = Be::getEs();
        $results = $es->search($query);

        $total = 0;
        if (isset($results['hits']['total']['value'])) {
            $total = $results['hits']['total']['value'];
        }

        $rows = [];
        foreach ($results['hits']['hits'] as $x) {
            $article = (object)$x['_source'];
            try {
                $article->absolute_url = beUrl('Cms.Article.detail', ['id' => $article->id]);
            } catch (\Throwable $t) {
                continue;
            }

            $rows[] = $this->formatEsArticle($article);
        }

        $return = [
            'total' => $total,
            'pageSize' => $pageSize,
            'page' => $page,
            'rows' => $rows,
        ];

        $configCache = Be::getConfig('App.Cms.Cache');
        $cache->set($cacheKey, $return, $configCache->articles);

        return $return;
    }

    /**
     * 热搜文章
     *
     * @param array $params 查询参数
     * @return array
     */

    /**
     * 热搜文章
     *
     * @param int $n Top N 数量
     * @return array
     */
    public function getHotSearchTopNArticles(int $n = 10): array
    {
        $results = $this->getHotSearchArticles([
            'pageSize' => $n,
        ]);

        return $results['rows'];
    }

    /**
     * 指定分类下的热搜文章
     *
     * @param string $categoryId 分类ID
     * @param int $n Top N 数量
     * @return array
     */
    public function getCategoryHotSearchTopNArticles(string $categoryId, int $n = 10): array
    {
        $results = $this->getHotSearchArticles([
            'categoryId' => $categoryId,
            'pageSize' => $n,
        ]);

        return $results['rows'];
    }


    /**
     * 猜你喜欢
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getGuessYouLikeArticles(array $params = []): array
    {
        // 分页
        if (isset($params['pageSize']) && is_numeric($params['pageSize']) && $params['pageSize'] > 0) {
            $pageSize = $params['pageSize'];
        } else {
            $pageSize = 12;
        }

        if ($pageSize > 200) {
            $pageSize = 200;
        }

        if (isset($params['page']) && is_numeric($params['page']) && $params['page'] > 0) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        $configSystemEs = Be::getConfig('App.System.Es');
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configSystemEs->enable === 0 || $configEs->enable === 0) {
            return [
                'total' => 0,
                'pageSize' => $pageSize,
                'page' => $page,
                'rows' => [],
            ];
        }


        $my = Be::getUser();
        $es = Be::getEs();
        $cache = Be::getCache();

        $historyKey = 'App:Cms:Article:history:' . $my->id;
        $history = $cache->get($historyKey);

        $keywords = [];
        if ($history && is_array($history) && count($history) > 0) {
            $keywords = $history;
        }

        if (!$keywords) {
            $keywords = $this->getHotSearchKeywords(10);
        }

        if (!$keywords) {
            return [
                'total' => 0,
                'pageSize' => $pageSize,
                'page' => $page,
                'rows' => [],
            ];
        }

        $query = [
            'index' => $configEs->indexArticle,
            'body' => [
                'size' => $pageSize,
                'from' => ($page - 1) * $pageSize,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'title' => implode(', ', $keywords)
                            ]
                        ],
                    ]
                ]
            ]
        ];

        if (isset($params['excludeArticleId']) && $params['excludeArticleId'] !== '') {
            $query['body']['query']['bool']['must_not'] = [
                'term' => [
                    '_id' => $params['excludeArticleId']
                ]
            ];
        }

        if (isset($params['categoryId']) && $params['categoryId'] !== '') {
            $query['body']['query']['bool']['filter'] = [
                [
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
                    ],
                ],
            ];
        }

        $results = $es->search($query);

        $total = 0;
        if (isset($results['hits']['total']['value'])) {
            $total = $results['hits']['total']['value'];
        }

        $rows = [];
        foreach ($results['hits']['hits'] as $x) {
            $article = (object)$x['_source'];
            try {
                $article->absolute_url = beUrl('Cms.Article.detail', ['id' => $article->id]);
            } catch (\Throwable $t) {
                continue;
            }

            $rows[] = $this->formatEsArticle($article);
        }

        $return = [
            'total' => $total,
            'pageSize' => $pageSize,
            'page' => $page,
            'rows' => $rows,
        ];

        return $return;
    }


    /**
     * 猜你喜欢 Top N
     *
     * @param int $n Top N 数量
     * @param string $excludeArticleId 要排除的文章ID
     * @return array
     */
    public function getGuessYouLikeTopNArticles(int $n = 40, string $excludeArticleId = null): array
    {
        $results = $this->getGuessYouLikeArticles([
            'pageSize' => $n,
            'excludeArticleId' => $excludeArticleId,
        ]);

        return $results['rows'];
    }

    /**
     * 指定分类下猜你喜欢
     *
     * @param string $categoryId 分类ID
     * @param int $n Top N 数量
     * @param string $excludeArticleId 要排除的文章ID
     * @return array
     */
    public function getCategoryGuessYouLikeTopNArticles(string $categoryId, int $n = 40, string $excludeArticleId = null): array
    {
        $results = $this->getGuessYouLikeArticles([
            'categoryId' => $categoryId,
            'pageSize' => $n,
            'excludeArticleId' => $excludeArticleId,
        ]);

        return $results['rows'];
    }

    /**
     * 格式化ES查询出来的文章
     *
     * @param object $article
     * @return object
     */
    private function formatEsArticle(object $article): object
    {
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
    public function getHotSearchKeywords(int $n = 6): array
    {
        $configSystemEs = Be::getConfig('App.System.Es');
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configSystemEs->enable === 0 || $configEs->enable === 0) {
            return [];
        }

        $cache = Be::getCache();
        $cacheKey = 'App:Cms:Article:HotSearchKeywords';
        $topSearchKeywords = $cache->get($cacheKey);
        if ($topSearchKeywords) {
            return $topSearchKeywords;
        }

        $es = Be::getEs();
        $query = [
            'index' => $configEs->indexArticleSearchHistory,
            'body' => [
                'size' => 0,
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

        $configCache = Be::getConfig('App.Cms.Cache');
        $cache->set($cacheKey, $hotKeywords, $configCache->hotKeywords);

        return $hotKeywords;
    }

    /**
     * 获取文章伪静态页网址
     *
     * @param array $params
     * @return string
     * @throws ServiceException
     */
    public function getArticleUrl(array $params = []): array
    {
        $configArticle = Be::getConfig('App.Cms.Article');
        $article = $this->getArticle($params['id']);

        $params1 = ['id' => $params['id']];
        unset($params['id']);
        return [$configArticle->urlPrefix . $article->url, $params1, $params];
    }


    /**
     * 获取标签
     *
     * @param int $n
     * @return array
     */
    public function getTopTags(int $n): array
    {
        $cache = Be::getCache();

        $key = 'App:Cms:Article:TopTags:' . $n;
        $tags = $cache->get($key);
        if ($tags === false) {
            try {
                $tags = $this->getTopTagsFromDb($n);
            } catch (\Throwable $t) {
                $tags = [];
            }

            $configCache = Be::getConfig('App.Cms.Cache');
            $cache->set($key, $tags, $configCache->tag);
        }

        return $tags;
    }

    /**
     * 从数据库获取标签
     *
     * @param int $n
     * @return array
     */
    public function getTopTagsFromDb(int $n): array
    {
        $db = Be::getDb();
        $sql = 'SELECT tag FROM (SELECT tag, COUNT(*) AS cnt FROM `cms_article_tag` GROUP  BY tag) t ORDER BY cnt DESC LIMIT ' . $n;
        return $db->getValues($sql);
    }


}
