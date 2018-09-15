<?php
namespace App\Cms\AdminController;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;
use Phpbe\System\AdminController;
use Phpbe\Util\Str;

class Article extends AdminController
{

    public function articles()
    {
        $orderBy = Request::post('orderBy', 'id');
        $orderByDir = Request::post('orderByDir', 'ASC');
        $categoryId = Request::post('categoryId', -1, 'int');
        $key = Request::post('key', '');
        $block = Request::post('block', -1, 'int');
        $limit = Request::post('limit', -1, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System', 'Admin');
            $limit = $adminConfigSystem->limit;
        }

        $serviceArticle = Be::getService('Cms', 'Article');
        $serviceArticleComment = Be::getService('Cms', 'ArticleComment');
        $serviceCategory = Be::getService('Cms', 'Category');

        Response::setTitle('文章列表');

        $option = array('categoryId' => $categoryId, 'key' => $key, 'block' => $block);

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($serviceArticle->getArticleCount($option));
        $pagination->setPage(Request::post('page', 1, 'int'));

        Response::set('pagination', $pagination);
        Response::set('orderBy', $orderBy);
        Response::set('orderByDir', $orderByDir);
        Response::set('categoryId', $categoryId);
        Response::set('key', $key);
        Response::set('block', $block);

        $option['orderBy'] = $orderBy;
        $option['orderByDir'] = $orderByDir;
        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;

        $articles = $serviceArticle->getArticles($option);
        foreach ($articles as $article) {
            $article->commentCount = $serviceArticleComment->getCommentCount(array('articleId' => $article->id));
        }
        Response::set('articles', $articles);
        Response::set('categories', $serviceCategory->getCategories());
        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save('Admin.Cms.Article.articles');
    }


    public function edit()
    {
        $id = Request::post('id', 0, 'int');

        $tupleArticle = Be::getTuple('Cms', 'Article');
        $tupleArticle->load($id);

        if ($id == 0) {
            Response::setTitle('添加文章');
        } else {
            Response::setTitle('编辑文章');
        }
        Response::set('article', $tupleArticle);

        $serviceCategory = Be::getService('Cms', 'Category');
        $categories = $serviceCategory->getCategories();
        Response::set('categories', $categories);
        Response::display();
    }


    public function editSave()
    {
        $id = Request::post('id', 0, 'int');

        $my = Be::getAdminUser();

        $tupleArticle = Be::getTuple('Cms', 'Article');
        if ($id != 0) $tupleArticle->load($id);
        $tupleArticle->bind(Request::post());

        $tupleArticle->createTime = strtotime($tupleArticle->createTime);

        $body = Request::post('body', '', 'html');

        $configSystem = Be::getConfig('System', 'System');

        // 找出内容中的所有图片
        $images = array();

        $imageTypes = implode('|', $configSystem->allowUploadImageTypes);
        preg_match_all("/src=[\\\|\"|'|\s]{0,}(http:\/\/([^>]*)\.($imageTypes))/isU", $body, $images);
        $images = array_unique($images[1]);

        // 过滤掉本服务器上的图片
        $remoteImages = array();
        if (count($images) > 0) {
            $beUrlLen = strlen(url());
            foreach ($images as $image) {
                if (substr($image, 0, $beUrlLen) != url()) {
                    $remoteImages[] = $image;
                }
            }
        }

        $thumbnailSource = Request::post('thumbnailSource', ''); // upload：上传缩图图 / url：从指定网址获取缩图片
        $thumbnailPickUp = Request::post('thumbnailPickUp', 0, 'int'); // 是否提取第一张图作为缩略图
        $downloadRemoteImage = Request::post('downloadRemoteImage', 0, 'int'); // 是否下载远程图片
        $downloadRemoteImageWatermark = Request::post('downloadRemoteImageWatermark', 0, 'int'); // 是否下截远程图片添加水印

        // 下载远程图片
        if ($downloadRemoteImage == 1) {
            if (count($remoteImages) > 0) {
                $libHttp = Be::getLib('Http');

                // 下载到本地的文件夹
                $dirName = date('Y-m-d');
                $dirPath = Be::getRuntime()->getDataPath() . '/Cms/Article/' .  $dirName;

                // 文件夹不存在时自动创建
                if (!file_exists($dirPath)) {
                    $libFso = Be::getLib('Fso');
                    $libFso->mkDir($dirPath);
                }

                $t = date('YmdHis');
                $i = 0;
                foreach ($remoteImages as $remoteImage) {
                    $localImageName = $t . $i . '.' . strtolower(substr(strrchr($remoteImage, '.'), 1));
                    $data = $libHttp->get($remoteImage);

                    file_put_contents($dirPath . '/' . $localImageName, $data);

                    // 下截远程图片添加水印
                    if ($downloadRemoteImageWatermark == 1) {
                        $serviceSystem = Be::getService('System', 'Admin');
                        $serviceSystem->watermark($dirPath . '/' . $localImageName);
                    }

                    $body = str_replace($remoteImage, '/' . DATA . '/Article/' . $dirName . '/' . $localImageName, $body);
                    $i++;
                }
            }
        }
        $tupleArticle->body = $body;

        $configArticle = Be::getConfig('Cms', 'Article');

        // 提取第一张图作为缩略图
        if ($thumbnailPickUp == 1) {
            if (count($images) > 0) {

                $libHttp = Be::getLib('\GuzzleHttp\Client');
                $response = $libHttp->request('GET', $images[0]);
                if ($response->getStatusCode() == 200) {
                    $data = $response->getBody();
                    if ($data) {
                        $tmpImage = Be::getRuntime()->getDataPath() . '/Tmp/' .  date('YmdHis') . '.' . strtolower(substr(strrchr($images[0], '.'), 1));
                        file_put_contents($tmpImage, $data);

                        $libImage = Be::getLib('image');
                        $libImage->open($tmpImage);

                        if ($libImage->isImage()) {
                            $t = date('YmdHis');
                            $dir = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail';
                            if (!file_exists($dir)) {
                                $libFso = Be::getLib('Fso');
                                $libFso->mkDir($dir);
                            }

                            $thumbnailLName = $t . '_l.' . $libImage->getType();
                            $libImage->resize($configArticle->thumbnailLW, $configArticle->thumbnailLH, 'scale');
                            $libImage->save($dir . '/' . $thumbnailLName);
                            $tupleArticle->thumbnail_l = $thumbnailLName;

                            $thumbnailMName = $t . '_m.' . $libImage->getType();
                            $libImage->resize($configArticle->thumbnailMW, $configArticle->thumbnailMH, 'scale');
                            $libImage->save($dir . '/' . $thumbnailMName);
                            $tupleArticle->thumbnail_m = $thumbnailMName;

                            $thumbnailSName = $t . '_s.' . $libImage->getType();
                            $libImage->resize($configArticle->thumbnailSW, $configArticle->thumbnailSH, 'scale');
                            $libImage->save($dir . '/' . $thumbnailSName);
                            $tupleArticle->thumbnail_s = $thumbnailSName;
                        }

                        @unlink($tmpImage);
                    }
                }
            }
        } else {
            // 上传缩图图
            if ($thumbnailSource == 'upload') {
                $thumbnailUpload = $_FILES['thumbnailUpload'];
                if ($thumbnailUpload['error'] == 0) {
                    $libImage = Be::getLib('image');
                    $libImage->open($thumbnailUpload['tmpName']);
                    if ($libImage->isImage()) {
                        $t = date('YmdHis');
                        $dir = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail';
                        if (!file_exists($dir)) {
                            $libFso = Be::getLib('Fso');
                            $libFso->mkDir($dir);
                        }

                        $thumbnailLName = $t . '_l.' . $libImage->getType();
                        $libImage->resize($configArticle->thumbnailLW, $configArticle->thumbnailLH, 'scale');
                        $libImage->save($dir . '/' . $thumbnailLName);
                        $tupleArticle->thumbnail_l = $thumbnailLName;

                        $thumbnailMName = $t . '_m.' . $libImage->getType();
                        $libImage->resize($configArticle->thumbnailMW, $configArticle->thumbnailMH, 'scale');
                        $libImage->save($dir . '/' . $thumbnailMName);
                        $tupleArticle->thumbnail_m = $thumbnailMName;

                        $thumbnailSName = $t . '_s.' . $libImage->getType();
                        $libImage->resize($configArticle->thumbnailSW, $configArticle->thumbnailSH, 'scale');
                        $libImage->save($dir . '/' . $thumbnailSName);
                        $tupleArticle->thumbnail_s = $thumbnailSName;
                    }
                }
            } elseif ($thumbnailSource == 'url') { // 从指定网址获取缩图片
                $thumbnailUrl = Request::post('thumbnailUrl', '');
                if ($thumbnailUrl != '' && substr($thumbnailUrl, 0, 7) == 'http://') {

                    $libHttp = Be::getLib('\GuzzleHttp\Client');
                    $response = $libHttp->request('GET', $thumbnailUrl);
                    if ($response->getStatusCode() == 200) {
                        $data = $response->getBody();
                        if ($data) {
                            $tmpImage = Be::getRuntime()->getDataPath() . '/Tmp/' .  date('YmdHis') . '.' . strtolower(substr(strrchr($thumbnailUrl, '.'), 1));
                            file_put_contents($tmpImage, $data);

                            $libImage = Be::getLib('image');
                            $libImage->open($tmpImage);

                            if ($libImage->isImage()) {
                                $t = date('YmdHis');
                                $dir = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail';
                                if (!file_exists($dir)) {
                                    $libFso = Be::getLib('Fso');
                                    $libFso->mkDir($dir);
                                }

                                $thumbnailLName = $t . '_l.' . $libImage->getType();
                                $libImage->resize($configArticle->thumbnailLW, $configArticle->thumbnailLH, 'scale');
                                $libImage->save($dir . '/' . $thumbnailLName);
                                $tupleArticle->thumbnail_l = $thumbnailLName;

                                $thumbnailMName = $t . '_m.' . $libImage->getType();
                                $libImage->resize($configArticle->thumbnailMW, $configArticle->thumbnailMH, 'scale');
                                $libImage->save($dir . '/' . $thumbnailMName);
                                $tupleArticle->thumbnail_m = $thumbnailMName;

                                $thumbnailSName = $t . '_s.' . $libImage->getType();
                                $libImage->resize($configArticle->thumbnailSW, $configArticle->thumbnailSH, 'scale');
                                $libImage->save($dir . '/' . $thumbnailSName);
                                $tupleArticle->thumbnail_s = $thumbnailSName;
                            }

                            @unlink($tmpImage);
                        }
                    }

                }
            }
        }


        if ($id == 0) {
            $tupleArticle->create_by_id = $my->id;
        } else {
            $tupleArticle->modify_time = time();
            $tupleArticle->modify_by_id = $my->id;
        }

        if ($tupleArticle->save()) {
            if ($id == 0) {
                Response::setMessage('添加文章成功！');
                systemLog('添加文章：#' . $tupleArticle->id . ': ' . $tupleArticle->title);
            } else {
                Response::setMessage('修改文章成功！');
                systemLog('编辑文章：#' . $id . ': ' . $tupleArticle->title);
            }
        } else {
            Response::setMessage($tupleArticle->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back('Admin.Cms.Article.articles');
    }


    public function unblock()
    {
        $ids = Request::post('id', '');
        try {
            $serviceArticle = Be::getService('Cms', 'Article');
            $serviceArticle->unblock($ids);
            Response::setMessage('公开文章成功！');
            systemLog('公开文章：#' . $ids);
        } catch (\Exception $e) {
            Response::setMessage($e->getMessage(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back('Admin.Cms.Article.articles');
    }

    public function block()
    {
        $ids = Request::post('id', '');
        try {
            $serviceArticle = Be::getService('Cms', 'Article');
            $serviceArticle->block($ids);
            Response::setMessage('屏蔽文章成功！');
            systemLog('屏蔽文章：#' . $ids);
        } catch (\Exception $e) {
            Response::setMessage($e->getMessage(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back('Admin.Cms.Article.articles');
    }

    public function delete()
    {
        $ids = Request::post('id', '');

        try {
            $serviceArticle = Be::getService('Cms', 'Article');
            $serviceArticle->delete($ids);
            Response::setMessage('删除文章成功！');
            systemLog('删除文章：#' . $ids);
        } catch (\Exception $e) {
            Response::setMessage($e->getMessage(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back('Admin.Cms.Article.articles');
    }


    private function cleanHtml($html)
    {
        $html = trim($html);
        $html = strip_tags($html);
        $html = str_replace(array('&nbsp;', '&ldquo;', '&rdquo;', '　'), '', $html);
        $html = preg_replace("/\t/", "", $html);
        $html = preg_replace("/\r\n/", "", $html);
        $html = preg_replace("/\r/", "", $html);
        $html = preg_replace("/\n/", "", $html);
        $html = preg_replace("/ /", "", $html);
        return $html;
    }

    // 从内容中提取摘要
    public function ajaxGetSummary()
    {
        $body = $this->cleanHtml($_POST['body']);

        $configArticle = Be::getConfig('Cms', 'Article');

        Response::set('error', 0);
        Response::set('summary', Str::limit($body, intval($configArticle->getSummary)));
        Response::ajax();
    }


    // 从内容中提取 META 关键字
    public function ajaxGetMetaKeywords()
    {
        $body = $this->cleanHtml($_POST['body']);

        $configArticle = Be::getConfig('Cms', 'Article');

        $libPscws = Be::getLib('Pscws');
        $libPscws->sendText($body);
        $keywords = $libPscws->getTops(intval($configArticle->getMetaKeywords));
        $metaKeywords = '';
        if ($keywords !== false) {
            $tmpMetaKeywords = array();
            foreach ($keywords as $keyword) {
                $tmpMetaKeywords[] = $keyword['word'];
            }
            $metaKeywords = implode(' ', $tmpMetaKeywords);
        }

        Response::set('error', 0);
        Response::set('metaKeywords', $metaKeywords);
        Response::ajax();
    }

    // 从内容中提取 META 描述
    public function ajaxGetMetaDescription()
    {
        $body = $this->cleanHtml($_POST['body']);

        $configArticle = Be::getConfig('Cms', 'Article');

        Response::set('error', 0);
        Response::set('metaDescription', Str::limit($body, intval($configArticle->getMetaDescription)));
        Response::ajax();
    }

    public function comments()
    {
        $orderBy = Request::post('orderBy', 'createTime');
        $orderByDir = Request::post('orderByDir', 'DESC');
        $articleId = Request::post('articleId', 0, 'int');
        $key = Request::post('key', '');
        $status = Request::post('status', -1, 'int');
        $limit = Request::post('limit', -1, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System', 'admin');
            $limit = $adminConfigSystem->limit;
        }

        $adminServiceArticle = Be::getService('Cms', 'Article');
        Response::setTitle('评论列表');

        $option = array('articleId' => $articleId, 'key' => $key, 'status' => $status);

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceArticle->getCommentCount($option));
        $pagination->setPage(Request::post('page', 1, 'int'));

        Response::set('pagination', $pagination);
        Response::set('orderBy', $orderBy);
        Response::set('orderByDir', $orderByDir);
        Response::set('key', $key);
        Response::set('status', $status);

        Response::set('articleId', $articleId);

        $option['orderBy'] = $orderBy;
        $option['orderByDir'] = $orderByDir;
        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;

        $articles = array();
        $comments = $adminServiceArticle->getComments($option);
        foreach ($comments as $comment) {
            if (!array_key_exists($comment->articleId, $articles)) {
                $tupleArticle = Be::getTuple('Cms', 'article');
                $tupleArticle->load($comment->articleId);
                $articles[$comment->articleId] = $tupleArticle;
            }

            $comment->article = $articles[$comment->articleId];
        }

        Response::set('comments', $comments);
        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save('Admin.Cms.Article.comments');
    }

    public function commentsUnblock()
    {
        $ids = Request::post('id', '');

        try {
            $serviceArticle = Be::getService('Cms', 'Article');
            $serviceArticle->commentsUnblock($ids);
            Response::setMessage('公开评论成功！');
            systemLog('公开文章评论：#' . $ids);
        } catch (\Exception $e) {
            Response::setMessage($e->getMessage(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back('Admin.Cms.Article.comments');
    }

    public function commentsBlock()
    {
        $ids = Request::post('id', '');

        try {
            $serviceArticle = Be::getService('Cms', 'Article');
            $serviceArticle->commentsBlock($ids);
            Response::setMessage('屏蔽评论成功！');
            systemLog('屏蔽文章评论：#' . $ids);
        } catch (\Exception $e) {
            Response::setMessage($e->getMessage(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back('Admin.Cms.Article.comments');
    }

    public function commentsDelete()
    {
        $ids = Request::post('id', '');

        try {
            $serviceArticle = Be::getService('Cms', 'Article');
            $serviceArticle->commentsDelete($ids);
            Response::setMessage('删除评论成功！');
            systemLog('删除文章评论：#' . $ids . ')');
        } catch (\Exception $e) {
            Response::setMessage($e->getMessage(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back('Admin.Cms.Article.comments');
    }

}
