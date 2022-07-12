<?php

namespace Be\App\Cms\Section\Latest;


/**
 * @BeConfig("横幅", icon="el-icon-picture", ordering="4")
 */
class Config
{

    /**
     * @BeConfigItem("是否启用",
     *     driver = "FormItemSwitch")
     */
    public int $enable = 1;

    /**
     * @BeConfigItem("宽度",
     *     description="位于middle时有效",
     *     driver="FormItemSelect",
     *     keyValues = "return ['default' => '默认', 'fullWidth' => '全屏'];"
     * )
     */
    public string $width = 'fullWidth';

    /**
     * @BeConfigItem("背景颜色",
     *     driver="FormItemColorPicker"
     * )
     */
    public string $backgroundColor = '#fff';

    /**
     * @BeConfigItem("顶部内边距 - 电脑端（像素）",
     *     driver = "FormItemInputNumberInt",
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public int $paddingTopDesktop = 40;

    /**
     * @BeConfigItem("顶部内边距 - 平板端（像素）",
     *     driver = "FormItemInputNumberInt",
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public int $paddingTopTablet = 30;

    /**
     * @BeConfigItem("顶部内边距 - 手机端（像素）",
     *     driver = "FormItemInputNumberInt",
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public int $paddingTopMobile = 20;

    /**
     * @BeConfigItem("底部内边距 - 电脑端（像素）",
     *     driver = "FormItemInputNumberInt",
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public int $paddingBottomDesktop = 40;

    /**
     * @BeConfigItem("底部内边距 - 平板端（像素）",
     *     driver = "FormItemInputNumberInt",
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public int $paddingBottomTablet = 30;

    /**
     * @BeConfigItem("底部内边距 - 手机端（像素）",
     *     driver = "FormItemInputNumberInt",
     *     ui="return [':min' => 0, ':max' => 100];"
     * )
     */
    public int $paddingBottomMobile = 20;

}
