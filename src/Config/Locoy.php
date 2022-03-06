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
     * @BeConfigItem("接口密钥",
     *     description="密码用于识别已授权的访问，附加到网址中传输，为了系统安全，请妥善保管。",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $token = '';


}

