<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class Article
{

    /**
     * 编辑文章
     *
     * @param array $data 文章数据
     * @return bool
     * @throws \Throwable
     */
    public function edit($data)
    {
        $configCms = Be::getConfig('App.Cms.Cms');

        $db = Be::getDb($configCms->db);

        $isNew = true;
        $articleId = null;
        if (isset($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $articleId = $data['id'];
        }

        $tupleArticle = Be::newTuple('cms_article', $configCms->db);
        if (!$isNew) {
            try {
                $tupleArticle->load($articleId);
            } catch (\Throwable $t) {
                throw new ServiceException('文章（# ' . $articleId . '）不存在！');
            }
        }

        if (!isset($data['title']) || !is_string($data['title'])) {
            throw new ServiceException('文章标题未填写！');
        }
        $title = $data['title'];

        if (!isset($data['summary']) || !is_string($data['summary'])) {
            $data['summary'] = '';
        }

        if (!isset($data['description']) || !is_string($data['description'])) {
            $data['description'] = '';
        }

        $url = null;
        if (!isset($data['url']) || !is_string($data['url'])) {
            $url = strtolower($title);
            $url = str_replace(' ', '', $url);
            $url = preg_replace('/[^a-z0-9\-]/', '', $url);
        } else {
            $url = $data['url'];
        }
        $urlUnique = $url;
        $urlIndex = 0;
        $urlExist = null;
        do {
            if ($isNew) {
                $urlExist = Be::newTable('cms_article', $configCms->db)
                        ->where('url', $urlUnique)
                        ->getValue('COUNT(*)') > 0;
            } else {
                $urlExist = Be::newTable('cms_article', $configCms->db)
                        ->where('url', $urlUnique)
                        ->where('id', '!=', $articleId)
                        ->getValue('COUNT(*)') > 0;
            }

            if ($urlExist) {
                $urlIndex++;
                $urlUnique = $url . '-' . $urlIndex;
            }
        } while ($urlExist);
        $url = $urlUnique;

        if (!isset($data['image']) || !is_string($data['image'])) {
            $data['image'] = '';
        }

        if (!isset($data['seo']) || $data['seo'] !== 1) {
            $data['seo'] = 0;
        }

        if (!isset($data['seo_title']) || !is_string($data['seo_title'])) {
            $data['seo_title'] = $title;
        }

        if (!isset($data['seo_description']) || !is_string($data['seo_description'])) {
            $data['seo_description'] = $data['description'];
        }

        if (!isset($data['seo_keywords']) || !is_string($data['seo_keywords'])) {
            $data['seo_keywords'] = '';
        }

        if (!isset($data['is_enable']) || !is_numeric($data['is_enable'])) {
            $data['is_enable'] = 0;
        }

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tupleArticle->image = $data['image'];
            $tupleArticle->title = $title;
            $tupleArticle->summary = $data['summary'];
            $tupleArticle->description = $data['description'];
            $tupleArticle->url = $url;
            $tupleArticle->seo = $data['seo'];
            $tupleArticle->seo_title = $data['seo_title'];
            $tupleArticle->seo_description = $data['seo_description'];
            $tupleArticle->seo_keywords = $data['seo_keywords'];
            $tupleArticle->is_enable = $data['is_enable'];
            $tupleArticle->is_delete = 0;
            $tupleArticle->update_time = $now;
            if ($isNew) {
                $tupleArticle->create_time = $now;
                $tupleArticle->insert();
            } else {
                $tupleArticle->update();
            }

            if (isset($data['category_ids']) && is_array($data['category_ids']) && count($data['category_ids']) > 0) {
                if ($isNew) {
                    foreach ($data['category_ids'] as $category_id) {
                        $tupleArticleCategory = Be::newTuple('cms_article_category', $configCms->db);
                        $tupleArticleCategory->article_id = $tupleArticle->id;
                        $tupleArticleCategory->category_id = $category_id;
                        $tupleArticleCategory->insert();
                    }
                } else {
                    $existCategoryIds = Be::newTable('cms_article_category', $configCms->db)
                        ->where('article_id', $articleId)
                        ->getValues('category_id');

                    // 需要删除的分类
                    if (count($existCategoryIds) > 0) {
                        $removeCategoryIds = array_diff($existCategoryIds, $data['category_ids']);
                        if (count($removeCategoryIds) > 0) {
                            Be::newTable('cms_article_category', $configCms->db)
                                ->where('article_id', $articleId)
                                ->where('category_id', 'NOT IN', $removeCategoryIds)
                                ->delete();
                        }
                    }

                    // 新增的分类
                    $newCategoryIds = null;
                    if (count($existCategoryIds) > 0) {
                        $newCategoryIds = array_diff($data['category_ids'], $existCategoryIds);
                    } else {
                        $newCategoryIds = $data['category_ids'];
                    }
                    if (count($newCategoryIds) > 0) {
                        foreach ($newCategoryIds as $category_id) {
                            $tupleArticleCategory = Be::newTuple('cms_article_category', $configCms->db);
                            $tupleArticleCategory->article_id = $tupleArticle->id;
                            $tupleArticleCategory->category_id = $category_id;
                            $tupleArticleCategory->insert();
                        }
                    }
                }
            }

            // 标签
            if (isset($data['tags']) && is_array($data['tags']) && count($data['tags']) > 0) {
                if ($isNew) {
                    foreach ($data['tags'] as $tag) {
                        $tupleArticleTag = Be::newTuple('cms_article_tag', $configCms->db);
                        $tupleArticleTag->article_id = $tupleArticle->id;
                        $tupleArticleTag->tag = $tag;
                        $tupleArticleTag->insert();
                    }
                } else {
                    $existTags = Be::newTable('cms_article_tag', $configCms->db)
                        ->where('article_id', $articleId)
                        ->getValues('tag');

                    // 需要删除的标签
                    if (count($existTags) > 0) {
                        $removeTags = array_diff($existTags, $data['tags']);
                        if (count($removeTags) > 0) {
                            Be::newTable('cms_article_tag', $configCms->db)
                                ->where('article_id', $articleId)
                                ->where('tag', 'NOT IN', $removeTags)
                                ->delete();
                        }
                    }

                    // 新增的标签
                    $newTags = null;
                    if (count($existTags) > 0) {
                        $newTags = array_diff($data['tags'], $existTags);
                    } else {
                        $newTags = $data['tags'];
                    }
                    if (count($newTags) > 0) {
                        foreach ($newTags as $newTag) {
                            $tupleArticleTag = Be::newTuple('cms_article_tag', $configCms->db);
                            $tupleArticleTag->article_id = $tupleArticle->id;
                            $tupleArticleTag->tag = $newTag;
                            $tupleArticleTag->insert();
                        }
                    }
                }
            }

            $db->commit();

            $this->onUpdate([$tupleArticle->id]);

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '文章发生异常！');
        }

        return true;
    }

    /**
     * 获取文章
     *
     * @param $articleId
     * @param array $with
     * @return mixed
     */
    public function getArticle($articleId, $with = [])
    {
        $configCms = Be::getConfig('App.Cms.Cms');

        $db = Be::getDb($configCms->db);

        $sql = 'SELECT * FROM `cms_article` WHERE id=?';
        $article = $db->getObject($sql, [$articleId]);
        if (!$article) {
            throw new ServiceException('文章（# ' . $articleId . '）不存在！');
        }

        $article->seo = (int)$article->seo;
        $article->ordering = (int)$article->ordering;
        $article->is_enable = (int)$article->is_enable;
        $article->is_delete = (int)$article->is_delete;

        if (isset($with['categories'])) {
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
        }

        if (isset($with['tags'])) {
            $sql = 'SELECT tag FROM cms_article_tag WHERE article_id = ?';
            $article->tags = $db->getValues($sql, [$articleId]);
        }

        return $article;
    }


    /**
     * 文章更新
     *
     * @param array $articleIds 文章ID列表
     */
    public function onUpdate(array $articleIds)
    {
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configEs->enable) {
            $this->syncEs($articleIds);
        }

        $this->syncRedis($articleIds);
    }

    /**
     * 文章同步到 ES
     *
     * @param array $articleId
     */
    public function syncEs(array $articleIds)
    {

    }

    /**
     * 文章同步到 Redis
     *
     * @param array $articleId
     */
    public function syncRedis(array $articleIds)
    {
        $keyValues = [];
        foreach ($articleIds as $articleId) {
            $key = 'Cms:Article:' . $articleId;
            $article = $this->getArticle($articleId);
            $keyValues[$key] = $article;
        }

        $cache = Be::getCache();
        $cache->setMany($keyValues);
    }

}
