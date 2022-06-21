<?php
namespace Be\Theme\ShopFai\Config\Section;


/**
 * @BeConfig("热门商品", icon="el-icon-star-on")
 */
class Hottest
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
     * @BeConfigItem("标题对齐方式",
     *     driver = "FormItemSelect",
     *     keyValues = "return ['left' => '居左', 'center' => '居中'];"
     * )
     */
    public $titleAlign = 'left';

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
     * @BeConfigItem("背景颜色",
     *     driver="FormItemColorPicker"
     * )
     */
    public $backgroundColor = '#fff';

    /**
     * @BeConfigItem("顶部内边距 - 电脑端（像素）",
     *     driver = "FormItemSlider"
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public $paddingTopDesktop = 40;

    /**
     * @BeConfigItem("顶部内边距 - 平板端（像素）",
     *     driver = "FormItemSlider"
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public $paddingTopTablet = 30;

    /**
     * @BeConfigItem("顶部内边距 - 手机端（像素）",
     *     driver = "FormItemSlider"
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public $paddingTopMobile = 20;

    /**
     * @BeConfigItem("底部内边距 - 电脑端（像素）",
     *     driver = "FormItemSlider"
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public $paddingBottomDesktop = 40;

    /**
     * @BeConfigItem("底部内边距 - 平板端（像素）",
     *     driver = "FormItemSlider"
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public $paddingBottomTablet = 30;

    /**
     * @BeConfigItem("底部内边距 - 手机端（像素）",
     *     driver = "FormItemSlider"
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public $paddingBottomMobile = 20;

    /**
     * @BeConfigItem("间距 - 电脑端（像素）",
     *     driver = "FormItemSlider"
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public $spacingDesktop = 40;

    /**
     * @BeConfigItem("间距 - 平板端（像素）",
     *     driver = "FormItemSlider"
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public $spacingTablet = 30;

    /**
     * @BeConfigItem("间距 - 手机端（像素）",
     *     driver = "FormItemSlider"
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public $spacingMobile = 20;


}
