<?php

namespace App\Cms\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\System\AdminController;

class Setting extends AdminController
{

    public function setting()
    {
        Response::setTitle('设置文章系统参数');
        Response::set('configArticle', Be::getConfig('Cms', 'Article'));
        Response::display();
    }

    public function settingSave()
    {
        $configArticle = Be::getConfig('Cms', 'Article');

        $configArticle->getSummary = Request::post('getSummary', 0, 'int');
        $configArticle->getMetaKeywords = Request::post('getMetaKeywords', 0, 'int');
        $configArticle->getMetaDescription = Request::post('getMetaDescription', 0, 'int');
        $configArticle->downloadRemoteImage = Request::post('downloadRemoteImage', 0, 'int');
        $configArticle->comment = Request::post('comment', 0, 'int');
        $configArticle->commentPublic = Request::post('commentPublic', 0, 'int');

        $configArticle->thumbnailLW = Request::post('thumbnailLW', 0, 'int');
        $configArticle->thumbnailLH = Request::post('thumbnailLH', 0, 'int');
        $configArticle->thumbnailMW = Request::post('thumbnailMW', 0, 'int');
        $configArticle->thumbnailMH = Request::post('thumbnailMH', 0, 'int');
        $configArticle->thumbnailSW = Request::post('thumbnailSW', 0, 'int');
        $configArticle->thumbnailSH = Request::post('thumbnailSH', 0, 'int');

        // 缩图图大图
        $defaultThumbnailL = $_FILES['defaultThumbnailL'];
        if ($defaultThumbnailL['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultThumbnailL['tmpName']);
            if ($libImage->isImage()) {
                $defaultThumbnailLName = date('YmdHis') . 'L.' . $libImage->getType();
                $defaultThumbnailLPath = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/Default/' .  $defaultThumbnailLName;
                if (move_uploaded_file($defaultThumbnailL['tmpName'], $defaultThumbnailLPath)) {
                    // @unlink(Be::getRuntime()->getDataPath().'/article/thumbnail/default/'.$configArticle->defaultThumbnailL);
                    $configArticle->defaultThumbnailL = $defaultThumbnailLName;
                }
            }
        }

        // 缩图图中图
        $defaultThumbnailM = $_FILES['defaultThumbnailM'];
        if ($defaultThumbnailM['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultThumbnailM['tmpName']);
            if ($libImage->isImage()) {
                $defaultThumbnailMName = date('YmdHis') . 'M.' . $libImage->getType();
                $defaultThumbnailMPath = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/Default/' .  $defaultThumbnailMName;
                if (move_uploaded_file($defaultThumbnailM['tmpName'], $defaultThumbnailMPath)) {
                    // @unlink(Be::getRuntime()->getDataPath().'/article/thumbnail/default/'.$configArticle->defaultThumbnailM);
                    $configArticle->defaultThumbnailM = $defaultThumbnailMName;
                }
            }
        }

        // 缩图图小图
        $defaultThumbnailS = $_FILES['defaultThumbnailS'];
        if ($defaultThumbnailS['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultThumbnailS['tmpName']);
            if ($libImage->isImage()) {
                $defaultThumbnailSName = date('YmdHis') . 'S.' . $libImage->getType();
                $defaultThumbnailSPath = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/Default/' .  $defaultThumbnailSName;
                if (move_uploaded_file($defaultThumbnailS['tmpName'], $defaultThumbnailSPath)) {
                    // @unlink(Be::getRuntime()->getDataPath().'/article/thumbnail/default/'.$configArticle->defaultThumbnailS);
                    $configArticle->defaultThumbnailS = $defaultThumbnailSName;
                }
            }
        }

        $serviceSystem = Be::getService('System', 'Admin');
        $serviceSystem->updateConfig($configArticle, Be::getRuntime()->getRootPath() . '/Config/Article.php');

        Be::getService('System', 'AdminLog')->addLog('设置文章系统参数');

        Response::success('设置成功！', adminUrl('Cms', 'Article', 'setting'));
    }

}
