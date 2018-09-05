<?php
namespace App\Cms\Config;

use Phpbe\System\Be;

/**
 * @config-name 文章
 * @config-writeAble true
 */
class Article
{
    /**
     *
     * @config-name 默认从内容中提取摘要长度
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     */
    public $getSummary = 80;

    /**
     *
     * @config-name 默认从内容中提取 META 关键词个数
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     */
    public $getMetaKeywords = 10;

    /**
     *
     * @config-name 默认从内容中提取 META 描述长度
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     * @config-save defaultThumbnailL
     */
    public $getMetaDescription = 80;

    /**
     *
     * @config-name 下载远程图片
     * @config-description
     * @config-valueType bool
     * @config-optionType checkbox
     * @config-optionValues 1:下载
     */
    public $downloadRemoteImage = true;

    /**
     *
     * @config-name 是否允许评论
     * @config-description
     * @config-valueType bool
     * @config-optionType radio
     * @config-optionValues 1:允许|0:不允许
     */
    public $comment = true;

    /**
     *
     * @config-name 评论是否默认公开
     * @config-description 不公开时，需要管理员审核
     * @config-valueType bool
     * @config-optionType radio
     * @config-optionValues 1:公开|0:不公开
     */
    public $commentPublic = true;

    /**
     *
     * @config-name 缩图图大图宽度
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     */
    public $thumbnailLW = 800;

    /**
     *
     * @config-name 缩图图大图高度
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     */
    public $thumbnailLH = 600;

    /**
     *
     * @config-name 缩图图中图宽度
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     */
    public $thumbnailMW = 200;

    /**
     *
     *
     * 缩图图中图高度aa
     * 缩图图中图高度aa
     *
     * 缩图图中图高度bb
     * 缩图图中图高度bb
     * 缩图图中图高度bb
     *
     * @config-name 缩图图中图高度
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     */
    public $thumbnailMH = 150;

    /**
     *
     * @config-name 缩图图小图宽度
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     */
    public $thumbnailSW = 100;

    /**
     *
     * @config-name 缩图图小图高度
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     */
    public $thumbnailSH = 75;

    /**
     *
     * @config-name 默认缩略图大图
     * @config-description data/Cms/Article/Thumbnail/Default 文件夹下
     * @config-valueType string
     * @config-optionType file
     * @config-optionValues 
     */
    public $defaultThumbnailL = '0_l.gif';

    /**
     *
     * @config-name 默认缩略图中图
     * @config-description data/Cms/Article/Thumbnail/Default 文件夹下
     * @config-valueType string
     * @config-optionType file
     * @config-optionValues
     */
    public $defaultThumbnailM = '0_m.gif';

    /**
     *
     * @config-name 默认缩略图小图
     * @config-description data/Cms/Article/Thumbnail/Default 文件夹下
     * @config-valueType string
     * @config-optionType file
     * @config-optionValues
     */
    public $defaultThumbnailS = '0_s.gif';

    /**
     *
     * @config-name 缓存有效期（单位：秒）
     * @config-description
     * @config-valueType int
     * @config-optionType number
     * @config-optionValues
     */
    public $cacheExpire = 600;


    /**
     * 处理缩略图大图
     */
    public function defaultThumbnailL() {
        $defaultThumbnailL = $_FILES['defaultThumbnailL'];
        if ($defaultThumbnailL['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultThumbnailL['tmp_name']);
            if ($libImage->isImage()) {
                $defaultThumbnailLName = date('YmdHis') . '_l.' . $libImage->getType();
                $defaultThumbnailLPath = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/Default/' .  $defaultThumbnailLName;
                if (move_uploaded_file($defaultThumbnailL['tmp_name'], $defaultThumbnailLPath)) {
                    // @unlink(Be::getRuntime()->getDataPath().'/Cms/Article/Thumbnail/Default/'.$configArticle->defaultThumbnailL);
                    $this->defaultThumbnailL = $defaultThumbnailLName;
                }
            }
        }
    }

    /**
     * 处理缩略图中图
     */
    public function defaultThumbnailM() {
        $defaultThumbnailM = $_FILES['defaultThumbnailM'];
        if ($defaultThumbnailM['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultThumbnailM['tmp_name']);
            if ($libImage->isImage()) {
                $defaultThumbnailMName = date('YmdHis') . '_m.' . $libImage->getType();
                $defaultThumbnailMPath = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/Default/' .  $defaultThumbnailMName;
                if (move_uploaded_file($defaultThumbnailM['tmp_name'], $defaultThumbnailMPath)) {
                    // @unlink(Be::getRuntime()->getDataPath().'/Cms/Article/Thumbnail/Default/'.$configArticle->defaultThumbnailM);
                    $this->defaultThumbnailM = $defaultThumbnailMName;
                }
            }
        }
    }

    /**
     * 处理缩略图小图
     */
    public function defaultThumbnailS() {
        $defaultThumbnailS = $_FILES['defaultThumbnailS'];
        if ($defaultThumbnailS['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultThumbnailS['tmp_name']);
            if ($libImage->isImage()) {
                $defaultThumbnailSName = date('YmdHis') . '_s.' . $libImage->getType();
                $defaultThumbnailSPath = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/Default/' .  $defaultThumbnailSName;
                if (move_uploaded_file($defaultThumbnailS['tmp_name'], $defaultThumbnailSPath)) {
                    // @unlink(Be::getRuntime()->getDataPath().'/Cms/Article/Thumbnail/Default/'.$configArticle->defaultThumbnailS);
                    $this->defaultThumbnailS = $defaultThumbnailSName;
                }
            }
        }
    }
}
