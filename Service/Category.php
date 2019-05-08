<?php
namespace App\Cms\Service;

use Phpbe\System\Be;
use Phpbe\System\Service;

class Category extends Service
{


    private $categories = null;
    private $categoryTree = null;

    /**
     * 获取分类列表
     */
    public function getCategories()
    {
        return Be::newTable('Cms.Category')->orderBy('ordering', 'ASC')->getObjects();
    }

    /**
     * 获取分类列表
     *
     * @return array|null
     */
    public function getCategoryFlatTree()
    {
        if ($this->categories === null) {
            $this->categories = $this->_createCategoryTree($this->getCategoryTree());
        }
        return $this->categories;
    }

    /**
     * 获取分类总数
     *
     * @return int
     */
    public function getCategoryCount()
    {
        return Be::newTable('Cms.ArticleCategory')->count();
    }

    /**
     * 获取分类树
     *
     * @return array|null
     */
    public function getCategoryTree()
    {
        if ($this->categoryTree === null) {
            $categories = Be::newTable('Cms.ArticleCategory')->getObjects();
            $this->categoryTree = $this->_createCategoryTree($categories);
        }
        return $this->categoryTree;
    }

    /**
     * 获取指定分类ID下的所有层级的子分类ID
     *
     * @param $categoryId
     * @return array
     */
    public function getSubCategoryIds($categoryId)
    {
        $categories = $this->getCategories();

        $ids = [];
        $level = 0;
        $start = false;
        foreach ($categories as $category) {
            if ($start) {
                if ($category->level > $level) {
                    $ids[] = $category->id;
                } else {
                    break;
                }
            } elseif ($category->id == $categoryId) {
                $level = $category->level;
                $start = true;
            }
        }
        return $ids;
    }

    /**
     * 生成分类列表，按树结构格式化过
     *
     * @param array $categoryTree 分类树
     * @param array $categories
     * @return array
     */
    private function createCategories($categoryTree = null, &$categories = [])
    {
        if (count($categoryTree)) {
            foreach ($categoryTree as $category) {
                $subCategory = null;
                if (isset($category->subCategory)) {
                    $subCategory = $category->subCategory;
                    unset($category->subCategory);
                }
                $categories[] = $category;

                if ($subCategory !== null) $this->createCategories($subCategory, $categories);
            }
        }
        return $categories;
    }

    /**
     * 生成分类树
     *
     * @param array $categories
     * @param int $parentId
     * @param int $level
     * @return array
     */
    private function _createCategoryTree(&$categories = null, $parentId = 0, $level = 0)
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category->parentId == $parentId) {
                $category->level = $level;
                $subCategory = $this->_createCategoryTree($categories, $category->id, $level + 1);
                if (count($subCategory)) $category->subCategory = $subCategory;
                $category->children = count($subCategory);
                $tree[] = $category;
            }
        }
        return $tree;
    }

    /**
     * 获取分类
     *
     * @param $categoryId
     * @return \Phpbe\System\Db\Tuple
     */
    public function getCategory($categoryId) {
        $tupleCategory = Be::newTuple('Cms.Category');
        $tupleCategory->load($categoryId);
        return $tupleCategory;
    }

    /**
     * 获取指定分类的最高父级分类
     * @param $categoryId
     * @return mixed | null | \Phpbe\System\Db\Tuple
     */
    public function getTopParentCategory($categoryId) {
        $tupleCategory = Be::newTuple('Cms.Category');
        $tupleCategory->load($categoryId);

        $parentCategory = null;
        $tmpCategory = $tupleCategory;
        while ($tmpCategory->parentId > 0) {
            $parentId = $tmpCategory->parentId;
            $tmpCategory = Be::newTuple('Cms.Category');
            $tmpCategory->load($parentId);
        }
        $parentCategory = $tmpCategory;

        return $parentCategory;
    }


    /**
     * 删除分类
     * @param int $categoryId 分类编号
     * @throws \Exception
     */
    public function deleteCategory($categoryId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {

            Be::newTable('Cms.Article')->where('category_id', $categoryId)->update(['category_id' => 0]);
            Be::newTable('Cms.Category')->where('parent_id', $categoryId)->update(['parent_id' => 0]);
            Be::newTuple('Cms.Category')->delete($categoryId);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }
}
