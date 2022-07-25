<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class TaskArticle
{

    /**
     * 同步到 ES
     *
     * @param array $articles
     */
    public function syncEs(array $articles)
    {
        if (count($articles) === 0) return;

        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return;
        }

        $es = Be::getEs();
        $db = Be::getDb();

        $batch = [];
        foreach ($articles as $article) {
            $article->is_enable = (int)$article->is_enable;

            // 采集的文章，不处理
            if ($article->is_enable === -1) {
                continue;
            }

            $article->is_delete = (int)$article->is_delete;

            $batch[] = [
                'index' => [
                    '_index' => $configEs->indexArticle,
                    '_id' => $article->id,
                ]
            ];

            if ($article->is_delete === 1) {
                $batch[] = [
                    'id' => $article->id,
                    'is_delete' => true
                ];
            } else {

                $categories = [];
                $sql = 'SELECT category_id FROM cms_article_category WHERE article_id = ?';
                $categoryIds = $db->getValues($sql, [$article->id]);
                if (count($categoryIds) > 0) {
                    $sql = 'SELECT id, `name` FROM cms_category WHERE id IN (\'' . implode('\',\'', $categoryIds) . '\')';
                    $categories = $db->getObjects($sql);
                }

                $sql = 'SELECT tag FROM cms_article_tag WHERE article_id = ?';
                $tags = $db->getValues($sql, [$article->id]);

                $batch[] = [
                    'id' => $article->id,
                    'image' => $article->image,
                    'title' => $article->title,
                    'summary' => $article->summary,
                    'description' => $article->description,
                    'url' => $article->url,
                    'author' => $article->author,
                    'publish_time' => $article->publish_time,
                    'ordering' => (int)$article->ordering,
                    'hits' => (int)$article->hits,
                    'is_enable' => $article->is_enable === 1,
                    'is_delete' => $article->is_delete === 1,
                    'create_time' => $article->create_time,
                    'update_time' => $article->update_time,
                    'categories' => $categories,
                    'tags' => $tags,
                ];
            }
        }

        $response = $es->bulk(['body' => $batch]);
        if ($response['errors'] > 0) {
            $reason = '';
            if (isset($response['items']) && count($response['items']) > 0) {
                foreach ($response['items'] as $item) {
                    if (isset($item['index']['error']['reason'])) {
                        $reason = $item['index']['error']['reason'];
                        break;
                    }
                }
            }
            throw new ServiceException('文章全量量同步到ES出错：' . $reason);
        }
    }

    /**
     * 文章同步到 Redis
     *
     * @param array $articles
     */
    public function syncCache(array $articles)
    {
        if (count($articles) === 0) return;

        $db = Be::getDb();
        $cache = Be::getCache();
        $keyValues = [];
        foreach ($articles as $article) {

            $article->is_enable = (int)$article->is_enable;

            // 采集的商品，不处理
            if ($article->is_enable === -1) {
                continue;
            }

            $key = 'Cms:Article:' . $article->id;

            $article->is_delete = (int)$article->is_delete;

            if ($article->is_delete === 1) {
                $cache->delete($key);
            } else {

                $article->url_custom = (int)$article->url_custom;
                $article->seo_title_custom = (int)$article->seo_title_custom;
                $article->seo_description_custom = (int)$article->seo_description_custom;
                $article->ordering = (int)$article->ordering;

                $sql = 'SELECT category_id FROM cms_article_category WHERE article_id = ?';
                $category_ids = $db->getValues($sql, [$article->id]);
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
                $article->tags = $db->getValues($sql, [$article->id]);

                $keyValues[$key] = $article;
            }
        }

        if (count($keyValues) > 0) {
            $cache->setMany($keyValues);
        }

    }


}
