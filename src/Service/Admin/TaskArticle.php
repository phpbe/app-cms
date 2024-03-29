<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;
use Be\Task\TaskException;
use Be\Util\File\FileSize;
use Be\Util\File\Mime;
use Be\Util\Net\Curl;

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

        $configSystemEs = Be::getConfig('App.System.Es');
        $configEs = Be::getConfig('App.Cms.Es');
        if ($configSystemEs->enable === 0 || $configEs->enable === 0) {
            return;
        }

        $es = Be::getEs();
        $db = Be::getDb();

        $batch = [];
        foreach ($articles as $article) {

            // 采集的文章，不处理
            if ($article->is_enable === '-1') {
                continue;
            }

            if ($article->is_delete !== '0' || $article->is_enable !== '1') {
                $params = [
                    'body' => [
                        'index' => $configEs->indexArticle,
                        'id' => $article->id,
                    ]
                ];

                $es->delete($params);

            } else {

                $categories = [];
                $sql = 'SELECT category_id FROM cms_article_category WHERE article_id = ?';
                $categoryIds = $db->getValues($sql, [$article->id]);
                if (count($categoryIds) > 0) {
                    $sql = 'SELECT id, `name` FROM cms_category WHERE is_delete=0 AND id IN (\'' . implode('\',\'', $categoryIds) . '\') ORDER BY ordering ASC';
                    $categories = $db->getObjects($sql);
                }

                $sql = 'SELECT tag FROM cms_article_tag WHERE article_id = ?';
                $tags = $db->getValues($sql, [$article->id]);


                $batch[] = [
                    'index' => [
                        '_index' => $configEs->indexArticle,
                        '_id' => $article->id,
                    ]
                ];

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
                    'is_push_home' => $article->is_push_home === '1',
                    'is_on_top' => $article->is_on_top === '1',
                    //'is_enable' => $article->is_enable === '1',
                    //'is_delete' => $article->is_delete === '1',
                    //'create_time' => $article->create_time,
                    //'update_time' => $article->update_time,
                    'categories' => $categories,
                    'tags' => $tags,
                ];
            }
        }

        if (count($batch) > 0) {
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
    }

    /**
     * 文章同步到缓存
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

            // 采集的商品，不处理
            if ($article->is_enable === '-1') {
                continue;
            }

            $key = 'App:Cms:Article:' . $article->id;

            if ($article->is_delete !== '0' || $article->is_enable !== '1') {
                $cache->delete($key);
            } else {
                $categories = [];
                $sql = 'SELECT category_id FROM cms_article_category WHERE article_id = ?';
                $categoryIds = $db->getValues($sql, [$article->id]);
                if (count($categoryIds) > 0) {
                    $sql = 'SELECT id, `name` FROM cms_category WHERE is_delete=0 AND id IN (\'' . implode('\',\'', $categoryIds) . '\') ORDER BY ordering ASC';
                    $categories = $db->getObjects($sql);
                }
                $article->categories = $categories;
                $article->category_ids = array_column($categories, 'id');

                $sql = 'SELECT tag FROM cms_article_tag WHERE article_id = ?';
                $article->tags = $db->getValues($sql, [$article->id]);

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

                $keyValues[$key] = $newArticle;
            }
        }

        if (count($keyValues) > 0) {
            $cache->setMany($keyValues);
        }
    }

    /**
     * 下载远程图片
     *
     * @param object $article 文章
     */
    public function downloadRemoteImages(object $article)
    {
        $storageRootUrl = Be::getStorage()->getRootUrl();
        $storageRootUrlLen = strlen($storageRootUrl);
        $imageKeyValues = [];

        $hasChange = false;
        $updateObj = new \stdClass();
        $updateObj->id = $article->id;
        $updateObj->download_remote_image = 2;
        $updateObj->update_time = date('Y-m-d H:i:s');
        Be::getDb()->update('cms_article', $updateObj, 'id');


        $article->image = trim($article->image);
        if ($article->image !== '') {
            if (strlen($article->image) < $storageRootUrlLen || substr($article->image, 0, $storageRootUrlLen) !== $storageRootUrl) {
                $storageImage = false;
                try {
                    $storageImage = $this->downloadRemoteImage($article, $article->image);
                } catch (\Throwable $t) {
                    Be::getLog()->error($t);
                }

                if ($storageImage) {
                    $imageKeyValues[$article->image] = $storageImage;

                    $updateObj->image = $storageImage;
                    $hasChange = true;
                }
            }
        }

        $descriptionHasChange = false;
        $descriptionImages = [];

        $configSystem = Be::getConfig('App.System.System');
        $reg = '/ src=\"([^\"]*\.(' . implode('|', $configSystem->allowUploadImageTypes) . ')[^\"]*)\"/is';
        if (preg_match_all($reg, $article->description, $descriptionImages)) {
            $i = 0;
            foreach ($descriptionImages[1] as $descriptionImage) {
                $descriptionImage = trim($descriptionImage);
                if ($descriptionImage !== '') {
                    if (strlen($descriptionImage) < $storageRootUrlLen || substr($descriptionImage, 0, $storageRootUrlLen) !== $storageRootUrl) {
                        $storageImage = false;
                        if (isset($imageKeyValues[$descriptionImage])) {
                            $storageImage = $imageKeyValues[$descriptionImage];
                        } else {
                            try {
                                $storageImage = $this->downloadRemoteImage($article, $descriptionImage);
                            } catch (\Throwable $t) {
                                Be::getLog()->error($t);
                            }
                        }

                        if ($storageImage) {
                            $imageKeyValues[$descriptionImage] = $storageImage;

                            $replaceFrom = $descriptionImages[0][$i];
                            $replaceTo = str_replace($descriptionImage, $storageImage, $replaceFrom);
                            $article->description = str_replace($replaceFrom, $replaceTo, $article->description);
                            $descriptionHasChange = true;
                        }
                    }
                }

                $i++;
            }

            if ($descriptionHasChange) {
                $updateObj->description = $article->description;
                $hasChange = true;
            }
        }

        if ($hasChange) {
            $updateObj->download_remote_image = 10;
            $updateObj->update_time = date('Y-m-d H:i:s');
            Be::getDb()->update('cms_article', $updateObj, 'id');
        }
    }

    /**
     * 下载远程图片
     *
     * @param object $article 文章
     */
    public function downloadRemoteImage(object $article, string $remoteImage)
    {
        $configDownloadRemoteImage = Be::getConfig('App.Cms.DownloadRemoteImage');

        // 示例：https://cdn.shopify.com/s/files/1/0139/8942/products/Womens-Zamora-Jogger-Scrub-Pant_martiniolive-4.jpg
        $remoteImage = trim($remoteImage);

        $name = substr($remoteImage, strrpos($remoteImage, '/') + 1);
        $name = trim($name);

        $originalExt = strrchr($name, '.');
        if ($originalExt && strlen($originalExt) > 1) {
            $originalExt = substr($originalExt, 1);
            $originalExt = strtolower($originalExt);
            $originalExt = trim($originalExt);

            $originalName = substr($name, 0, strrpos($name, '.'));
        } else {
            $originalExt = '';
            $originalName = $name;
        }

        $tmpDir = Be::getRuntime()->getRootPath() . '/data/tmp/';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
            chmod($tmpDir, 0777);
        }
        $tmpFile = $tmpDir . uniqid(date('Ymdhis') . '-' . rand(1, 999999) . '-', true);

        $fileData = null;
        $success = false;
        $n = 0;
        do {
            $n++;
            try {
                $fileData = Curl::get($remoteImage);
                $success = true;
            } catch (\Throwable $t) {
                if ($configDownloadRemoteImage->retryIntervalMin > 0 || $configDownloadRemoteImage->retryIntervalMax) {
                    if (Be::getRuntime()->isSwooleMode()) {
                        \Swoole\Coroutine::sleep(rand($configDownloadRemoteImage->retryIntervalMin, $configDownloadRemoteImage->retryIntervalMax));
                    } else {
                        sleep(rand($configDownloadRemoteImage->retryIntervalMin, $configDownloadRemoteImage->retryIntervalMax));
                    }
                }
            }
        } while ($success === false && $n < $configDownloadRemoteImage->retryTimes);

        if (!$success) {
            throw new TaskException('获取远程图片（' . $remoteImage . '）失败！');
        }

        file_put_contents($tmpFile, $fileData);

        try {
            $configSystem = Be::getConfig('App.System.System');
            $maxSize = $configSystem->uploadMaxSize;
            $maxSizeInt = FileSize::string2Int($maxSize);
            $size = filesize($tmpFile);
            if ($size > $maxSizeInt) {
                throw new ServiceException('您上传的文件尺寸已超过最大限制：' . $maxSize . '！');
            }

            $ext = Mime::detectExt($tmpFile, $originalExt);

            if (!in_array($ext, $configSystem->allowUploadImageTypes)) {
                throw new ServiceException('禁止上传的图像类型：' . $ext . '！');
            }

            $dirName = '';
            switch ($configDownloadRemoteImage->dirname) {
                case 'id':
                    $dirName = $article->id;
                    break;
                case 'url':
                    $dirName = $article->url;
                    break;
            };
            
            $fileName = '';
            switch ($configDownloadRemoteImage->fileName) {
                case 'original':
                    $fileName = $originalName . '.' . $ext;
                    break;
                case 'md5':
                    $fileName = md5_file($tmpFile) . '.' . $ext;
                    break;
                case 'sha1':
                    $fileName = sha1_file($tmpFile) . '.' . $ext;
                    break;
                case 'timestamp':
                    $fileName = uniqid(date('Ymdhis') . '-' . rand(1, 999999) . '-', true) . '.' . $ext;
                    break;
            };

            $storage = Be::getStorage();
            $object = $configDownloadRemoteImage->rootPath . $dirName . '/' . $fileName;
            if ($storage->isFileExist($object)) {
                $url = $storage->getFileUrl($object);
            } else {
                $url = $storage->uploadFile($object, $tmpFile);
            }

        } catch (\Throwable $t) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }

            throw $t;
        }

        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }

        if ($configDownloadRemoteImage->intervalMin > 0 || $configDownloadRemoteImage->intervalMax) {
            if (Be::getRuntime()->isSwooleMode()) {
                \Swoole\Coroutine::sleep(rand($configDownloadRemoteImage->intervalMin, $configDownloadRemoteImage->intervalMax));
            } else {
                sleep(rand($configDownloadRemoteImage->intervalMin, $configDownloadRemoteImage->intervalMax));
            }
        }

        return $url;
    }
}
