<?php
namespace App\Cms\AdminController;

use Be\System\Be;
use Be\System\Log;
use Be\System\Request;
use Be\System\Response;
use Be\System\AdminController;

class Category extends AdminController
{


    public function categories()
    {
        $serviceCategory = Be::getService('Cms', 'Category');

        Response::setTitle('分类管理');
        Response::set('categories', $serviceCategory->getCategories());
        Response::display();
    }

    public function saveCategories()
    {
        $ids = Request::post('id', array(), 'int');
        $parentIds = Request::post('parentId', array(), 'int');
        $names = Request::post('name', array());

        $db = Be::getDb();
        $db->startTransaction();

        try {
            $tupleUser = Be::newTuple('system_user');
            $tupleUser->load(1);
            if (count($ids)) {
                for ($i = 0, $n = count($ids); $i < $n; $i++) {
                    if (!$ids[$i] && !$names[$i]) continue;

                    $tupleCategory = Be::newTuple('cms_category');
                    $tupleCategory->id = $ids[$i];
                    $tupleCategory->parent_id = $parentIds[$i];
                    $tupleCategory->name = $names[$i];
                    $tupleCategory->ordering = $i;
                    $tupleCategory->save();
                }
            }
            $db->commit();

            Be::getService('System', 'AdminLog')->addLog('修改文章分类信息');
            Response::success('保存分类成功！', adminUrl('Cms', 'Article', 'categories'));

        } catch (\Exception $e) {
            $db->rollback();
            Response::error('保存分类失败：'.$e->getMessage(), adminUrl('Cms', 'Article', 'categories'));
        }
    }

    public function ajaxDeleteCategory()
    {
        $categoryId = Request::post('id', 0, 'int');
        if (!$categoryId) {
            Response::error('参数(id)缺失！');
        } else {

            try {
                $tupleCategory = Be::newTuple('cms_category');
                $tupleCategory->load($categoryId);

                $serviceCategory = Be::getService('Cms', 'Category');
                $serviceCategory->deleteCategory($categoryId);

                Be::getService('System', 'AdminLog')->addLog('删除文章分类：#' . $categoryId . ': ' . $tupleCategory->title);

                Response::success('分类删除成功！');

            } catch (\Exception $e) {
                Response::error( $e->getMessage());
            }
        }
    }

}
