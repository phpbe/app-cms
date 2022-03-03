<?php
namespace Be\App\Cms\Config;

/**
 * @BeConfig("火车采集器接口")
 */
class Locoy
{

    /**
     * @BeConfigItem("是否启用火车采集器接口",
     *     description="启用后，您可能将火车采集器采集的文章导入到CMS系统中",
     *     driver="FormItemSwitch"
     * )
     */
    public int $enable = 0;

    /**
     * @BeConfigItem("接口密码",
     *     description="密码用于识别已授权的访问，附加到网址中传输，为了系统安全，请妥善保管。",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $password = '123456';

    /**
     * @BeConfigItem("字段：标题",
     *     description="字段配置用于自定义POST传输的数据内容",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $field_title = 'title';

    /**
     * @BeConfigItem("字段：摘要",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $field_summary = 'summary';

    /**
     * @BeConfigItem("字段：描述",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $field_description = 'description';


}

