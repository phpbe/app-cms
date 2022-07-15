<be-head>
    <script src="<?php echo \Be\Be::getProperty('App.Cms')->getWwwUrl(); ?>/admin/js/pinyin.js"></script>
    <style>
        .el-form-item {
            margin-bottom: inherit;
        }

        .el-form-item__content {
            line-height: inherit;
        }

        .el-tooltip {
            cursor: pointer;
        }

        .el-tooltip:hover {
            color: #409EFF;
        }
    </style>
</be-head>


<be-north>
    <div class="be-north" id="be-north">
        <div class="be-row">
            <div class="be-col">
                <div style="padding: 1.25rem 0 0 2rem;">
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Cms.Category.categories'); ?>">返回文章分类列表</el-link>
                </div>
            </div>
            <div class="be-col-auto">
                <div style="padding: .75rem 2rem 0 0;">
                    <el-button size="medium" :disabled="loading" @click="vueCenter.cancel();">取消</el-button>
                    <el-button size="medium" type="primary" :disabled="loading" @click="vueCenter.save();">保存</el-button>
                </div>
            </div>
        </div>
    </div>
    <script>
        let vueNorth = new Vue({
            el: '#be-north',
            data: {
                loading: false,
            }
        });
    </script>
</be-north>


<be-page-content>
    <?php
    $js = [];
    $css = [];
    $formData = [];
    $vueData = [];
    $vueMethods = [];
    $vueHooks = [];

    $rootUrl = \Be\Be::getRequest()->getRootUrl();
    ?>

    <div id="app" v-cloak>
        <el-form ref="formRef" :model="formData" class="be-mb-400">
            <?php
            $formData['id'] = ($this->category ? $this->category->id : '');
            ?>

            <div class="be-row">
                <div class="be-col-24 be-col-md-18">
                    <div class="be-center-box">
                        <div><span class="be-c-red">*</span> 名称：</div>
                        <el-form-item class="be-mt-50" prop="name" :rules="[{required: true, message: '请输入名称', trigger: 'change' }]">
                            <el-input
                                    type="text"
                                    placeholder="请输入名称"
                                    v-model = "formData.name"
                                    size="medium"
                                    maxlength="120"
                                    show-word-limit
                                    @change="seoUpdate">
                            </el-input>
                        </el-form-item>
                        <?php $formData['name'] = ($this->category ? $this->category->name : ''); ?>

                        <div class="be-mt-100">描述：</div>
                        <?php
                        $driver = new \Be\AdminPlugin\Form\Item\FormItemTinymce([
                            'name' => 'description',
                            'ui' => [
                                'form-item' => [
                                    'class' => 'be-mt-50'
                                ],
                                '@change' => 'seoUpdate',
                            ]
                        ]);
                        echo $driver->getHtml();

                        $formData['description'] = ($this->category ? $this->category->description : '');

                        $jsX = $driver->getJs();
                        if ($jsX) {
                            $js = array_merge($js, $jsX);
                        }

                        $cssX = $driver->getCss();
                        if ($cssX) {
                            $css = array_merge($css, $cssX);
                        }

                        $vueDataX = $driver->getVueData();
                        if ($vueDataX) {
                            $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                        }

                        $vueMethodsX = $driver->getVueMethods();
                        if ($vueMethodsX) {
                            $vueMethods = array_merge($vueMethods, $vueMethodsX);
                        }

                        $vueHooksX = $driver->getVueHooks();
                        if ($vueHooksX) {
                            foreach ($vueHooksX as $k => $v) {
                                if (isset($vueHooks[$k])) {
                                    $vueHooks[$k] .= "\r\n" . $v;
                                } else {
                                    $vueHooks[$k] = $v;
                                }
                            }
                        }
                        ?>
                    </div>
                </div>


                <div class="be-col-24 be-col-md-6 be-pl-150">

                    <div class="be-center-box">
                        <div class="be-row">
                            <div class="be-col be-lh-250">排序：</div>
                            <div class="be-col-auto">
                                <el-form-item prop="ordering">
                                    <el-input-number
                                            v-model = "formData.ordering"
                                            size="medium">
                                    </el-input-number>
                                </el-form-item>
                            </div>
                        </div>
                        <?php $formData['ordering'] = ($this->category ? $this->category->ordering : ''); ?>
                    </div>

                    <div class="be-center-box be-mt-200">
                        <div class="be-row">
                            <div class="be-col">
                                <div class="be-center-box-title">
                                    SEO（搜索引擎优化）
                                </div>
                            </div>
                            <div class="be-col-auto">
                                <el-link type="primary" @click="drawerSeo=true">编辑</el-link>
                            </div>
                        </div>

                        <div class="be-mt-100 be-t-break be-c-999 be-fs-80"><?php echo $rootUrl; ?>/articles/{{formData.url}}</div>
                        <div class="be-mt-100">{{formData.seo_title}}</div>
                        <div class="be-mt-100 be-t-ellipsis-2">{{formData.seo_description}}</div>
                    </div>
                </div>
            </div>


        </el-form>

        <el-drawer
                :visible.sync="drawerSeo"
                title="搜索引擎优化"
                size="40%"
                :wrapper-closable="false"
                :destroy-on-close="true">

            <div class="be-px-150">

                <div>
                    <el-checkbox v-model.number="formData.seo" :true-label="1" :false-label="0">独立编辑</el-checkbox>
                    <el-tooltip effect="dark" content="单独编辑SEO后,SEO信息不随标题和描述改动" placement="top">
                        <i class="el-icon-fa fa-question-circle-o"></i>
                    </el-tooltip>
                </div>
                <?php
                $formData['seo'] = ($this->category ? $this->category->seo : 0);
                ?>

                <div class="be-mt-150">
                    SEO标题
                    <el-tooltip effect="dark" content="标题是SEO最重要的部分，该标题会显示在搜索引擎的搜索结果中。" placement="top">
                        <i class="el-icon-fa fa-question-circle-o"></i>
                    </el-tooltip>
                </div>
                <el-input
                        class="be-mt-50"
                        type="text"
                        placeholder="请输入SEO标题"
                        v-model = "formData.seo_title"
                        size="medium"
                        maxlength="120"
                        show-word-limit
                        :disabled="formData.seo === 0">
                </el-input>
                <?php
                $formData['seo_title'] = ($this->category ? $this->category->seo_title : '');
                ?>

                <div class="be-mt-150">
                    SEO描述
                    <el-tooltip effect="dark" content="这是该文章分类的整体SEO描述，可以添加一些文章分类描述，使文章分类在搜索引擎中获得更高的排名。" placement="top">
                        <i class="el-icon-fa fa-question-circle-o"></i>
                    </el-tooltip>
                </div>
                <el-input
                        class="be-mt-50"
                        type="textarea"
                        :rows="6"
                        placeholder="请输入SEO描述"
                        v-model = "formData.seo_description"
                        size="medium"
                        maxlength="500"
                        show-word-limit
                        :disabled="formData.seo === 0">
                </el-input>
                <?php
                $formData['seo_description'] = ($this->category ? $this->category->seo_description : '');
                ?>


                <div class="be-mt-150">
                    SEO友好链接
                </div>
                <el-input
                        class="be-mt-50"
                        type="text"
                        placeholder="请输入SEO友好链接"
                        v-model = "formData.url"
                        size="medium"
                        maxlength="200"
                        show-word-limit
                        :disabled="formData.seo === 0">
                    <template slot="prepend"><?php echo $rootUrl; ?>/articles/</template>
                </el-input>
                <?php
                $formData['url'] = ($this->category ? $this->category->url : '');
                ?>

                <div class="be-mt-150">
                    SEO关键词
                    <el-tooltip effect="dark" content="关键词可以提高搜索结果排名，建议1-2个关键词即可，堆砌关键词可能会降低排名！" placement="top">
                        <i class="el-icon-fa fa-question-circle-o"></i>
                    </el-tooltip>
                </div>
                <el-input
                        class="be-mt-50"
                        type="text"
                        placeholder="请输入SEO关键词，多个关键词以逗号分隔。"
                        v-model = "formData.seo_keywords"
                        size="medium"
                        maxlength="60">
                </el-input>
                <?php
                $formData['seo_keywords'] = ($this->category ? $this->category->seo_keywords : '');
                ?>

                <div class="be-mt-150 be-ta-right">
                    <el-button size="medium" type="primary" @click="drawerSeo=false">确定</el-button>
                </div>

            </div>

        </el-drawer>

    </div>

    <?php
    if (count($js) > 0) {
        $js = array_unique($js);
        foreach ($js as $x) {
            echo '<script src="' . $x . '"></script>';
        }
    }

    if (count($css) > 0) {
        $css = array_unique($css);
        foreach ($css as $x) {
            echo '<link rel="stylesheet" type="text/css" href="' . $x . '" />';
        }
    }
    ?>

    <script>
        let vueCenter = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>,
                loading: false,

                drawerSeo: false,

                t: false
                <?php
                if ($vueData) {
                    foreach ($vueData as $k => $v) {
                        echo ',' . $k . ':' . json_encode($v);
                    }
                }
                ?>
            },
            methods: {
                save: function () {
                    let _this = this;
                    this.$refs["formRef"].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            vueNorth.loading = true;
                            _this.$http.post("<?php echo beAdminUrl('Cms.Category.' . ($this->category ? 'edit' :'create')); ?>", {
                                formData: _this.formData
                            }).then(function (response) {
                                _this.loading = false;
                                vueNorth.loading = false;
                                //console.log(response);
                                if (response.status === 200) {
                                    var responseData = response.data;
                                    if (responseData.success) {
                                        _this.$message.success(responseData.message);
                                        setTimeout(function () {
                                            window.onbeforeunload = null;
                                            window.location.href = "<?php echo beAdminUrl('Cms.Category.categories'); ?>";
                                        }, 1000);
                                    } else {
                                        if (responseData.message) {
                                            _this.$message.error(responseData.message);
                                        } else {
                                            _this.$message.error("服务器返回数据异常！");
                                        }
                                    }
                                }
                            }).catch(function (error) {
                                _this.loading = false;
                                vueNorth.loading = false;
                                _this.$message.error(error);
                            });
                        } else {
                            return false;
                        }
                    });
                },
                cancel: function () {
                    window.onbeforeunload = null;
                    window.location.href = "<?php echo beAdminUrl('Cms.Category.categories'); ?>";
                },

                seoUpdate: function () {
                    if (this.formData.seo === 0) {
                        this.formData.seo_title = this.formData.name;

                        let seoDescription = this.formData.description;
                        seoDescription = seoDescription.replace(/<[^>]+>/g,"");
                        seoDescription = seoDescription.replace("\r", " ");
                        seoDescription = seoDescription.replace("\n", " ");
                        if (seoDescription.length > 500) {
                            seoDescription = seoDescription.substr(0, 500);
                        }
                        this.formData.seo_description = seoDescription;

                        let name = this.formData.name.toLowerCase();
                        let url = Pinyin.convert(name, "-");
                        if (url.length > 200) {
                            url = Pinyin.convert(name, "-", true);
                            if (url.length > 200) {
                                url = Pinyin.convert(name, "", true);
                            }
                        }
                        this.formData.url = url;
                    }
                },

                <?php
                if ($vueMethods) {
                    foreach ($vueMethods as $k => $v) {
                        echo ',' . $k . ':' . $v;
                    }
                }
                ?>
            },
            created: function () {
                <?php
                if (isset($vueHooks['created'])) {
                    echo $vueHooks['created'];
                }
                ?>
            },
            mounted: function () {
                window.onbeforeunload = function(e) {
                    e = e || window.event;
                    if (e) {
                        e.returnValue = "";
                    }
                    return "";
                }
                <?php
                if (isset($vueHooks['mounted'])) {
                    echo $vueHooks['mounted'];
                }
                ?>
            },
            updated: function () {
                <?php
                if (isset($vueHooks['updated'])) {
                    echo $vueHooks['updated'];
                }
                ?>
            }
            <?php
            if (isset($vueHooks['beforeCreate'])) {
                echo ',beforeCreate: function () {' . $vueHooks['beforeCreate'] . '}';
            }

            if (isset($vueHooks['beforeMount'])) {
                echo ',beforeMount: function () {' . $vueHooks['beforeMount'] . '}';
            }

            if (isset($vueHooks['beforeUpdate'])) {
                echo ',beforeUpdate: function () {' . $vueHooks['beforeUpdate'] . '}';
            }

            if (isset($vueHooks['beforeDestroy'])) {
                echo ',beforeDestroy: function () {' . $vueHooks['beforeDestroy'] . '}';
            }

            if (isset($vueHooks['destroyed'])) {
                echo ',destroyed: function () {' . $vueHooks['destroyed'] . '}';
            }
            ?>
        });
    </script>

</be-page-content>