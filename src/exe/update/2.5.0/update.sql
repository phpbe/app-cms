ALTER TABLE `cms_page`
DROP `description`,
DROP `url_custom`,
DROP `seo_title`,
DROP `seo_title_custom`,
DROP `seo_description`,
DROP `seo_description_custom`,
DROP `seo_keywords`;

ALTER TABLE `cms_page`
ADD `config` MEDIUMTEXT NOT NULL COMMENT '配置' AFTER `url`,
ADD `theme` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '主题' AFTER `config`;
