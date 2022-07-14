<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("文章")
 */
class Article
{

    /**
     * @BeConfigItem("默认分页条数", driver="FormItemSwitch")
     */
    public int $pageSize = 5;

    /**
     * @BeConfigItem("是否允许评论", driver="FormItemSwitch")
     */
    public int $comment = 1;

    /**
     * @BeConfigItem("评论是否默认公开", description="不公开时，需要管理员审核", driver="FormItemSwitch")
     */
    public int $commentPublic = 1;

}

