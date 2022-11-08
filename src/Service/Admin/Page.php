<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;
use Be\Config\Annotation\BeConfig;
use Be\Config\Annotation\BeConfigItem;
use Be\Config\ConfigHelper;
use Be\Db\DbException;
use Be\Runtime\RuntimeException;
use Be\Util\Str\Pinyin;

class Page
{


    /**
     * 获取自定义页面列表
     *
     * @return array
     * @throws DbException|RuntimeException
     */
    public function getPages(): array
    {
        $sql = 'SELECT * FROM cms_page WHERE is_delete = 0';
        $categories = Be::getDb()->getObjects($sql);
        return $categories;
    }

    /**
     * 获取自定义页面键值对
     *
     * @return array
     * @throws DbException|RuntimeException
     */
    public function getPageKeyValues(): array
    {
        $sql = 'SELECT id, title FROM cms_page WHERE is_delete = 0';
        return Be::getDb()->getKeyValues($sql);
    }

    /**
     * 创建自定义页面
     *
     * @return object
     * @throws DbException
     * @throws RuntimeException
     * @throws ServiceException
     */
    public function create(): object
    {
        $db = Be::getDb();

        $title = '新建页面';

        $urlTitle = strtolower($title);
        $url = Pinyin::convert($urlTitle, '-');
        $urlUnique = $url;
        $urlIndex = 0;
        $urlExist = null;
        do {
            $urlExist = Be::getTable('cms_page')
                    ->where('url', $urlUnique)
                    ->getValue('COUNT(*)') > 0;
            if ($urlExist) {
                $urlIndex++;
                $urlUnique = $url . '-' . $urlIndex;
            }
        } while ($urlExist);
        $url = $urlUnique;

        $tuplePage = Be::getTuple('cms_page');

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tuplePage->title = $title;
            $tuplePage->url = $url;
            $tuplePage->theme = '';
            $tuplePage->config = '';
            $tuplePage->is_delete = 0;
            $tuplePage->create_time = $now;
            $tuplePage->update_time = $now;
            $tuplePage->insert();

            $db->commit();

            Be::getService('App.System.Task')->trigger('Cms.PageSyncCache');

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException('新建自定义页面发生异常！');
        }

        return $tuplePage->toObject();
    }

    /**
     * 获取自定义页面
     *
     * @param string $pageId 自定义页面ID
     * @return object 自定义页面对象
     * @throws ServiceException
     */
    public function getPage(string $pageId): object
    {
        $tuplePage = Be::getTuple('cms_page');
        try {
            $tuplePage->load($pageId);
        } catch (\Throwable $t) {
            throw new ServiceException('自定义页面不存在！');
        }

        $page = $tuplePage->toObject();

        if ($page->config) {
            $config = unserialize($page->config);
            if ($config) {
                $page->config = $config;
            } else {
                $page->config = $this->getPageDefaultConfig($page);
            }
        } else {
            $page->config = $this->getPageDefaultConfig($page);
        }

        $page->name = 'Cms.Page.detail';
        $page->desktopPreviewUrl = beUrl('Cms.Page.detail', ['id' => $pageId]);
        $page->mobilePreviewUrl = beUrl('Cms.Page.detail', ['id' => $pageId, 'be-is-mobile' => 1]);

        $configPage = $page->config;

        $serviceTheme = Be::getService('App.System.Admin.Theme');

        foreach (['north', 'middle', 'west', 'center', 'east', 'south'] as $position) {
            $page->$position = $configPage->$position;
            $property = $position . 'Sections';
            if ($configPage->$position !== 0) {
                $sections = [];
                if (isset($configPage->$property) && count($configPage->$property)) {
                    foreach ($configPage->$property as $sectionIndex => $sectionData) {
                        try {

                            if (!isset($sectionData['config'])) {
                                $sectionConfig = $serviceTheme->getSectionConfig($sectionData['name']);
                            } else {
                                $sectionConfig = $sectionData['config'];
                            }

                            $section = $this->getSection($pageId, $position, $sectionIndex, $sectionData['name'], $sectionConfig);
                        } catch (\Throwable $t) {
                            continue;
                        }

                        $sections[] = $section;
                    }
                }
                $page->$property = $sections;

                $availableSections = $serviceTheme->getAvailableSections('Cms.Page.detail', $position);
                $property2 = $position . 'AvailableSections';
                $page->$property2 = $availableSections;
            }
        }

        return $page;
    }

    /**
     * 获取页面默认配置
     *
     * @param string $title 标题
     * @return object
     */
    public function getPageDefaultConfig($page): object
    {
        $themeName = $page->theme;
        $configTheme = Be::getConfig('App.System.Theme');
        if (!$themeName || !in_array($themeName, $configTheme->available)) {
            $themeName = $configTheme->default;
        }

        return  Be::getConfig('Theme.' . $themeName . '.Page.Cms.Page.detail');
    }

    /**
     * 获取指定的线上的部件
     *
     * @param string $pageId 页面ID
     * @param string $position 位置 方位
     * @param int $sectionIndex 部件索引 部件键名
     * @param string $sectionName 部件名称
     * @param object $sectionConfig 部件配置数据
     * @return object
     */
    public function getSection(string $pageId, string $position, int $sectionIndex, string $sectionName, object $sectionConfig): object
    {
        $parts = explode('.', $sectionName);
        $type = array_shift($parts);
        $name = array_shift($parts);
        $classPart = implode('\\', $parts);

        $configClass = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Config';
        if (!class_exists($configClass)) {
            throw new ServiceException('部件配置文件（' . $sectionName . '.Config）不存在!');
        }

        $templateClass = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Template';
        if (!class_exists($templateClass)) {
            throw new ServiceException('部件主题文件（' . $sectionName . '.Template）不存在!');
        }

        $sectionConfigInstance = new $configClass();
        $sectionTemplateInstance = new $templateClass();

        // 部件配置文件注解
        if (isset($sectionConfigInstance->items)) {
            // 包含子项的部件
            $sectionConfigAnnotation = $this->getConfigAnnotation($configClass, true);
        } else {
            $sectionConfigAnnotation = $this->getConfigAnnotation($configClass, false);
        }

        $section = new \stdClass();
        $section->name = $sectionName;
        $section->label = $sectionConfigAnnotation['label'];
        $section->ordering = $sectionConfigAnnotation['ordering'] ?? 100;
        $section->positions = $sectionTemplateInstance->positions;
        $section->routes = $sectionTemplateInstance->routes;

        $icon = null;
        if (isset($sectionConfigAnnotation['icon'])) {
            $icon = $sectionConfigAnnotation['icon'];
        } else {
            if (is_callable([$sectionConfigInstance, '__icon'])) {
                $icon = $sectionConfigInstance->__icon();
            }
        }
        if ($icon === null) {
            $icon = 'el-icon-menu';
        }
        if (strpos($icon, '<') === false) {
            $icon = '<i class="' . $icon . '"></i>';
        }
        $section->icon = $icon;

        $section->url = beAdminUrl('Cms.Page.editSection', ['pageId' => $pageId, 'position' => $position, 'sectionIndex' => $sectionIndex]);

        // 包含子项的部件
        if (isset($sectionConfigInstance->items)) {

            $items = [];

            $sectionConfigAnnotationItems = $sectionConfigAnnotation['configItems'];

            $annotationItems = [];
            foreach ($sectionConfigAnnotationItems as $sectionConfigAnnotationItem) {
                if ($sectionConfigAnnotationItem['name'] === 'items') {
                    $annotationItems = $sectionConfigAnnotationItem;
                    break;
                }
            }

            $itemDriverClasses = [];
            if (isset($annotationItems['items'])) {
                foreach ($annotationItems['items'] as $driverClass) {
                    $name = substr($driverClass, strrpos($driverClass, '\\') + 1);
                    $itemDriverClasses[$name] = [
                        'class' => $driverClass,
                        'annotation' => $this->getConfigAnnotation($driverClass, false)
                    ];
                }
            }

            $existItems = [];
            if (isset($sectionConfig->items)) {
                foreach ($sectionConfig->items as $itemIndex => $item) {
                    if (isset($itemDriverClasses[$item['name']])) {

                        $itemDriver = $itemDriverClasses[$item['name']];
                        $itemDriverClass = $itemDriver['class'];
                        $itemInstance = new $itemDriverClass();

                        $existItem = new \stdClass();
                        $existItem->name = $item['name'];
                        $existItem->label = $itemDriver['annotation']['label'];

                        /*
                        foreach ($item['config'] as $k => $v) {
                            $itemInstance->$k = $v;
                        }
                        */

                        $icon = null;
                        if (isset($itemDriver['annotation']['icon'])) {
                            $icon = $itemDriver['annotation']['icon'];
                        } else if (is_callable([$itemInstance, '__icon'])) {
                            $icon = $itemInstance->__icon();
                        } else {
                            $icon = 'el-icon-full-screen';
                        }
                        if (strpos($icon, '<') === false) {
                            $icon = '<i class="' . $icon . '"></i>';
                        }

                        $existItem->icon = $icon;

                        $existItem->url = beAdminUrl('Cms.Page.editSectionItem', ['pageId' => $pageId, 'position' => $position, 'sectionIndex' => $sectionIndex, 'itemIndex' => $itemIndex]);

                        $existItems[] = $existItem;
                    }
                }
            }
            $items['existItems'] = $existItems;

            if (!isset($annotationItems['lock']) || !$annotationItems['lock']) {
                $newItems = [];
                foreach ($itemDriverClasses as $itemDriver) {

                    $newItem = new \stdClass();
                    $newItem->name = $itemDriver['annotation']['name'];
                    $newItem->label = $itemDriver['annotation']['label'];

                    $icon = null;
                    if (isset($itemDriver['annotation']['icon'])) {
                        $icon = $itemDriver['annotation']['icon'];
                    } else {
                        $icon = 'el-icon-full-screen';
                    }

                    if (strpos($icon, '<') === false) {
                        $icon = '<i class="' . $icon . '"></i>';
                    }

                    $newItem->icon = $icon;

                    $newItem->url = beAdminUrl('Cms.Page.addSectionItem', ['pageId' => $pageId, 'position' => $position, 'sectionIndex' => $sectionIndex, 'itemName' => $itemDriver['annotation']['name']]);

                    $newItems[] = $newItem;
                }

                $items['newItems'] = $newItems;
            }

            $section->items = $items;

        }

        return $section;
    }

    /**
     * 获取页面 编辑表单驱动
     *
     * @param string $pageId 页面名
     * @return array
     */
    public function getPageDrivers(string $pageId): array
    {
        $page = $this->getPage($pageId);
        $configInstance = $page->config;
        $class = '\\Be\\App\\Cms\\Config\\Page\\Page\\detail';

        try {
            $configAnnotation = $this->getConfigAnnotation($class, true);
            if ($configAnnotation['configItems']) {
                $configItemDrivers = [];

                foreach ($configAnnotation['configItems'] as $configItem) {

                    $itemName = $configItem['name'];
                    if (isset($configInstance->$itemName)) {
                        $configItem['value'] = $configInstance->$itemName;
                    }

                    $driverClass = null;
                    if (isset($configItem['driver'])) {
                        if (substr($configItem['driver'], 0, 8) === 'FormItem') {
                            $driverClass = '\\Be\\AdminPlugin\\Form\\Item\\' . $configItem['driver'];
                        } else {
                            $driverClass = $configItem['driver'];
                        }
                    } else {
                        $driverClass = \Be\AdminPlugin\Form\Item\FormItemInput::class;
                    }
                    $driver = new $driverClass($configItem);

                    $configItemDrivers[] = $driver;
                }

                return $configItemDrivers;
            }
        } catch (\Throwable $t) {
            return [];
        }

        return [];
    }

    /**
     * 获取部件 编辑表单驱动
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     * @return array
     * @throws ServiceException
     */
    public function getSectionDrivers(string $pageId, string $position, int $sectionIndex): array
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';
        $sectionData = $configPage->$property[$sectionIndex];

        if (!isset($sectionData['config'])) {
            $sectionConfig = Be::getService('App.System.Admin.Theme')->getSectionConfig($sectionData['name']);
        } else {
            $sectionConfig = $sectionData['config'];
        }

        $parts = explode('.', $sectionData['name']);
        $type = array_shift($parts);
        $name = array_shift($parts);
        $classPart = implode('\\', $parts);

        $class = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Config';
        $configAnnotation = $this->getConfigAnnotation($class, true);

        if ($configAnnotation['configItems']) {
            $configItemDrivers = [];
            foreach ($configAnnotation['configItems'] as $configItem) {

                if ($configItem['name'] === 'items') {
                    continue;
                }

                $configItemName = $configItem['name'];
                if (isset($sectionConfig->$configItemName)) {
                    $configItem['value'] = $sectionConfig->$configItemName;
                }

                $driverClass = null;
                if (isset($configItem['driver'])) {
                    if (substr($configItem['driver'], 0, 8) === 'FormItem') {
                        $driverClass = '\\Be\\AdminPlugin\\Form\\Item\\' . $configItem['driver'];
                    } else {
                        $driverClass = $configItem['driver'];
                    }
                } else {
                    $driverClass = \Be\AdminPlugin\Form\Item\FormItemInput::class;
                }
                $driver = new $driverClass($configItem);

                $configItemDrivers[] = $driver;
            }

            return $configItemDrivers;
        }

        return [];
    }

    /**
     * 获取部件 编辑表单驱动
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     * @param int $itemIndex 部件子项索引
     * @return array
     * @throws ServiceException
     */
    public function getSectionItemDrivers(string $pageId, string $position, int $sectionIndex, int $itemIndex): array
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';
        $sectionData = $configPage->$property[$sectionIndex];

        if (!isset($sectionData['config'])) {
            $sectionConfig = Be::getService('App.System.Admin.Theme')->getSectionConfig($sectionData['name']);
        } else {
            $sectionConfig = $sectionData['config'];
        }

        $sectionItemData = $sectionConfig->items[$itemIndex];
        $sectionItemName = $sectionItemData['name'];
        if (!isset($sectionItemData['config'])) {
            $sectionItemConfig = Be::getService('App.System.Admin.Theme')->getSectionItemConfig($sectionData['name'], $sectionItemName);
        } else {
            $sectionItemConfig = $sectionItemData['config'];
        }

        $parts = explode('.', $sectionData['name']);
        $type = array_shift($parts);
        $name = array_shift($parts);
        $classPart = implode('\\', $parts);

        $class = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Item\\' . $sectionItemName;
        $configAnnotation = $this->getConfigAnnotation($class, true);

        if ($configAnnotation['configItems']) {
            $configItemDrivers = [];
            foreach ($configAnnotation['configItems'] as $configItem) {

                $configItemName = $configItem['name'];
                if (isset($sectionItemConfig->$configItemName)) {
                    $configItem['value'] = $sectionItemConfig->$configItemName;
                }

                $driverClass = null;
                if (isset($configItem['driver'])) {
                    if (substr($configItem['driver'], 0, 8) === 'FormItem') {
                        $driverClass = '\\Be\\AdminPlugin\\Form\\Item\\' . $configItem['driver'];
                    } else {
                        $driverClass = $configItem['driver'];
                    }
                } else {
                    $driverClass = \Be\AdminPlugin\Form\Item\FormItemInput::class;
                }
                $driver = new $driverClass($configItem);

                $configItemDrivers[] = $driver;
            }

            return $configItemDrivers;
        }

        return [];
    }


    private static $cache = [];

    public static function getConfigAnnotation($className, $withItemAnnotation = true)
    {
        if (!class_exists($className)) {
            throw new ServiceException('配置文件（' . $className . '）不存在！');
        }

        $reflection = null;
        if (isset(self::$cache['configAnnotation'][$className])) {
            $configAnnotation = self::$cache['configAnnotation'][$className];
        } else {
            $reflection = new \ReflectionClass($className);
            $classComment = $reflection->getDocComment();
            $parseClassComments = \Be\Util\Annotation::parse($classComment);
            if (!isset($parseClassComments['BeConfig'][0])) {
                throw new ServiceException('配置文件（' . $className . '）中未检测到 BeConfig 注解！');
            }

            $annotation = new BeConfig($parseClassComments['BeConfig'][0]);
            $configAnnotation = $annotation->toArray();
            if (isset($configAnnotation['value'])) {
                $configAnnotation['label'] = $configAnnotation['value'];
                unset($configAnnotation['value']);
            }

            $configAnnotation['name'] = substr($className, strrpos($className, '\\') + 1);

            self::$cache['configAnnotation'][$className] = $configAnnotation;
        }

        if ($withItemAnnotation) {
            if (isset(self::$cache['configItemAnnotation'][$className])) {
                $configItemAnnotations = self::$cache['configItemAnnotation'][$className];
            } else {
                $configItemAnnotations = [];
                $originalConfigInstance = new $className();
                if ($reflection === null) {
                    $reflection = new \ReflectionClass($className);
                }
                $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
                foreach ($properties as $property) {
                    $itemName = $property->getName();
                    $itemComment = $property->getDocComment();
                    $parseItemComments = \Be\Util\Annotation::parse($itemComment);

                    $configItemAnnotation = null;
                    if (isset($parseItemComments['BeConfigItem'][0])) {
                        $annotation = new BeConfigItem($parseItemComments['BeConfigItem'][0]);
                        $configItemAnnotation = $annotation->toArray();
                        if (isset($configItemAnnotation['value'])) {
                            $configItemAnnotation['label'] = $configItemAnnotation['value'];
                            unset($configItemAnnotation['value']);
                        }
                    } else {
                        $fn = '_' . $itemName;
                        if (is_callable([$originalConfigInstance, $fn])) {
                            $configItemAnnotation = $originalConfigInstance->$fn($itemName);
                        }
                    }

                    if ($configItemAnnotation) {
                        $configItemAnnotation['name'] = $itemName;
                        $configItemAnnotations[] = $configItemAnnotation;
                    }
                }

                self::$cache['configItemAnnotation'][$className] = $configItemAnnotations;
            }

            $configAnnotation['configItems'] = $configItemAnnotations;
        }

        return $configAnnotation;
    }

    /**
     * 修改主题
     *
     * @param string $pageId 页面ID
     * @param string $themeType 主题类型
     * @param string $theme 主题
     */
    public function changeTheme(string $pageId, string $themeType, string $theme)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        if ($themeType === '0') {
            $page->theme = '';
        } else if ($themeType === '1') {
            if ($theme === '') {
                throw new ServiceException('未选择有效的主题！');
            }

            $configTheme = Be::getConfig('App.System.Theme');
            if (!in_array($theme, $configTheme->available)) {
                throw new ServiceException('未选择有效的主题！！');
            }

            $page->theme = $theme;
        }

        $this->save($page, $configPage);
    }

    /**
     * 保存页面配置信息
     *
     * @param string $pageId 页面名
     * @param array $formData 表单数据
     */
    public function editPage(string $pageId, array $formData)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;
        $class = '\\Be\\App\\Cms\\Config\\Page\\Page\\detail';

        $newValues = $this->submitFormData($class, $formData, $configPage);

        foreach ($newValues as $key => $val) {
            $configPage->$key = $val;
        }

        $this->save($page, $configPage);
    }

    /**
     * 重置页面配置信息
     *
     * @param string $pageId 页面名
     */
    public function resetPage(string $pageId)
    {
        $page = $this->getPage($pageId);
        $configPage = $this->getPageDefaultConfig();
        $this->save($page, $configPage);
    }

    /**
     * 指定页面指定方位配置
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     */
    public function editPosition(string $pageId, string $position, array $formData)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        if (!isset($formData['enable']) || !is_numeric($formData['enable'])) {
            throw new ServiceException('参数（enable）无效！');
        }

        $formData['enable'] = (int)$formData['enable'];

        if (!in_array($formData['enable'], [-1, 0, 1])) {
            throw new ServiceException('参数（enable）无效！');
        }

        if (!isset($formData['width']) || !is_numeric($formData['width'])) {
            $formData['width'] = 1;
        }

        $formData['width'] = (int)$formData['width'];
        if ($formData['width'] < 1) {
            $formData['width'] = 1;
        }

        if ($formData['width'] > 100) {
            $formData['width'] = 100;
        }

        if (in_array($position, ['west', 'center', 'east'])) {
            if ($formData['enable'] === -1) {
                $configPage->$position = -1;
            } elseif ($formData['enable'] === 0) {
                $configPage->$position = 0;
            } else {
                $configPage->$position = $formData['width'];
                $configPage->middle = 0;
            }
        } else {
            $configPage->$position = $formData['enable'];
            if ($position === 'middle' && $formData['enable'] === 1) {
                $configPage->west = 0;
                $configPage->center = 0;
                $configPage->east = 0;
            }
        }

        $property = $position . 'Sections';
        if ($configPage->$position > 0) {
            if (!isset($configPage->$property) || !is_array($configPage->$property)) {
                $configPage->$property = [];
                $themeName = Be::getConfig('App.System.Theme')->default;
                $configPageDefault = Be::getService('App.System.Theme')->getPageConfig('Theme', $themeName, 'Cms.Page.detail');
                if (isset($configPageDefault->$property) && is_array($configPageDefault->$property)) {
                    $configPage->$property = $configPageDefault->$property;
                }
            }
        } else {
            unset($configPage->$property);
        }

        $this->save($page, $configPage);
    }

    /**
     * 重置方位配置信息
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     */
    public function resetPosition(string $pageId, string $position)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $themeName = Be::getConfig('App.System.Theme')->default;
        $configPageDefault = Be::getService('App.System.Theme')->getPageConfig('Theme', $themeName, 'Cms.Page.detail');

        $property = $position . 'Sections';

        // 重置部件配置
        if (isset($configPage->$property)) {
            if (isset($instanceOriginal->$property)) {
                $configPage->$property = $configPageDefault->$property;
            } else {
                unset($configPage->$property);
            }
        }

        $this->save($page, $configPage);
    }

    /**
     * 保存配置信息
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     * @param array $formData 表单数据
     */
    public function editSection(string $pageId, string $position, int $sectionIndex, array $formData)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';

        $sectionData = $configPage->$property[$sectionIndex];

        if (!isset($sectionData['config'])) {
            $sectionConfig = Be::getService('App.System.Admin.Theme')->getSectionConfig($sectionData['name']);
            $configPage->$property[$sectionIndex]['config'] = $sectionConfig;
        } else {
            $sectionConfig = $sectionData['config'];
        }

        $parts = explode('.', $sectionData['name']);
        $type = array_shift($parts);
        $name = array_shift($parts);
        $classPart = implode('\\', $parts);

        // 配置部件信息
        $class = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Config';
        $newValues = $this->submitFormData($class, $formData, $sectionConfig);
        foreach ($newValues as $key => $val) {
            $sectionConfig->$key = $val;
        }
        $configPage->$property[$sectionIndex]['config'] = $sectionConfig;

        $this->save($page, $configPage);
    }

    /**
     * 新增部件
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param string $sectionName
     */
    public function addSection(string $pageId, string $position, string $sectionName)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';
        $configPage->$property[] = [
            'name' => $sectionName,
        ];

        $this->save($page, $configPage);
    }

    /**
     * 新增部件
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     */
    public function deleteSection(string $pageId, string $position, int $sectionIndex)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';
        if (isset($configPage->$property[$sectionIndex])) {
            unset($configPage->$property[$sectionIndex]);
            $configPage->$property = array_values($configPage->$property);
        }

        $this->save($page, $configPage);
    }

    /**
     * 部件排序
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $oldIndex
     * @param int $newIndex
     */
    public function sortSection(string $pageId, string $position, int $oldIndex, int $newIndex)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';

        if (!isset($configPage->$property[$oldIndex]) || !isset($configPage->$property[$newIndex])) {
            throw new ServiceException('部件排序出错：索引超出数据范围');
        }

        $tmpData = $configPage->$property[$oldIndex];
        unset($configPage->$property[$oldIndex]);
        $arr = array_slice($configPage->$property, 0, $newIndex);
        $arr[] = $tmpData;
        $arr = array_merge($arr, array_slice($configPage->$property, $newIndex));
        $configPage->$property = array_values($arr);

        $this->save($page, $configPage);
    }

    /**
     * 重置配置信息
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     */
    public function resetSection(string $pageId, string $position, int $sectionIndex)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';
        if (isset($configPage->$property[$sectionIndex]['config'])) {
            unset($configPage->$property[$sectionIndex]['config']);
        }

        $this->save($page, $configPage);
    }

    /**
     * 保存配置信息
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     * @param int $itemIndex 部件子项索引
     * @param array $formData 表单数据
     */
    public function editSectionItem(string $pageId, string $position, int $sectionIndex, int $itemIndex, array $formData)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';

        $sectionData = $configPage->$property[$sectionIndex];

        if (!isset($sectionData['config'])) {
            $sectionConfig = Be::getService('App.System.Admin.Theme')->getSectionConfig($sectionData['name']);
            $configPage->$property[$sectionIndex]['config'] = $sectionConfig;
        } else {
            $sectionConfig = $sectionData['config'];
        }

        $parts = explode('.', $sectionData['name']);
        $type = array_shift($parts);
        $name = array_shift($parts);
        $classPart = implode('\\', $parts);

        // 配置子部件信息
        $sectionItemData = $sectionConfig->items[$itemIndex];
        $sectionItemName = $sectionItemData['name'];
        if (!isset($sectionItemData['config'])) {
            $sectionItemConfig = Be::getService('App.System.Admin.Theme')->getSectionItemConfig($sectionData['name'], $sectionItemName);
        } else {
            $sectionItemConfig = $sectionItemData['config'];
        }

        $class = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Item\\' . $sectionItemName;
        $newValues = $this->submitFormData($class, $formData, $sectionItemConfig);
        foreach ($newValues as $key => $val) {
            $sectionItemConfig->$key = $val;
        }
        $configPage->$property[$sectionIndex]['config']->items[$itemIndex]['config'] = $sectionItemConfig;

        $this->save($page, $configPage);
    }

    /**
     * 新增子项目
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     * @param string $itemName 部件子项名称
     */
    public function addSectionItem(string $pageId, string $position, int $sectionIndex, string $itemName)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';

        $sectionData = $configPage->$property[$sectionIndex];

        if (!isset($sectionData['config'])) {
            $sectionConfig = Be::getService('App.System.Admin.Theme')->getSectionConfig($sectionData['name']);
            $configPage->$property[$sectionIndex]['config'] = $sectionConfig;
        } else {
            $sectionConfig = $sectionData['config'];
        }

        if (isset($sectionConfig->items) && is_array($sectionConfig->items)) {
            $sectionConfigItems = $sectionConfig->items;
        } else {
            $sectionConfigItems = [];
        }

        $sectionConfigItems[] = [
            'name' => $itemName,
        ];

        $configPage->$property[$sectionIndex]['config']->items = $sectionConfigItems;

        $this->save($page, $configPage);
    }

    /**
     * 新增子部件
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     * @param int $itemIndex 部件子项索引
     */
    public function deleteSectionItem(string $pageId, string $position, int $sectionIndex, int $itemIndex)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';

        $sectionData = $configPage->$property[$sectionIndex];

        if (!isset($sectionData['config'])) {
            $sectionConfig = Be::getService('App.System.Admin.Theme')->getSectionConfig($sectionData['name']);
            $configPage->$property[$sectionIndex]['config'] = $sectionConfig;
        } else {
            $sectionConfig = $sectionData['config'];
        }

        if (isset($sectionConfig->items[$itemIndex])) {
            unset($sectionConfig->items[$itemIndex]);
            $configPage->$property[$sectionIndex]['config']->items = array_values($sectionConfig->items);
        }

        $this->save($page, $configPage);
    }

    /**
     * 重置配置信息
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     * @param int $itemIndex 部件子项索引
     */
    public function resetSectionItem(string $pageId, string $position, int $sectionIndex, int $itemIndex)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;

        $property = $position . 'Sections';
        // 重置部件子项配置
        if (isset($configPage->$property[$sectionIndex]['config']->items[$itemIndex]['config'])) {
            unset($configPage->$property[$sectionIndex]['config']->items[$itemIndex]['config']);
        }

        $this->save($page, $configPage);
    }

    /**
     * 部件排序
     *
     * @param string $pageId 页面名
     * @param string $position 位置
     * @param int $sectionIndex 部件索引
     * @param int $oldIndex 旧索引
     * @param int $newIndex 新索引
     */
    public function sortSectionItem(string $pageId, string $position, int $sectionIndex, int $oldIndex, int $newIndex)
    {
        $page = $this->getPage($pageId);
        $configPage = $page->config;
        
        $property = $position . 'Sections';

        $sectionData = $configPage->$property[$sectionIndex];

        if (!isset($sectionData['config'])) {
            $sectionConfig = Be::getService('App.System.Admin.Theme')->getSectionConfig($sectionData['name']);
            $configPage->$property[$sectionIndex]['config'] = $sectionConfig;
        } else {
            $sectionConfig = $sectionData['config'];
        }

        $sectionConfigItems = $sectionConfig->items;

        if (!isset($sectionConfigItems[$oldIndex]) ||
            !isset($sectionConfigItems[$newIndex])) {
            throw new ServiceException('子部件排序出错：索引超出数据范围' . $property . '-' . $sectionIndex . '-' . $oldIndex . '-' . $newIndex);
        }

        $tmpData = $sectionConfigItems[$oldIndex];
        unset($sectionConfigItems[$oldIndex]);
        $arr = array_slice($sectionConfigItems, 0, $newIndex);
        $arr[] = $tmpData;
        $arr = array_merge($arr, array_slice($sectionConfigItems, $newIndex));

        $configPage->$property[$sectionIndex]['config']->items = array_values($arr);

        $this->save($page, $configPage);
    }

    /**
     * 保存页面配置
     *
     * @param object $page
     * @param object $configPage
     * @return void
     */
    private function save(object $page, object $configPage)
    {
        if ($configPage->middle !== 0) {
            $configPage->west = 0;
            $configPage->center = 0;
            $configPage->east = 0;
            unset($configPage->westSections, $configPage->centerSections, $configPage->eastSections);
        }

        if ($configPage->west !== 0 || $configPage->center !== 0 || $configPage->east !== 0) {
            $configPage->middle = 0;
            unset($configPage->middleSections);
        }

        foreach (['north', 'middle', 'west', 'center', 'east', 'south'] as $position) {
            if ($configPage->$position <= 0) {
                $property = $position . 'Sections';
                unset($configPage->$property);
            }
        }

        $db = Be::getDb();

        $tuplePage = Be::getTuple('cms_page');
        try {
            $tuplePage->load($page->id);
        } catch (\Throwable $t) {
            throw new ServiceException('自定义页面（# ' . $page->id . '）不存在！');
        }

        if ($tuplePage->is_delete === 1) {
            throw new ServiceException('自定义页面（# ' . $page->id . '）不存在！');
        }

        $configPage->title = trim($configPage->title);
        $configPage->pageTitle = trim($configPage->pageTitle);
        if ($configPage->pageTitle === '') {
            if ($configPage->title === '') {
                throw new ServiceException('标题未填写！');
            }
            $title = $configPage->title;
        } else {
            $title = $configPage->pageTitle;
        }

        $configPage->url = trim($configPage->url);
        if ($configPage->url === '') {
            $urlTitle = strtolower($title);
            $url = Pinyin::convert($urlTitle, '-');
            if (strlen($url) > 200) {
                $url = Pinyin::convert($urlTitle, '-', true);
                if (strlen($url) > 200) {
                    $url = Pinyin::convert($urlTitle, '', true);
                }
            }
            //$configPage->url = $url;
        } else {
            $url = $configPage->url;
        }

        $urlUnique = $url;
        $urlIndex = 0;
        $urlExist = null;
        do {
            $urlExist = Be::getTable('cms_page')
                    ->where('url', $urlUnique)
                    ->where('id', '!=', $page->id)
                    ->getValue('COUNT(*)') > 0;

            if ($urlExist) {
                $urlIndex++;
                $urlUnique = $url . '-' . $urlIndex;
            }
        } while ($urlExist);
        $url = $urlUnique;


        $db->startTransaction();
        try {

            $now = date('Y-m-d H:i:s');
            $tuplePage->title = $title;
            $tuplePage->url = $url;
            $tuplePage->theme = $page->theme;
            $tuplePage->config = serialize($configPage);
            $tuplePage->update_time = $now;
            $tuplePage->update();

            $db->commit();

            Be::getService('App.System.Task')->trigger('Cms.PageSyncCache');

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException('编辑自定义页面发生异常！');
        }
    }

    /**
     * 指定类名的配置项表单提交
     *
     * @param string $className 类名
     * @param array $formData 表单数据
     * @param object $config 配置数据
     * @return array
     * @throws ServiceException
     * @throws \Be\AdminPlugin\AdminPluginException
     * @throws \ReflectionException
     */
    private function submitFormData(string $className, array $formData, object $config): array
    {
        $originalConfigInstance = new $className();

        $newValues = [];
        $reflection = new \ReflectionClass($className);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            $itemName = $property->getName();

            if ($itemName === 'items') {
                continue;
            }

            if (!isset($formData[$itemName])) {
                continue;
                //throw new ServiceException('参数 (' . $itemName . ') 缺失！');
            }

            $itemComment = $property->getDocComment();
            $parseItemComments = \Be\Util\Annotation::parse($itemComment);

            $configItem = null;
            if (isset($parseItemComments['BeConfigItem'][0])) {
                $annotation = new BeConfigItem($parseItemComments['BeConfigItem'][0]);
                $configItem = $annotation->toArray();
                if (isset($configItem['value'])) {
                    $configItem['label'] = $configItem['value'];
                    unset($configItem['value']);
                }
            } else {
                $fn = '_' . $itemName;
                if (is_callable([$originalConfigInstance, $fn])) {
                    $configItem = $originalConfigInstance->$fn($itemName);
                }
            }

            if ($configItem) {
                $configItem['name'] = $itemName;

                $driverClass = null;
                if (isset($configItem['driver'])) {
                    if (substr($configItem['driver'], 0, 8) === 'FormItem') {
                        $driverClass = '\\Be\\AdminPlugin\\Form\\Item\\' . $configItem['driver'];
                    } else {
                        $driverClass = $configItem['driver'];
                    }
                } else {
                    $driverClass = \Be\AdminPlugin\Form\Item\FormItemInput::class;
                }

                if (isset($config->$itemName)) {
                    $configItem['value'] = $config->$itemName;
                }

                $driver = new $driverClass($configItem);
                $driver->submit($formData);

                $newValues[$itemName] = $driver->newValue;
            }
        }

        return $newValues;
    }

    /**
     * 删除自定义页面
     *
     * @param string $pageId 自定义页面ID
     * @return true
     */
    public function deletePage(string $pageId): bool
    {
        $tuplePage = Be::getTuple('cms_page');
        try {
            $tuplePage->load($pageId);
        } catch (\Throwable $t) {
            throw new ServiceException('自定义页面不存在！');
        }

        if ($tuplePage->is_delete === 1) {
            throw new ServiceException('自定义页面不存在！');
        }

        $now = date('Y-m-d H:i:s');

        $tuplePage->is_delete = 1;
        $tuplePage->update_time = $now;
        $tuplePage->update();

        Be::getService('App.System.Task')->trigger('Cms.PageSyncCache');
        return true;
    }

    /**
     * 获取菜单参数选择器
     *
     * @return array
     */
    public function getPageMenuPicker(): array
    {
        return [
            'name' => 'id',
            'value' => '自定义页面：{title}',
            'table' => 'cms_page',
            'grid' => [
                'title' => '选择一个自定义页面',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'form' => [
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '标题',
                        ],
                    ],
                ],

                'table' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '标题',
                            'align' => 'left'
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                    ],
                ],
            ]
        ];
    }
}
