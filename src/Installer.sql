CREATE TABLE `cms_article` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`image` varchar(200) NOT NULL COMMENT '封面图片',
`title` varchar(120) NOT NULL COMMENT '标题',
`summary` varchar(500) NOT NULL COMMENT '摘要',
`description` mediumtext NOT NULL COMMENT '描述',
`url` varchar(200) NOT NULL COMMENT '自定义网址',
`author` varchar(50) NOT NULL COMMENT '作者',
`publish_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
`seo` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否自定义SEO信息',
`seo_title` varchar(200) NOT NULL COMMENT 'SEO标题',
`seo_description` varchar(500) NOT NULL COMMENT 'SEO描述',
`seo_keywords` varchar(60) NOT NULL COMMENT 'SEO关键词',
`ordering` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
`hits` int(11) NOT NULL DEFAULT '0' COMMENT '点击量 ',
`collect_blog_id` varchar(36) NOT NULL DEFAULT '' COMMENT '采集的文章ID',
`is_enable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否启用',
`is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已删除',
`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
`update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章';

CREATE TABLE `cms_article_category` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`article_id` varchar(36) NOT NULL COMMENT '文章ID',
`category_id` varchar(36) NOT NULL COMMENT '文章分类ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章的分类';

CREATE TABLE `cms_article_tag` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`article_id` varchar(36) NOT NULL COMMENT '文章ID',
`tag` varchar(60) NOT NULL COMMENT '标签'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章标签';

CREATE TABLE `cms_category` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`name` varchar(120) NOT NULL COMMENT '名称',
`description` varchar(500) NOT NULL COMMENT '描述',
`url` varchar(200) NOT NULL COMMENT '自定义网址',
`seo` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否自定义SEO信息',
`seo_title` varchar(120) NOT NULL COMMENT 'SEO标题',
`seo_description` varchar(500) NOT NULL COMMENT 'SEO描述',
`seo_keywords` varchar(60) NOT NULL COMMENT 'SEO关键词',
`ordering` int(11) NOT NULL COMMENT '排序',
`is_delete` int(11) NOT NULL DEFAULT '0' COMMENT '是否已删除',
`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
`update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类';

CREATE TABLE `cms_collect_article` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`unique_key` varchar(200) NOT NULL COMMENT '唯一键',
`image` varchar(200) NOT NULL COMMENT '封面图片',
`title` varchar(120) NOT NULL COMMENT '标题',
`summary` varchar(500) NOT NULL COMMENT '摘要',
`description` mediumtext NOT NULL COMMENT '描述',
`article_id` varchar(36) NOT NULL COMMENT '导入到的文章ID',
`is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已删除',
`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
`update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='采集的文章';

CREATE TABLE `cms_page` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`title` varchar(120) NOT NULL COMMENT '标题',
`description` text NOT NULL COMMENT '描述',
`url` varchar(200) NOT NULL COMMENT '自定义网址',
`seo` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否自定义SEO信息',
`seo_title` varchar(120) NOT NULL COMMENT 'SEO标题',
`seo_description` varchar(500) NOT NULL COMMENT 'SEO描述',
`seo_keywords` varchar(60) NOT NULL COMMENT 'SEO关键词',
`is_delete` tinyint(4) NOT NULL COMMENT '是否已删除',
`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
`update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='页面';


ALTER TABLE `cms_article`
ADD PRIMARY KEY (`id`),
ADD KEY `url` (`url`),
ADD KEY `update_time` (`update_time`);


ALTER TABLE `cms_article_category`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `article_id` (`article_id`,`category_id`) USING BTREE,
ADD KEY `category_id` (`category_id`) USING BTREE;

ALTER TABLE `cms_article_tag`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `article_tag` (`article_id`,`tag`) USING BTREE;

ALTER TABLE `cms_category`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `url` (`url`) USING BTREE;

ALTER TABLE `cms_collect_article`
ADD PRIMARY KEY (`id`),
ADD KEY `unique_key` (`unique_key`);

ALTER TABLE `cms_page`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `url` (`url`) USING BTREE;
