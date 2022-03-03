<?php
namespace Be\App\Cms\Controller;

use Be\App\ControllerException;
use Be\Be;

/**
 * 接口
 */
class Api
{

    /**
     * 火车采集器接口
     *
     * @BeRoute("/article/api/locoy")
     */
    public function locoy()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $configLocoy = Be::getConfig('App.Cms.Locoy');
            if ($configLocoy->enable === 0) {
                throw new ControllerException('火车采集器接口未启用！');
            }

            $password = $request->get('password', '');
            if ($configLocoy->password !== $password) {
                throw new ControllerException('密码错误！');
            }

            $title = $request->post($configLocoy->field_title, '');
            if ($title === '') {
                $categoryKeyValues = Be::getService('App.Cms.Admin.Category')->getCategoryKeyValues();
                $response->set('categories', $categoryKeyValues);
                $response->json();
                return;
            }

            $data = [];
            $data['title'] = $title;
            $data['summary'] = $request->post($configLocoy->field_summary, '');
            $data['description'] = $request->post($configLocoy->field_description, '');
            Be::getService('App.Cms.Admin.CollectArticle')->edit($data);

            $response->success('导入成功！');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }

}
