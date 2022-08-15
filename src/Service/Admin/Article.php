<?php

namespace Be\App\Cms\Service\Admin;

use Be\AdminPlugin\Form\Item\FormItemSelect;
use Be\AdminPlugin\Table\Item\TableItemImage;
use Be\App\ServiceException;
use Be\Be;
use Be\Db\DbException;
use Be\Db\Tuple;
use Be\Runtime\RuntimeException;
use Be\Util\Str\Pinyin;

class Article
{

    /**
     * 编辑文章
     *
     * @param array $data 文章数据
     * @return Tuple
     * @throws ServiceException
     * @throws DbException|RuntimeException
     */
    public function edit(array $data): Tuple
    {
        $db = Be::getDb();

        $isNew = true;
        $articleId = null;
        if (isset($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $articleId = $data['id'];
        }

        $tupleArticle = Be::getTuple('cms_article');
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

        if (!isset($data['author']) || !is_string($data['author'])) {
            $data['author'] = '';
        }

        if (!isset($data['publish_time']) || !is_string($data['publish_time']) || strtotime($data['publish_time']) === false) {
            $data['publish_time'] = date('Y-m-d H:i:s');
        }

        if (!isset($data['url_custom']) || $data['url_custom'] !== 1) {
            $data['url_custom'] = 0;
        }

        $url = null;
        if (!isset($data['url']) || !is_string($data['url'])) {
            $urlTitle = strtolower($title);
            $url = Pinyin::convert($urlTitle, '-');
            if (strlen($url) > 200) {
                $url = Pinyin::convert($urlTitle, '-', true);
                if (strlen($url) > 200) {
                    $url = Pinyin::convert($urlTitle, '', true);
                }
            }

            $data['url_custom'] = 0;
        } else {
            $url = $data['url'];
        }
        $urlUnique = $url;
        $urlIndex = 0;
        $urlExist = null;
        do {
            if ($isNew) {
                $urlExist = Be::getTable('cms_article')
                        ->where('url', $urlUnique)
                        ->getValue('COUNT(*)') > 0;
            } else {
                $urlExist = Be::getTable('cms_article')
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

        if (!isset($data['seo_title']) || !is_string($data['seo_title'])) {
            $data['seo_title'] = $title;
        }

        if (!isset($data['seo_title_custom']) || !is_numeric($data['seo_title_custom']) || $data['seo_title_custom'] !== 1) {
            $data['seo_title_custom'] = 0;
        }

        if (!isset($data['seo_description']) || !is_string($data['seo_description'])) {
            $data['seo_description'] = '';
        }
        $data['seo_description'] = strip_tags($data['seo_description']);

        if (!isset($data['seo_description_custom']) || !is_numeric($data['seo_description_custom']) || $data['seo_description_custom'] !== 1) {
            $data['seo_description_custom'] = 0;
        }

        if (!isset($data['seo_keywords']) || !is_string($data['seo_keywords'])) {
            $data['seo_keywords'] = '';
        }

        if (!isset($data['is_push_home']) || !is_numeric($data['is_push_home'])) {
            $data['is_push_home'] = 0;
        }

        if (!isset($data['is_on_top']) || !is_numeric($data['is_on_top'])) {
            $data['is_on_top'] = 0;
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
            $tupleArticle->url_custom = $data['url_custom'];
            $tupleArticle->author = $data['author'];
            $tupleArticle->publish_time = $data['publish_time'];
            $tupleArticle->seo_title = $data['seo_title'];
            $tupleArticle->seo_title_custom = $data['seo_title_custom'];
            $tupleArticle->seo_description = $data['seo_description'];
            $tupleArticle->seo_description_custom = $data['seo_description_custom'];
            $tupleArticle->seo_keywords = $data['seo_keywords'];
            $tupleArticle->is_push_home = $data['is_push_home'];
            $tupleArticle->is_on_top = $data['is_on_top'];
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
                        $tupleArticleCategory = Be::getTuple('cms_article_category');
                        $tupleArticleCategory->article_id = $tupleArticle->id;
                        $tupleArticleCategory->category_id = $category_id;
                        $tupleArticleCategory->insert();
                    }
                } else {
                    $existCategoryIds = Be::getTable('cms_article_category')
                        ->where('article_id', $articleId)
                        ->getValues('category_id');

                    // 需要删除的分类
                    if (count($existCategoryIds) > 0) {
                        $removeCategoryIds = array_diff($existCategoryIds, $data['category_ids']);
                        if (count($removeCategoryIds) > 0) {
                            Be::getTable('cms_article_category')
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
                            $tupleArticleCategory = Be::getTuple('cms_article_category');
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
                        $tupleArticleTag = Be::getTuple('cms_article_tag');
                        $tupleArticleTag->article_id = $tupleArticle->id;
                        $tupleArticleTag->tag = $tag;
                        $tupleArticleTag->insert();
                    }
                } else {
                    $existTags = Be::getTable('cms_article_tag')
                        ->where('article_id', $articleId)
                        ->getValues('tag');

                    // 需要删除的标签
                    if (count($existTags) > 0) {
                        $removeTags = array_diff($existTags, $data['tags']);
                        if (count($removeTags) > 0) {
                            Be::getTable('cms_article_tag')
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
                            $tupleArticleTag = Be::getTuple('cms_article_tag');
                            $tupleArticleTag->article_id = $tupleArticle->id;
                            $tupleArticleTag->tag = $newTag;
                            $tupleArticleTag->insert();
                        }
                    }
                }
            }

            $db->commit();

            Be::getService('App.System.Task')->trigger('Cms.ArticleSyncEsAndCache');

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '文章发生异常！');
        }

        return $tupleArticle;
    }

    /**
     * 获取文章
     *
     * @param string $articleId
     * @param array $with
     * @return object
     * @throws ServiceException
     * @throws DbException|RuntimeException
     */
    public function getArticle(string $articleId, array $with = []): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM `cms_article` WHERE id=?';
        $article = $db->getObject($sql, [$articleId]);
        if (!$article) {
            throw new ServiceException('文章（# ' . $articleId . '）不存在！');
        }

        $article->url_custom = (int)$article->url_custom;
        $article->seo_title_custom = (int)$article->seo_title_custom;
        $article->seo_description_custom = (int)$article->seo_description_custom;
        $article->ordering = (int)$article->ordering;
        $article->is_push_home = (int)$article->is_push_home;
        $article->is_on_top = (int)$article->is_on_top;
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
     * 获取菜单参数选择器
     *
     * @return array
     */
    public function getArticleMenuPicker(): array
    {
        $categoryKeyValues = Be::getService('App.Cms.Admin.Category')->getCategoryKeyValues();
        return [
            'name' => 'id',
            'value' => '指定文章：{title}',
            'table' => 'cms_article',
            'grid' => [
                'title' => '选择一篇文章',

                'filter' => [
                    ['is_enable', '=', '1'],
                    ['is_delete', '=', '0'],
                ],

                'form' => [
                    'items' => [
                        [
                            'name' => 'category_id',
                            'label' => '分类',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $categoryKeyValues,
                            'buildSql' => function ($dbName, $formData) {
                                if (isset($formData['category_id']) && $formData['category_id']) {
                                    $articleIds = Be::getTable('cms_article_category', $dbName)
                                        ->where('category_id', $formData['category_id'])
                                        ->getValues('article_id');
                                    if (count($articleIds) > 0) {
                                        return ['id', 'IN', $articleIds];
                                    } else {
                                        return ['id', '=', ''];
                                    }
                                }
                                return '';
                            },
                        ],
                        [
                            'name' => 'title',
                            'label' => '标题',
                        ],
                    ],
                ],

                'table' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'name' => 'image',
                            'label' => '封面图片',
                            'width' => '90',
                            'driver' => TableItemImage::class,
                            'ui' => [
                                'style' => 'max-width: 60px; max-height: 60px'
                            ],
                            'value' => function ($row) {
                                if ($row['image'] === '') {
                                    return Be::getProperty('App.Cms')->getWwwUrl() . '/article/images/no-image.jpg';
                                }
                                return $row['image'];
                            },
                        ],
                        [
                            'name' => 'title',
                            'label' => '标题',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                    ],
                ],
            ]
        ];
    }

}
