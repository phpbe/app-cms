<?php

namespace Be\App\Cms\Section\Latest;

/**
 * @BeConfig("最新文章", icon="bi-star")
 */
class Config
{

    /**
     * @BeConfigItem("是否启用",
     *     driver = "FormItemSwitch")
     */
    public $enable = 1;

    /**
     * @BeConfigItem("标题",
     *     driver = "FormItemInput"
     * )
     */
    public $title = '最新文章';

    /**
     * @BeConfigItem("展示多少个最新文章?",
     *     driver = "FormItemSlider",
     *     ui="return [':min' => 1, ':max' => 100];"
     * )
     */
    public $quantity = 10;

    /**
     * @BeConfigItem("查看更多链接",
     *     driver = "FormItemInput"
     * )
     */
    public $more = '更多';

    /**
     * @BeConfigItem("宽度",
     *     description="位于middle时有效",
     *     driver="FormItemSelect",
     *     keyValues = "return ['default' => '默认', 'fullWidth' => '全屏'];"
     * )
     */
    public string $width = 'default';

    /**
     * @BeConfigItem("背景颜色",
     *     driver="FormItemColorPicker"
     * )
     */
    public string $backgroundColor = '#FFFFFF';

    /**
     * @BeConfigItem("内边距（手机端）",
     *     driver = "FormItemInput",
     *     description = "上右下左（CSS padding 语法）"
     * )
     */
    public string $paddingMobile = '1rem';

    /**
     * @BeConfigItem("内边距（平板端）",
     *     driver = "FormItemInput",
     *     description = "上右下左（CSS padding 语法）"
     * )
     */
    public string $paddingTablet = '1.5rem';

    /**
     * @BeConfigItem("内边距（电脑端）",
     *     driver = "FormItemInput",
     *     description = "上右下左（CSS padding 语法）"
     * )
     */
    public string $paddingDesktop = '2rem';

    /**
     * @BeConfigItem("外边距（手机端）",
     *     driver = "FormItemInput",
     *     description = "上右下左（CSS margin 语法）"
     * )
     */
    public string $marginMobile = '1rem 0';

    /**
     * @BeConfigItem("外边距（平板端）",
     *     driver = "FormItemInput",
     *     description = "上右下左（CSS margin 语法）"
     * )
     */
    public string $marginTablet = '1.5rem 0';

    /**
     * @BeConfigItem("外边距（电脑端）",
     *     driver = "FormItemInput",
     *     description = "上右下左（CSS margin 语法）"
     * )
     */
    public string $marginDesktop = '2rem 0';


}
