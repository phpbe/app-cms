<?php
namespace Be\App\Cms\Controller;

use Be\App\ControllerException;
use Be\App\ServiceException;
use Be\Be;

/**
 * 接口
 */
class Api
{

    /**
     * 采集接口
     *
     * @BeRoute("/cms/api/collect/article")
     */
    public function CollectArticle()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $db = Be::getDb();
        $db->startTransaction();
        try {
            $configCollectArticleApi = Be::getConfig('App.Cms.CollectArticleApi');
            if ($configCollectArticleApi->enable === 0) {
                throw new ControllerException('采集接口未启用！');
            }

            $token = $request->get('token', '');
            if ($configCollectArticleApi->token !== $token) {
                throw new ControllerException('密钥错误！');
            }

            $title = $request->post('title', '');
            if ($title === '') {
                $categoryKeyValues = Be::getService('App.Cms.Admin.Category')->getCategoryKeyValues();
                $response->set('categories', $categoryKeyValues);
                $response->json();
                return;
            }

            $uniqueKey = $request->post('unique_key', '');

            $data = [];

            $tupleCollectArticle = Be::getTuple('cms_collect_article');

            $collectArticleExist = false;
            if ($uniqueKey !== '') {
                if (mb_strlen($uniqueKey) > 200) {
                    throw new ServiceException('唯一值（unique_key）不得超过200个字符！');
                }

                try {
                    $tupleCollectArticle->loadBy([
                        'unique_key' => $uniqueKey,
                    ]);

                    $collectArticleExist = true;
                } catch (\Throwable $t) {
                }

                if ($collectArticleExist) {
                    $tupleArticle = Be::getTuple('cms_article');
                    try {
                        $tupleArticle->load($tupleCollectArticle->article_id);

                        $data['id'] = $tupleCollectArticle->article_id;
                    } catch (\Throwable $t) {
                        throw new ServiceException('唯一键值（unique_key=' . $uniqueKey . '）对应的文章异常！');
                    }

                    if ($tupleArticle->is_enable !== -1) {
                        throw new ServiceException('唯一键值（unique_key=' . $uniqueKey . '）对应的文章已发布！');
                    }
                }
            }

            $now = date('Y-m-d H:i:s');
            $tupleCollectArticle->update_time = $now;
            if ($collectArticleExist) {
                $tupleCollectArticle->update();
            } else {
                $tupleCollectArticle->unique_key = $uniqueKey;
                $tupleCollectArticle->article_id = '';
                $tupleCollectArticle->create_time = $now;
                $tupleCollectArticle->insert();
            }

            $data['collect_article_id'] = $tupleCollectArticle->id;

            $data['image'] = $request->post('image', '');

            $data['title'] = $title;
            if (mb_strlen($data['title']) > 200) {
                throw new ServiceException('采集的文章标题（title）不得超过200个字符！');
            }

            $data['summary'] = $request->post('summary', '');
            if ($data['summary'] && mb_strlen($data['summary']) > 500) {
                throw new ServiceException('摘要（summary）不得超过500个字符！');
            }

            $data['description'] = $request->post('description', '', 'html');

            $data['author'] = $request->post('author', '');
            if ($data['author'] && mb_strlen($data['author']) > 50) {
                throw new ServiceException('作者（author）不得超过50个字符！');
            }

            $data['publish_time'] = $request->post('publish_time', '');
            if (!strtotime($data['publish_time'])) {
                $data['publish_time'] = date('Y-m-d H:i:s');
            }

            $tags = $request->post('tags', '');
            if ($tags) {
                $tags = explode('|', $tags);
                $tagsData = [];
                foreach ($tags as $tag) {
                    $tagsData[] = [
                        'id' => '',
                        'tag' => $tag,
                    ];
                }
                $data['tags'] = $tagsData;
            } else {
                $data['tags'] = [];
            }

            $data['is_enable'] = -1; // 采集的文章标记

            $article = Be::getService('App.Cms.Admin.Article')->edit($data);

            if (!$collectArticleExist) {
                $tupleCollectArticle->article_id = $article->id;
                $tupleCollectArticle->update_time = date('Y-m-d H:i:s');
                $tupleCollectArticle->update();
            }

            $db->commit();

            $response->end('[OK] 数据已接收！');
        } catch (\Throwable $t) {
            $db->rollback();

            $response->end('[ERROR] ' . $t->getMessage());
        }

    }

}
