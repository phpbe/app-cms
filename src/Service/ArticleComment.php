<?php
namespace Be\App\Cms\Service;

use Be\App\ControllerException;
use Be\App\ServiceException;
use Be\Be;

class ArticleComment
{

    /**
     * 获取文章评论
     *
     * @param string $articleId
     * @param array $params
     * @return array
     */
    public function getComments(string $articleId, array $params = []): array
    {
        $configArticle = Be::getConfig('App.Cms.Article');
        if ($configArticle->commentsCacheExpire > 0) {
            $cache = Be::getCache();
            $cacheKey = 'App:Cms:Article:Comments:' . $articleId . ':' . md5(serialize($params));
            $comments = $cache->get($cacheKey);
            if ($comments !== false) {
                return $comments;
            }
        }

        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            $comments =  $this->getCommentsFromDb($articleId, $params);
        } else {
            $es = Be::getEs();

            $query = [
                'index' => $configEs->indexArticleComment,
                'body' => [
                    'query' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'article_id' => $articleId,
                                    ],
                                ],
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
                } elseif (is_string($params['orderBy'])) {
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
                $rows[] = (object)$x['_source'];
            }

            $comments = [
                'total' => $total,
                'pageSize' => $pageSize,
                'page' => $page,
                'rows' => $rows,
            ];
        }

        if ($configArticle->commentsCacheExpire > 0) {
            $cache->set($cacheKey, $comments, $configArticle->commentsCacheExpire);
        }

        return $comments;
    }


    /**
     * 获取文章评论
     *
     * @param string $articleId
     * @return array
     */
    public function getCommentsFromDb(string $articleId, array $params = []): array
    {
        $configArticle = Be::getConfig('App.Cms.Article');
        $tableArticleComment = Be::getTable('cms_article_comment');
        $tableArticleComment->where('article_id', $articleId);
        $tableArticleComment->where('is_enable', 1);
        $tableArticleComment->where('is_delete', 0);

        $total = $tableArticleComment->count();

        if (isset($params['orderBy'])) {
            $orderByDir = 'desc';
            if (isset($params['orderByDir']) && in_array($params['orderByDir'], ['asc', 'desc'])) {
                $orderByDir = $params['orderByDir'];
            }
            $tableArticleComment->orderBy($params['orderBy'], $orderByDir);
        } else {
            $tableArticleComment->orderBy('create_time DESC');
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
        $tableArticleComment->limit($pageSize);

        $page = null;
        if (isset($params['page']) && is_numeric($params['page']) && $params['page'] > 0) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        $tableArticleComment->offset(($page - 1) * $pageSize);

        $rows = $tableArticleComment->getObjects();

        return [
            'total' => $total,
            'pageSize' => $pageSize,
            'page' => $page,
            'rows' => $rows,
        ];
    }

    /**
     * 提交评论
     *
     * @param array $formData 表单数据
     * @throws \Exception
     */
    public function create(array $formData = [])
    {
        $articleId = $formData['article_id'] ?? '';
        if ($articleId === '') {
            throw new ServiceException(beLang('App.Cms', 'ARTICLE.NOT_EXIST'));
        }

        $tupleArticle = Be::getTuple('cms_article');
        try {
            $tupleArticle->load($articleId);
        } catch (\Throwable $t) {
            throw new ServiceException(beLang('App.Cms', 'ARTICLE.NOT_EXIST'));
        }

        $request = Be::getRequest();
        $now = date('Y-m-d H:i:s');
        $configArticle = Be::getConfig('App.Cms.Article');

        $tupleArticleComment = Be::getTuple('cms_article_comment');
        $tupleArticleComment->article_id = $articleId;
        $tupleArticleComment->name = $formData['name'] ?? '';
        $tupleArticleComment->email = $formData['email'] ?? '';
        $tupleArticleComment->content = $formData['content'] ?? '';
        $tupleArticleComment->ip = $request->getIp();
        $tupleArticleComment->is_enable = ($configArticle->commentPublic === 1 ? 1 : -1);
        $tupleArticleComment->is_delete = 0;
        $tupleArticleComment->create_time = $now;
        $tupleArticleComment->update_time = $now;

        $tupleArticleComment->insert();
    }


}
