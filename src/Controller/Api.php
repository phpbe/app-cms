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
     * @BeRoute("/cms/api/locoy")
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

            $token = $request->get('token', '');
            if ($configLocoy->token !== $token) {
                throw new ControllerException('密钥错误！');
            }

            $title = $request->post('title', '');
            if ($title === '') {
                $categoryKeyValues = Be::getService('App.Cms.Admin.Category')->getCategoryKeyValues();
                $response->set('categories', $categoryKeyValues);
                $response->json();
                return;
            }

            $data = [];
            $data['unique_key'] = $request->post('unique_key', '');
            $data['title'] = $title;
            $data['summary'] = $request->post('summary', '');
            $data['description'] = $request->post('description', '', 'html');
            $data['author'] = $request->post('author', '');
            $data['publish_time'] = $request->post('publish_time', '');

            Be::getService('App.Cms.Admin.CollectArticle')->edit($data);

            $response->end('[OK] 导入成功！');
        } catch (\Throwable $t) {
            $response->end('[ERROR] ' . $t->getMessage());
        }
    }

}
