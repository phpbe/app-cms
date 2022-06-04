<be-head>
    <script src="<?php echo \Be\Be::getProperty('App.Cms')->getUrl(); ?>/Template/Admin/js/pinyin.js"></script>
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

        .dialog-image-selector .el-dialog__body {
            padding: 0;
        }

        .image, .image-selector {
            display: inline-block;
            width: 148px;
            height: 148px;
            margin: 0 8px 8px 0;
            border: 1px dashed #c0ccda;
            border-radius: 6px;
            overflow: hidden;
            line-height: 148px;
            position: relative;
            text-align: center;
        }

        .image-selector {
            cursor: pointer;
        }
        .image-selector:hover {
            border-color: #409eff;
        }

        .image-selector i {
            font-size: 28px;
            color: #8c939d;
        }

        .image img {
            max-width: 100%;
            vertical-align: middle;
        }

        .image .image-actions {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 32px;
            line-height: 32px;
            background-color: rgba(0, 0, 0, 0.5);
            text-align: center;
            cursor: default;
            transition: all 0.3s ease;
            opacity: 0;
        }

        .image:hover .image-actions {
            opacity: 1;
        }

        .image .image-action {
            color: #ddd;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</be-head>


<be-north>
    <div class="be-north" id="be-north">
        <div class="be-row">
            <div class="be-col">
                <div style="padding: 1.25rem 0 0 2rem;">
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Cms.Article.articles'); ?>">返回文章列表</el-link>
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


<be-center>
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
        <div class="be-center">
            <div class="be-center-title"><?php echo $this->title; ?></div>
            <el-form ref="formRef" :model="formData" class="be-mb-400">
                <?php
                $formData['id'] = ($this->article ? $this->article->id : '');
                ?>

                <div class="be-row">
                    <div class="be-col-24 be-col-md-18">
                        <div class="be-center-box">

                            <div><span class="be-c-red">*</span> 标题：</div>
                            <el-form-item class="be-mt-50" prop="title" :rules="[{required: true, message: '请输入标题', trigger: 'change' }]">
                                <el-input
                                        type="text"
                                        placeholder="请输入标题"
                                        v-model = "formData.title"
                                        size="medium"
                                        maxlength="200"
                                        show-word-limit
                                        @change="seoUpdate">
                                </el-input>
                            </el-form-item>
                            <?php $formData['title'] = ($this->article ? $this->article->title : ''); ?>

                            <div class="be-mt-100">摘要：</div>
                            <el-form-item class="be-mt-50" prop="summary">
                                <el-input
                                        type="textarea"
                                        :autosize="{minRows:3,maxRows:6}"
                                        placeholder="请输入摘要"
                                        v-model="formData.summary"
                                        size="medium"
                                        maxlength="500"
                                        show-word-limit
                                        @change="seoUpdate">
                                </el-input>
                            </el-form-item>
                            <?php $formData['summary'] = ($this->article ? $this->article->summary : ''); ?>

                            <div class="be-mt-100">描述：</div>
                            <?php
                            $driver = new \Be\AdminPlugin\Form\Item\FormItemTinymce([
                                'name' => 'description',
                                'ui' => [
                                    'form-item' => [
                                        'class' => 'be-mt-50'
                                    ],
                                    '@change' => 'seoUpdate',
                                ],
                                'option' => [
                                    'height' => 600,
                                ],
                            ]);
                            echo $driver->getHtml();

                            $formData['description'] = ($this->article ? $this->article->description : '');

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
                                <div class="be-col">是否发布：</div>
                                <div class="be-col-auto">
                                    <el-form-item prop="is_enable">
                                        <el-switch v-model.number="formData.is_enable" :active-value="1" :inactive-value="0" size="medium"></el-switch>
                                    </el-form-item>
                                </div>
                            </div>
                            <?php $formData['is_enable'] = ($this->article ? $this->article->is_enable : 0); ?>


                            <div class="be-mt-150">封面图片：</div>
                            <div class="be-row be-mt-50">
                                <div class="be-col-auto">
                                    <div v-if="formData.image !== ''" :key="formData.image" class="image">
                                        <img :src="formData.image">
                                        <div class="image-actions">
                                            <span class="image-action" @click="imagePreview()"><i class="el-icon-zoom-in"></i></span>
                                            <span class="image-action" @click="imageRemove()"><i class="el-icon-delete"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="formData.image === ''" class="be-col-auto">
                                    <div class="image-selector" @click="imageSelect" key="99999">
                                        <i class="el-icon-plus"></i>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $formData['image'] = ($this->article ? $this->article->image : '');
                            ?>

                            <el-dialog :visible.sync="imageSelectorVisible" class="dialog-image-selector" title="选择主图" :width="600" :close-on-click-modal="false">
                                <iframe :src="imageSelectorUrl" style="width:100%;height:400px;border:0;}"></iframe>
                            </el-dialog>

                            <el-dialog :visible.sync="imagePreviewVisible" center="true">
                                <div class="be-ta-center">
                                    <img style="max-width: 100%;max-height: 400px;" :src="formData.image" alt="">
                                </div>
                            </el-dialog>


                            <div class="be-mt-150">
                                分类：
                            </div>
                            <el-form-item class="be-mt-50" prop="category_ids">
                                <el-select
                                        v-model="formData.category_ids"
                                        multiple
                                        placeholder="请选择分类"
                                        size="medium">
                                    <?php
                                    foreach ($this->categoryKeyValues as $key => $val) {
                                        echo '<el-option value="'. $key .'" key="'. $key .'" label="' .$val . '"></el-option>';
                                    }
                                    ?>
                                </el-select>
                            </el-form-item>
                            <?php
                            $formData['category_ids'] = ($this->article ? $this->article->category_ids : []);
                            ?>

                            <div class="be-mt-150">
                                标签：
                            </div>
                            <div v-if="formData.tags">
                                  <el-tag
                                        v-for="tag in formData.tags"
                                        :key="tag"
                                        closable
                                        @close="removeTag(tag)"
                                        class="be-mr-50 be-mt-50"
                                        size="medium">
                                    {{tag}}
                                </el-tag>
                            </div>
                            <el-form-item class="be-mt-50" v-if="formData.tags.length <= 60">
                                <el-input
                                        type="text"
                                        placeholder="添加标签（回车确认输入）"
                                        v-model="formItems.tags.currentTag"
                                        maxlength="60"
                                        size="medium"
                                        show-word-limit
                                        @change="addTag">
                                </el-input>
                            </el-form-item>
                            <?php
                            $formData['tags'] = ($this->article ? $this->article->tags : []);
                            $vueDataX = [
                                    'formItems' => [
                                            'tags' => ['currentTag' => '']
                                    ]
                            ];
                            $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                            ?>

                            <div class="be-mt-150">
                                作者：
                            </div>
                            <el-form-item class="be-mt-50" prop="author">
                                <el-form-item prop="author">
                                    <el-input v-model="formData.author" size="medium"></el-input>
                                </el-form-item>
                            </el-form-item>
                            <?php
                            $formData['author'] = ($this->article ? $this->article->author : \Be\Be::getAdminUser()->name);
                            ?>

                            <div class="be-mt-150">
                                发布时间：
                            </div>
                            <el-form-item class="be-mt-50" prop="publish_time">
                                <el-form-item prop="publish_time">
                                    <el-date-picker type="datetime" v-model="formData.publish_time" size="medium" placeholder="选择发布时间"></el-date-picker>
                                </el-form-item>
                            </el-form-item>
                            <?php
                            $formData['publish_time'] = ($this->article ? $this->article->publish_time : date('Y-m-d H:i:s'));
                            ?>

                            <div class="be-row be-mt-150">
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
                            <?php $formData['ordering'] = ($this->article ? $this->article->ordering : ''); ?>

                        </div>

                        <div class="be-center-box be-mt-150">

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

                            <div class="be-mt-100 be-t-break be-c-999 be-fs-80"><?php echo $rootUrl; ?>/article/{{formData.url}}</div>
                            <div class="be-mt-100">{{formData.seo_title}}</div>
                            <div class="be-mt-100 be-t-ellipsis-2">{{formData.seo_description}}</div>

                        </div>
                    </div>
                </div>

            </el-form>
        </div>

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
                $formData['seo'] = ($this->article ? $this->article->seo : 0);
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
                        maxlength="200"
                        show-word-limit
                        :disabled="formData.seo === 0">
                </el-input>
                <?php
                $formData['seo_title'] = ($this->article ? $this->article->seo_title : '');
                ?>

                <div class="be-mt-150">
                    SEO描述
                    <el-tooltip effect="dark" content="这是该商品的整体SEO描述，可以添加一些商品描述，使商品在搜索引擎中获得更高的排名。" placement="top">
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
                $formData['seo_description'] = ($this->article ? $this->article->seo_description : '');
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
                    <template slot="prepend"><?php echo $rootUrl; ?>/article/</template>
                </el-input>
                <?php
                $formData['url'] = ($this->article ? $this->article->url : '');
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
                $formData['seo_keywords'] = ($this->article ? $this->article->seo_keywords : '');
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

                imageSelectorVisible: false,
                imagePreviewVisible: false,
                imageAltVisible: false,
                imageSelectorUrl: "about:blank",

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
                            _this.$http.post("<?php echo beAdminUrl('Cms.Article.' . ($this->article ? 'edit' :'create')); ?>", {
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
                                            window.location.href = "<?php echo beAdminUrl('Cms.Article.articles'); ?>";
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
                    window.location.href = "<?php echo beAdminUrl('Cms.Article.articles'); ?>";
                },
                addTag: function () {
                    if (this.formItems.tags.currentTag) {
                        if (this.formData.tags.indexOf(this.formItems.tags.currentTag) === -1) {
                            this.formData.tags.push(this.formItems.tags.currentTag);
                        }

                        this.formItems.tags.currentTag = "";
                    }
                },
                removeTag: function (tag) {
                    this.formData.tags.splice(this.formData.tags.indexOf(tag), 1);
                },

                seoUpdate: function () {
                    if (this.formData.seo === 0) {
                        this.formData.seo_title = this.formData.title;

                        let seoDescription;
                        if (this.formData.summary) {
                            seoDescription = this.formData.summary;
                        } else {
                            seoDescription = this.formData.description;
                            seoDescription = seoDescription.replace(/<[^>]*>/g,"");
                            seoDescription = seoDescription.replace("\r", " ");
                            seoDescription = seoDescription.replace("\n", " ");
                        }
                        if (seoDescription.length > 500) {
                            seoDescription = seoDescription.substr(0, 500);
                        }
                        this.formData.seo_description = seoDescription;

                        let title = this.formData.title.toLowerCase();
                        let url = Pinyin.convert(title, "-");
                        if (url.length > 200) {
                            url = Pinyin.convert(title, "-", true);
                            if (url.length > 200) {
                                url = Pinyin.convert(title, "", true);
                            }
                        }
                        this.formData.url = url;
                    }
                },

                imageSelect: function () {
                    this.imageSelectorVisible = true;
                    <?php
                    $imageCallback = base64_encode('parent.imageSelected(files);');
                    $imageSelectorUrl = beAdminUrl('System.Storage.pop', ['filterImage' => 1, 'callback' => $imageCallback]);
                    ?>
                    let imageSelectorUrl = "<?php echo $imageSelectorUrl; ?>";
                    imageSelectorUrl += imageSelectorUrl.indexOf("?") === -1 ? "?" : "&"
                    imageSelectorUrl += "_=" + Math.random();
                    this.imageSelectorUrl = imageSelectorUrl;
                },
                imageSelected: function (files) {
                    if (files.length > 0) {
                        let file = files[0];
                        this.formData.image = file.url;
                        this.imageSelectorVisible = false;
                        this.imageSelectorUrl = "about:blank";
                    }
                },
                imagePreview: function () {
                    this.imagePreviewVisible = true;
                },
                imageRemove: function () {
                    this.formData.image = "";
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

        function imageSelected(files) {
            vueCenter.imageSelected(files);
        }

    </script>

</be-center>