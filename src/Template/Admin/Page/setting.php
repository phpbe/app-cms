<be-head>
    <?php
    $appSystemWwwUrl = \Be\Be::getProperty('App.System')->getWwwUrl();
    ?>
    <script src="<?php echo $appSystemWwwUrl; ?>/lib/sortable/sortable.min.js"></script>
    <script src="<?php echo $appSystemWwwUrl; ?>/lib/vuedraggable/vuedraggable.umd.min.js"></script>
    <link rel="stylesheet" href="<?php echo $appSystemWwwUrl; ?>/admin/theme-editor/css/setting.css?v=20220915" type="text/css"/>
</be-head>

<be-body>
    <div id="app" v-cloak>

        <header style="grid-area: north;z-index: 9;">

            <div style="display: flex; padding: 5px 10px; box-shadow: 0 0 2px 2px #eee; ">

                <div style="flex: 0 0 auto;line-height: 40px;">
                    主题：
                    <el-radio v-model="themeType" label="0" @change="changeTheme">跟随系统主题设置</el-radio>
                    <el-radio v-model="themeType" label="1">指定主题</el-radio>
                </div>

                <div style="flex: 0 0 auto;line-height: 40px;" v-if="themeType === '1'">
                    <el-select v-model="theme" size="medium" class="be-pl-100" @change="changeTheme">
                        <?php
                        foreach ($this->themeKeyValues as $key => $val) {
                            echo '<el-option value="'. $key .'" key="'. $key .'" label="' .$val . '"></el-option>';
                        }
                        ?>
                    </el-select>
                </div>

                <div style="flex: 1 1 auto;">

                </div>

                <div style="flex: 0 0 auto;">
                    <el-dropdown @command="toggleScreen">
                        <el-button size="medium" style="border: none">
                            <template v-if="screen === 'mobile'">
                                <i class="el-icon-mobile-phone" style="font-size: 1.5rem;"></i>
                            </template>
                            <template v-else-if="screen === 'desktop'">
                                <i class="el-icon-s-platform" style="font-size: 1.5rem;"></i>
                            </template>
                        </el-button>

                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item command="mobile"><i class="el-icon-mobile-phone" style="font-size: 1.2rem;"></i> 手机版</el-dropdown-item>
                            <el-dropdown-item command="desktop"><i class="el-icon-s-platform" style="font-size: 1.2rem;"></i> 桌面版</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>

            </div>
        </header>

        <aside style="grid-area: west; position: relative;">
            <div style="position: absolute; left:0; right:0 ;top: 0; bottom: 30px; overflow-y: auto;">
                <?php
                $positionClasses = [];
                $positionUrls = [];
                foreach (['north', 'middle', 'west', 'center', 'east', 'south'] as $position) {
                    $positionUrls[$position] = beAdminUrl('Cms.Page.editPosition', ['pageId' => $this->pageId, 'position' => $position]);
                    $positionClasses[$position] = '{\'position-' . $position.'\':true,';
                    if ($this->page->config->$position < 0) {
                        $positionClasses[$position] .= '\'position-' . $position.'-extend\':true,';
                    } elseif ($this->page->config->$position === 0) {
                        $positionClasses[$position] .= '\'position-' . $position.'-disable\':true,';
                    } else {
                        $positionClasses[$position] .= '\'position-' . $position.'-enable\':true,';
                    }
                    $positionClasses[$position] .= '\'position-on\':currentPosition === \''.$position.'\'}';
                }

                $totalWidth = 0;
                if ($this->page->config->west !== 0) {
                    $totalWidth += abs($this->page->config->west);
                }

                if ($this->page->config->center !== 0) {
                    $totalWidth += abs($this->page->config->center);
                }

                if ($this->page->config->east !== 0) {
                    $totalWidth += abs($this->page->config->east);
                }

                $widthWest = 25;
                $widthCenter = 50;
                $widthEast = 25;

                $leftWidth = 100;
                if ($totalWidth > 0) {
                    if ($this->page->config->west === 0) {
                        $widthWest = 10;
                        $leftWidth -= 10;
                    }

                    if ($this->page->config->east === 0) {
                        $widthEast = 10;
                        $leftWidth -= 10;
                    }

                    if ($this->page->config->center === 0) {
                        $widthCenter = 10;
                        $leftWidth -= 10;
                    }

                    if ($this->page->config->west !== 0) {
                        $widthWest = (int)($this->page->config->west * $leftWidth / $totalWidth);
                    }

                    if ($this->page->config->east !== 0) {
                        $widthEast = (int)($this->page->config->east * $leftWidth / $totalWidth);
                    }

                    if ($this->page->config->center !== 0) {
                        $widthCenter = (int)($this->page->config->center * $leftWidth / $totalWidth);
                    }
                }
                ?>

                <div class="be-p-100">
                    <div :class="<?php echo $positionClasses['north']; ?>" @click="togglePosition('north', '<?php echo $positionUrls['north']; ?>')">页面顶部（North）</div>

                    <div style="padding: 2px 0;">
                        <div :class="<?php echo $positionClasses['middle']; ?>" @click="togglePosition('middle', '<?php echo $positionUrls['middle']; ?>')">
                            <div class="be-pb-25">页面中部（Middle）</div>
                            <div style="display: flex;">
                                <div style="flex:0 0 <?php echo $widthWest; ?>%"><div :class="<?php echo $positionClasses['west']; ?>" @click.stop="togglePosition('west', '<?php echo $positionUrls['west']; ?>')">左</div></div>
                                <div style="flex:0 0 <?php echo $widthCenter; ?>%"><div style="padding: 0 4px;"><div :class="<?php echo $positionClasses['center']; ?>" @click.stop="togglePosition('center', '<?php echo $positionUrls['center']; ?>')">中</div></div></div>
                                <div style="flex:0 0 <?php echo $widthEast; ?>%"><div :class="<?php echo $positionClasses['east']; ?>" @click.stop="togglePosition('east', '<?php echo $positionUrls['east']; ?>')">右</div></div>
                            </div>
                        </div>
                    </div>

                    <div :class="<?php echo $positionClasses['south']; ?>" @click="togglePosition('south', '<?php echo $positionUrls['south']; ?>')">页面底部（South）</div>
                </div>

                <?php
                foreach (['north', 'middle', 'west', 'center', 'east', 'south'] as $position) {
                    $field = $position . 'Sections';
                    if ($this->page->config->$position < 0 && isset($this->pageDefault->$field)) {
                        ?>
                        <ul class="west-links-disabled <?php echo $position; ?>-west-links" v-if="currentPosition === '<?php echo $position; ?>'">
                            <?php
                            foreach ($this->pageDefault->$field as $section) {
                                ?>
                                <li>
                                    <div style="display: flex">
                                        <div style="flex: 0 0 20px; height: 30px; line-height: 30px;">
                                            <?php
                                            if (isset($section->items) && $section->items) {
                                                ?>
                                                <a href="javascript:void(0);">
                                                    <i class="el-icon-caret-right"></i>
                                                </a>
                                                <?php
                                            }
                                            ?>
                                        </div>

                                        <div style="flex: 1">
                                            <a href="javascript:void(0);">
                                                <span class="icon"><?php echo $section->icon; ?></span><?php echo $section->label; ?>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                        <?php
                        continue;
                    }
                    ?>

                    <ul class="west-links <?php echo $position; ?>-west-links" v-if="currentPosition === '<?php echo $position; ?>'">
                        <draggable v-model="page.<?php echo $position; ?>Sections" handle=".drag-icon" force-fallback="true" group="<?php echo $position; ?>" animation="100" @update="sectionDragUpdate">
                            <transition-group>
                                <li v-for="(section, sectionIndex) in page.<?php echo $position; ?>Sections" :key="sectionIndex" data-position="<?php echo $position; ?>">
                                    <div style="display: flex">
                                        <div style="flex: 0 0 20px; height: 30px; line-height: 30px;">
                                            <a v-if="section.items" href="javascript:void(0);" @click="toggleSectionItems('<?php echo $position; ?>', sectionIndex)">
                                                <i :class="sectionItemsToggle['<?php echo $position; ?>'][sectionIndex] ? 'el-icon-caret-bottom' : 'el-icon-caret-right'"></i>
                                            </a>
                                        </div>

                                        <div style="flex: 1">
                                            <a href="javascript:void(0);" @click="editItem(section.url, '<?php echo $position; ?>', sectionIndex)" :class="activeUrl === section.url ? 'active' : ''">
                                                <span class="icon" v-html="section.icon"></span>{{section.label}}
                                            </a>
                                        </div>

                                        <div class="close-icon" style="flex: 0 0 20px; height: 30px; line-height: 30px;">
                                            <a href="javascript:void(0);" @click="deleteSection('<?php echo $position; ?>', sectionIndex)">
                                                <i class="el-icon-close"></i>
                                            </a>
                                        </div>
                                        <div class="drag-icon" style="flex: 0 0 20px; height: 30px; line-height: 30px;">
                                            <a href="javascript:void(0);">
                                                <i class="el-icon-sort"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <ul v-if="section.items && sectionItemsToggle['<?php echo $position; ?>'][sectionIndex]">
                                        <template v-if="section.items.existItems">
                                            <draggable v-model="section.items.existItems" :disabled="!section.items.newItems" handle=".item-drag-icon" force-fallback="true" :group="'<?php echo $position; ?>' + sectionIndex" animation="100" @update="sectionItemDragUpdate">
                                                <transition-group>
                                                    <li v-for="(existItem, existItemKey) in section.items.existItems" :key="existItemKey" data-position="<?php echo $position; ?>" :data-section-index="sectionIndex">
                                                        <div style="display: flex">
                                                            <div style="flex: 1">
                                                                <a href="javascript:void(0);" @click="editItem(existItem.url, '<?php echo $position; ?>', sectionIndex)" :class="activeUrl === existItem.url ? 'active' : ''">
                                                                    <span class="icon" v-html="existItem.icon"></span>{{existItem.label}}
                                                                </a>
                                                            </div>

                                                            <div class="item-close-icon" style="flex: 0 0 20px; height: 30px; line-height: 30px;">
                                                                <a v-if="section.items.newItems" href="javascript:void(0);" @click="deleteSectionItem('<?php echo $position; ?>', sectionIndex, existItemKey)">
                                                                    <i class="el-icon-close"></i>
                                                                </a>
                                                            </div>
                                                            <div class="item-drag-icon" style="flex: 0 0 20px; height: 30px; line-height: 30px;">
                                                                <a href="javascript:void(0);">
                                                                    <i class="el-icon-sort"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </transition-group>
                                            </draggable>

                                        </template>

                                        <li v-if="section.items.newItems">
                                            <el-dropdown @command="addSectionItem">
                                                <span class="el-dropdown-link">
                                                    <i class="el-icon-circle-plus"></i>
                                                    新增子部件
                                                </span>
                                                <el-dropdown-menu slot="dropdown">
                                                    <el-dropdown-item v-for="(newItem, newItemKey) in section.items.newItems" :command="newItem.url">
                                                        <span class="icon" v-html="newItem.icon"></span>
                                                        {{newItem.label}}
                                                    </el-dropdown-item>
                                                </el-dropdown-menu>
                                            </el-dropdown>
                                        </li>
                                    </ul>
                                </li>

                            </transition-group>
                        </draggable>


                        <?php
                        if ($this->page->config->$position > 0) {
                            ?>
                            <li style="padding-left: 25px; margin-top: 10px">

                                <el-button type="primary" size="mini" icon="el-icon-plus" @click="sectionDrawerToggle.<?php echo $position; ?> = true">新增部件</el-button>

                                <el-drawer
                                        title="新增部件"
                                        :visible.sync="sectionDrawerToggle.<?php echo $position; ?>"
                                        direction="ltr">
                                    <div class="be-px-100">
                                        <el-tabs type="card">
                                            <el-tab-pane label="主题部件" v-if="page.<?php echo $position; ?>AvailableSections.themeSections.length > 0">
                                                <el-tabs tab-position="left">
                                                    <template v-for="availableSections in page.<?php echo $position; ?>AvailableSections.themeSections">
                                                        <el-tab-pane :label="availableSections.theme.label">
                                                            <div class="be-px-100" v-for="section in availableSections.sections">
                                                                <a href="javascript:void(0);" @click="addSection('<?php echo $position; ?>', section.name)">
                                                                    <span class="icon" v-html="section.icon"></span> {{section.label}}
                                                                </a>
                                                            </div>
                                                        </el-tab-pane>
                                                    </template>
                                                </el-tabs>
                                            </el-tab-pane>
                                            <el-tab-pane label="应用部件" v-if="page.<?php echo $position; ?>AvailableSections.appSections.length > 0">
                                                <el-tabs tab-position="left">
                                                    <template v-for="availableSections in page.<?php echo $position; ?>AvailableSections.appSections">
                                                        <el-tab-pane :label="availableSections.app.label">
                                                            <div class="be-px-100" v-for="section in availableSections.sections">
                                                                <a href="javascript:void(0);" @click="addSection('<?php echo $position; ?>', section.name)">
                                                                    <span class="icon" v-html="section.icon"></span> {{section.label}}
                                                                </a>
                                                            </div>
                                                        </el-tab-pane>
                                                    </template>
                                                </el-tabs>
                                            </el-tab-pane>
                                        </el-tabs>

                                    </div>
                                </el-drawer>
                            </li>
                            <?php
                        }
                        ?>

                    </ul>
                    <?php
                }
                ?>

            </div>

            <div style="position: absolute;left:0; right:0; bottom:0; height: 35px; line-height: 35px; background-color: #fff; ">

            </div>
        </aside>

        <main style="grid-area: center; background-color: #fafafa; padding: 15px;">
            <div :class="'preview-' + this.screen" style="height: 100%; box-shadow: 0 0 2px 2px #eee">
                <iframe :src="previewUrl" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </main>

        <aside style="grid-area: east; position: relative;">
            <div style="position: absolute; left:0; right:0 ;top: 0; bottom: 0; overflow: hidden;">
                <iframe name="frame-setting" id="frame-setting" :src="activeUrl" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </aside>

    </div>


    <?php
    $sectionDrawerToggle = [];
    $sectionItemsToggle = [];
    foreach (['north', 'middle', 'west', 'center', 'east', 'south'] as $position) {
        $field = $position.'Sections';
        $sectionDrawerToggle[$position] = 0;
        if (isset($this->page->config->$field)) {
            foreach ($this->page->config->$field as $sectionIndex => $sectionName) {
                $sectionItemsToggle[$position][$sectionIndex] = 0;
            }
        }
    }
    ?>

    <script>
        Vue.component('vuedraggable', window.vuedraggable);

        var vue = new Vue({
            el: '#app',
            components: {
                vuedraggable: window.vuedraggable,//当前页面注册部件
            },
            data: {
                pageId : "<?php echo $this->pageId; ?>",
                page : <?php echo json_encode($this->page); ?>,

                themeType: "<?php echo $this->page->theme === '' ? 0 : 1; ?>",
                theme: "<?php echo $this->page->theme; ?>",

                currentPosition: "",

                sectionDrawerToggle: <?php echo json_encode($sectionDrawerToggle); ?>,
                sectionItemsToggle: <?php echo json_encode($sectionItemsToggle); ?>,

                activeUrl: "<?php echo beAdminUrl('Cms.Page.editPage', ['pageId' => $this->pageId]); ?>",
                previewUrl: "<?php echo $this->page->desktopPreviewUrl; ?>",
                previewUrlTag: "",
                screen: "desktop"
            },
            methods: {
                toggleScreen: function (command) {
                    this.screen = command;
                    switch (this.screen) {
                        case "desktop":
                            this.previewUrl = this.page.desktopPreviewUrl + this.previewUrlTag;
                            break;
                        case "mobile":
                            this.previewUrl = this.page.mobilePreviewUrl + this.previewUrlTag;
                            break;
                    }
                },
                reloadPreviewFrame: function() {
                    var randomParam;
                    switch (this.screen) {
                        case "desktop":
                            randomParam = (this.page.desktopPreviewUrl.indexOf('?') === -1 ? '?_=' : '&_=') + Math.random();
                            this.previewUrl = this.page.desktopPreviewUrl + randomParam + this.previewUrlTag;
                            break;
                        case "mobile":
                            randomParam = (this.page.mobilePreviewUrl.indexOf('?') === -1 ? '?_=' : '&_=') + Math.random();
                            this.previewUrl = this.page.mobilePreviewUrl + randomParam + this.previewUrlTag;
                            break;
                    }
                },
                reloadPreviewFrameSection: function($section) {

                },
                changeTheme: function (theme) {
                    var _this = this;
                    var loading = _this.$loading();
                    _this.$http.post("<?php echo beAdminUrl('Cms.Page.changeTheme', ['pageId' => $this->pageId]); ?>", {
                        themeType: _this.themeType,
                        theme: theme
                    }).then(function (response) {
                        loading.close();
                        if (response.status === 200) {
                            if (response.data.success) {
                                _this.reloadPreviewFrame();
                            } else {
                                _this.$message.error(response.data.message);
                            }
                        }
                    }).catch(function (error) {
                        loading.close();
                        _this.$message.error(error);
                    });
                },
                togglePosition: function (position, url) {
                    this.currentPosition = position;
                    this.activeUrl = url;
                },
                toggleSectionItems: function(position, sectionIndex) {
                    this.sectionItemsToggle[position][sectionIndex] = !this.sectionItemsToggle[position][sectionIndex];
                    this.$forceUpdate();
                    //console.log(this.page);
                },
                addSection: function(position, sectionName) {
                    var _this = this;
                    var loading = _this.$loading();
                    _this.$http.post("<?php echo beAdminUrl('Cms.Page.addSection', ['pageId' => $this->pageId]); ?>", {
                        position: position,
                        sectionName: sectionName
                    }).then(function (response) {
                        loading.close();
                        if (response.status === 200) {
                            if (response.data.success) {
                                _this.sectionDrawerToggle[position] = false;
                                _this.page = response.data.page;
                                _this.reloadPreviewFrame();
                            } else {
                                _this.$message.error(response.data.message);
                            }
                        }
                    }).catch(function (error) {
                        loading.close();
                        _this.$message.error(error);
                    });
                },
                deleteSection: function(position, sectionIndex) {
                    var _this = this;
                    this.$confirm('确定要删除部件吗?', '删除确认', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(function () {
                        var loading = _this.$loading();
                        _this.$http.post("<?php echo beAdminUrl('Cms.Page.deleteSection', ['pageId' => $this->pageId]); ?>", {
                            position: position,
                            sectionIndex: sectionIndex
                        }).then(function (response) {
                            loading.close();
                            if (response.status === 200) {
                                if (response.data.success) {
                                    _this.page = response.data.page;
                                    _this.reloadPreviewFrame();
                                } else {
                                    _this.$message.error(response.data.message);
                                }
                            }
                        }).catch(function (error) {
                            loading.close();
                            _this.$message.error(error);
                        });
                    }).catch(function () {
                    });
                },
                sectionDragUpdate: function(event) {
                    //console.log(event);
                    //return;
                    var loading = this.$loading();

                    var _this = this;
                    _this.$http.post("<?php echo beAdminUrl('Cms.Page.sortSection', ['pageId' => $this->pageId]); ?>", {
                        position: event.item.dataset.position,
                        oldIndex: event.oldIndex,
                        newIndex: event.newIndex,
                    }).then(function (response) {
                        loading.close();
                        if (response.status === 200) {
                            if (response.data.success) {
                                _this.page = response.data.page;
                            } else {
                                _this.$message.error(response.data.message);
                            }
                        }
                    }).catch(function (error) {
                        loading.close();
                        _this.$message.error(error);
                    });
                },
                editItem: function(url, position, sectionIndex) {
                    this.activeUrl = url;

                    var previewUrlTag = "#be-section-" + position + "-" + sectionIndex;
                    if (this.previewUrlTag !== previewUrlTag) {
                        this.previewUrlTag = previewUrlTag;
                        this.reloadPreviewFrame();
                    }
                },
                addSectionItem:  function (command) {
                    var loading = this.$loading();

                    var _this = this;
                    _this.$http.get(command).then(function (response) {
                        loading.close();
                        if (response.status === 200) {
                            if (response.data.success) {
                                _this.page = response.data.page;
                            } else {
                                _this.$message.error(response.data.message);
                            }
                        }
                    }).catch(function (error) {
                        loading.close();
                        _this.$message.error(error);
                    });

                    console.log(command);
                },
                deleteSectionItem: function(position, sectionIndex, itemIndex) {
                    var _this = this;
                    this.$confirm('确定要删除子部件吗?', '删除确认', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(function () {
                        var loading = _this.$loading();
                        _this.$http.post("<?php echo beAdminUrl('Cms.Page.deleteSectionItem', ['pageId' => $this->pageId]); ?>", {
                            position: position,
                            sectionIndex: sectionIndex,
                            itemIndex: itemIndex
                        }).then(function (response) {
                            loading.close();
                            if (response.status === 200) {
                                if (response.data.success) {
                                    _this.page = response.data.page;
                                } else {
                                    _this.$message.error(response.data.message);
                                }
                            }
                        }).catch(function (error) {
                            loading.close();
                            _this.$message.error(error);
                        });
                    }).catch(function () {
                    });
                },
                sectionItemDragUpdate: function(event) {
                    console.log(event);
                    //return;
                    var loading = this.$loading();

                    var _this = this;
                    _this.$http.post("<?php echo beAdminUrl('Cms.Page.sortSectionItem', ['pageId' => $this->pageId]); ?>", {
                        position: event.item.dataset.position,
                        sectionIndex: event.item.dataset.sectionIndex,
                        oldIndex: event.oldIndex,
                        newIndex: event.newIndex,
                    }).then(function (response) {
                        loading.close();
                        if (response.status === 200) {
                            if (response.data.success) {
                                _this.page = response.data.page;
                            } else {
                                _this.$message.error(response.data.message);
                            }
                        }
                    }).catch(function (error) {
                        loading.close();
                        _this.$message.error(error);
                    });
                }
            }
        });

        function reloadPreviewFrame () {
            vue.reloadPreviewFrame();
        }

        function reload () {
            window.location.reload();
        }
    </script>

</be-body>