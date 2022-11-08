<?php

namespace Be\App\Cms\Service;

use Be\App\ServiceException;
use Be\Be;
use Be\Runtime\RuntimeException;

class Page
{

    /**
     * 获取页面伪静态页网址
     *
     * @param array $params
     * @return string
     * @throws ServiceException
     */
    public function getPageUrl(array $params = []): string
    {
        $page = $this->getPage($params['id']);
        return '/page/' . $page->url;
    }

    /**
     * 获取页面
     *
     * @param string $pageId 页面ID
     * @return object 页面对象
     * @throws ServiceException|RuntimeException
     */
    public function getPage(string $pageId): object
    {
        $cache = Be::getCache();

        $key = 'Cms:Page:' . $pageId;
        $page = $cache->get($key);
        if (!$page) {
            throw new ServiceException('页面不存在！');
        }

        $configTheme = Be::getConfig('App.System.Theme');
        if (!$page->theme || !in_array($page->theme, $configTheme->available)) {
            $page->theme = $configTheme->default;
        }

        $themeName = $page->theme;
        $configTheme = Be::getConfig('App.System.Theme');
        if (!$themeName || !in_array($themeName, $configTheme->available)) {
            $themeName = $configTheme->default;
        }
        $configDefault = Be::getConfig('Theme.' . $themeName . '.Page.Cms.Page.detail');

        if (!$page->config) {
            $page->config = $configDefault;
        } else {
            // 处理部件继承
            $vars = get_object_vars($configDefault);
            foreach ($vars as $key => $val) {
                if (in_array($key, ['north', 'middle', 'west', 'center', 'east', 'south'])) {
                    if (!isset($page->config->$key) || $page->config->$key < 0) {
                        // 方位属性如果是继承的公共的，用负数标记
                        $page->config->$key = -$val;
                    }
                } else {
                    if (!isset($page->config->$key)) {
                        $page->config->$key = $val;
                    }
                }
            }
        }

        // 填充部件模板及配置数据
        $serviceTheme = Be::getService('App.System.Theme');
        foreach (['north', 'middle', 'west', 'center', 'east', 'south'] as $position) {
            if ($page->config->$position !== 0) {
                $property = $position . 'Sections';
                if (isset($page->config->$property) && is_array($page->config->$property) && count($page->config->$property) > 0) {
                    $sections = [];
                    foreach ($page->config->$property as $sectionIndex => $sectionData) {
                        $sectionConfig = $sectionData['config'] ?? null;
                        try {
                            // 该方法私有，待版本升级后切换
                            // $section = $serviceTheme->getSection('Cms.Page.detail', $sectionData['name'], $sectionConfig, $position, $sectionIndex);
                            $section = $this->getSection('Cms.Page.detail', $sectionData['name'], $sectionConfig, $position, $sectionIndex);
                            $section->key = $sectionData['name'];
                            $section->name = $sectionData['name'];
                            $sections[] = $section;
                        } catch (\Throwable $t) {
                        }
                    }
                    $page->config->$property = $sections;
                } else {
                    $page->config->$property = [];
                }
            }
        }

        return $page;
    }


    /**
     * 获取部件
     *
     * @param string $route 页面路由
     * @param string $sectionName 部件名
     * @param object|null $sectionConfig 部件配置数据
     * @param string $position 方位
     * @param int $sectionIndex 部件索引编号
     * @return object
     */
    public function getSection(string $route, string $sectionName, ?object $sectionConfig, string $position, int $sectionIndex): object
    {
        $parts = explode('.', $sectionName);
        $type = array_shift($parts);
        $name = array_shift($parts);
        $classPart = implode('\\', $parts);

        $class = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Template';
        if (!class_exists($class)) {
            throw new ServiceException('Section template (' . $sectionName . '.Template) does not exist!');
        }

        $section = new \stdClass();
        $section->id = 'be-section-' . $position . '-' . $sectionIndex;
        $section->position = $position;
        $section->route = $route;

        $template = new $class();
        $template->id = $section->id;
        $template->position = $position;
        $template->route = $route;

        if ($sectionConfig === null) {
            $class = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Config';
            if (!class_exists($class)) {
                throw new ServiceException('Section config (' . $sectionName . '.Config) does not exist!');
            }

            $sectionConfig = new $class();

            if (isset($sectionConfig->items)) {
                if (count($sectionConfig->items) > 0) {
                    foreach ($sectionConfig->items as &$item) {
                        if (!isset($item['config'])) {
                            $class = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Item\\' . $item['name'];
                            if (!class_exists($class)) {
                                throw new ServiceException('Section item config (' . $sectionName . '.Item.' . $item['name'] . ') does not exist!');
                            }

                            $item['config'] = new $class();
                        }
                    }
                    unset($item);
                }
            }
        } else {
            if (isset($sectionConfig->items)) {
                if (count($sectionConfig->items) > 0) {
                    foreach ($sectionConfig->items as &$item) {
                        if (!isset($item['config'])) {
                            $class = '\\Be\\' . $type . '\\' . $name . '\\Section\\' . $classPart . '\\Item\\' . $item['name'];
                            if (!class_exists($class)) {
                                throw new ServiceException('Section item config  (' . $sectionName . '.Item.' . $item['name'] . ') does not exist!');
                            }

                            $item['config'] = new $class();
                        }
                    }
                    unset($item);
                }
            }
        }

        $template->config = $sectionConfig;

        $section->template = $template;

        return $section;
    }


}
