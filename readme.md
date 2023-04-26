# 内容管理系统

## 应用简介

    be/app-cms 是一款基于BE框架实现的内容管理系统；
    以Redis缓存和ES搜索引擎支撑高负载应用场景；
    可以支持亿级以上数据量的高并发访问；


## 如何安装

### 1 新建 be 项目

    composer create-project be/new

### 2 安装 be/app-cms

    composer require be/app-cms



## 部件

### 1xxx 首页相关部件
* 1001 - 首页文章列表 - Home



### 2xxx 文章相关部件
* 2001 - 最新文章列表 - Article.Latest
* 2002 - 最新文章TopN - Article.LatterTopN
* 2003 - 最新文章TopN边栏 - Article.LatterTopNSide
* 
* 2004 - 热门文章列表 - Article.Hottest
* 2005 - 热门文章TopN - Article.HottestTopN
* 2006 - 热门文章TopN边栏 - Article.HottestTopNSide
* 
* 2007 - 热搜文章列表 - Article.HotSearch
* 2008 - 热搜文章TopN - Article.HotSearchTopN
* 2009 - 热搜文章TopN边栏 - Article.HotSearchTopNSide
*
* 2010 - 猜你喜欢文章列表 - Article.GuessYouLike
* 2011 - 猜你喜欢文章TopN - Article.GuessYouLikeTopN
* 2012 - 猜你喜欢文章TopN边栏 - Article.GuessYouLikeTopNSide
* 
* 2013 - 搜索结果 - Article.Search
* 2014 - 搜索表单边栏 - Article.SearchFormSide
* 
* 2015 - 标签检索结果 - Article.Tag
* 2016 - 标签TopN边栏 - Article.TagsTopNSide
*
* 2100 - 文章详情-主体 - Article.Detail.Main
* 2101 - 文章详情-评论 - Article.Detail.Comments
* 2102 - 文章详情-评论表单 - Article.Detail.CommentForm
* 2103 - 文章详情-类似文章 - Article.Detail.Similar
* 



### 3xxx 分类相关部件
* 3001 - 分类文章列表 - Category.Articles
* 3002 - 分类TopN - Category.TopN
* 3003 - 分类TopN边栏 - Category.TopNSide
* 3004 - 分类最新文章TopN边栏 - Category.LatterTopNSide
* 3005 - 分类热门文章TopN边栏 - Category.HottestTopNSide
* 3006 - 分类热搜文章TopN边栏 - Category.HotSearchTopNSide
* 3007 - 分类猜你喜欢文章TopN边栏 - Category.GuessYouLikeTopNSide

