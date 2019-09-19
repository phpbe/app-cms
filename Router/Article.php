<?php
namespace App\Cms\Router;

use Be\System\Be;
use Be\System\Router;

class Article extends Router
{

    public function encodeUrl($app, $controller, $action, $params = [])
    {
        $configSystem = Be::getConfig('System', 'System');

        if ($action == 'articles') {
            if (isset($params['categoryId'])) {
                if (isset($params['page'])) {
                    return url() . '/Cms/Article/c' . $params['categoryId'] . '/p' . $params['page'] . '/';
                }
                return url() . '/Cms/Article/c' . $params['categoryId'] . '/';
            }
        } elseif ($action == 'detail') {
            if (isset($params['articleId'])) {
                return url() . '/Cms/Article/' . $params['articleId'] . $configSystem->sefSuffix;
            }
        } elseif ($action == 'user') {
            if (isset($params['userId'])) {
                return url() . '/Cms/Article/user/' . $params['userId'] . $configSystem->sefSuffix;
            }
        }

        return parent::encodeUrl($app, $controller, $action, $params);
    }

    public function decodeUrl($urls)
    {
        $len = count($urls);
        if ($len > 2) {
            if (is_numeric($urls[2])) {
                $_GET['action'] = $_REQUEST['action'] = 'detail';
                $_GET['articleId'] = $_REQUEST['articleId'] = $urls[2];

                return true;
            } elseif (substr($urls[2], 0, 1) == 'c') {
                $_GET['action'] = $_REQUEST['action'] = 'articles';
                $_GET['categoryId'] = $_REQUEST['categoryId'] = substr($urls[2], 1);

                if ($len > 3 && substr($urls[3], 0, 1) == 'p') {
                    $_GET['page'] = $_REQUEST['page'] = substr($urls[3], 1);
                }
                return true;
            } elseif ($urls[2] == 'user') {
                $_GET['action'] = $_REQUEST['action'] = 'user';
                $_GET['userId'] = $_REQUEST['userId'] = $urls[3];

                return true;
            }
        }

        return parent::decodeUrl($urls);
    }
}