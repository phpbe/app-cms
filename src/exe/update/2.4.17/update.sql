ALTER TABLE `cms_article` ADD `download_remote_image` TINYINT NOT NULL DEFAULT '0' COMMENT '是否下载远程图片（0-不下载/1-下载/2-已下载完成/-1-下载失败）' AFTER `hits`;
