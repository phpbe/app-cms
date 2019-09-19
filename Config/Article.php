<?php
namespace App\Cms\Config;

use Be\System\Be;

/**
 * @be-config-label 文章
 * @be-config-writeAble true
 */
class Article
{
    /**
     *
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 默认从内容中提取摘要长度
     */
    public $getSummary = 80;

    /**
     *
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 默认从内容中提取 META 关键词个数
     */
    public $getMetaKeywords = 10;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 默认从内容中提取 META 描述长度
     */
    public $getMetaDescription = 80;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 下载远程图片
     */
    public $downloadRemoteImage = 0;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否允许评论
     * @be-config-item-optionValues {"1":"允许","0":"不允许"}
     */
    public $comment = 1;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 评论是否默认公开
     * @be-config-item-description 不公开时，需要管理员审核
     * @be-config-item-optionValues {"1":"公开","0":"不公开"}
     */
    public $commentPublic = true;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 缩图图大图宽度
     */
    public $thumbnailLW = 800;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 缩图图大图高度
     */
    public $thumbnailLH = 600;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 缩图图中图宽度
     */
    public $thumbnailMW = 200;

    /**
     *
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 缩图图中图高度
     */
    public $thumbnailMH = 150;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 缩图图小图宽度
     */
    public $thumbnailSW = 100;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 缩图图小图高度
     */
    public $thumbnailSH = 75;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemImage
     * @be-config-item-label 默认缩略图大图
     * @be-config-item-option {"path": "/Cms/Article/Thumbnail/Default"}
     */
    public $defaultThumbnailL = '0_l.gif';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemImage
     * @be-config-item-label 默认缩略图中图
     * @be-config-item-option {"path": "/Cms/Article/Thumbnail/Default"}
     */
    public $defaultThumbnailM = '0_m.gif';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemImage
     * @be-config-item-label 默认缩略图小图
     * @be-config-item-option {"path": "/Cms/Article/Thumbnail/Default"}
     */
    public $defaultThumbnailS = '0_s.gif';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 缓存有效期（单位：秒）
     */
    public $cacheExpire = 600;

}
