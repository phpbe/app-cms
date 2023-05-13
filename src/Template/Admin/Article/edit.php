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
    <div id="app-north">
        <div class="be-row">
            <div class="be-col">
                <div style="padding: 1.25rem 0 0 2rem;">
                    <el-link icon="el-icon-back" href="<?php echo $this->backUrl; ?>">返回文章列表</el-link>
                </div>
            </div>
            <div class="be-col-auto">
                <div style="padding: .75rem 2rem 0 0;">
                    <el-button size="medium" :disabled="loading" @click="vueCenter.cancel();">取消</el-button>
                    <el-button type="success" size="medium" :disabled="loading" @click="vueCenter.save('stay');">仅保存</el-button>
                    <el-button type="primary" size="medium" :disabled="loading" @click="vueCenter.save('');">保存并返回</el-button>
                </div>
            </div>
        </div>
    </div>
    <script>
        let vueNorth = new Vue({
            el: '#app-north',
            data: {
                loading: false,
            }
        });
    </script>
</be-north>


<be-page-content>
    <?php
    $formData = [];
    $uiItems = new \Be\AdminPlugin\UiItem\UiItems();
    $rootUrl = \Be\Be::getRequest()->getRootUrl();
    ?>

    <div id="app" v-cloak>
        <el-form ref="formRef" :model="formData" class="be-mb-400">
            <?php
            $formData['id'] = ($this->article ? $this->article->id : '');
            ?>

            <div class="be-row">
                <div class="be-col-24 be-xl-col">
                    <div class="be-p-150 be-bc-fff">

                        <div><span class="be-c-red">*</span> 标题：</div>
                        <el-form-item class="be-mt-50" prop="title" :rules="[{required: true, message: '请输入标题', trigger: 'change' }]">
                            <el-input
                                    type="text"
                                    placeholder="请输入标题"
                                    v-model = "formData.title"
                                    size="medium"
                                    maxlength="120"
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
                            'layout' => $this->configArticle->tinymceLayout,
                            'option' => $this->configArticle->tinymceOption,
                        ]);
                        echo $driver->getHtml();

                        $formData['description'] = ($this->article ? $this->article->description : '');

                        $uiItems->add($driver);
                        ?>

                        <div class="be-row be-mt-100">
                            <div class="be-col-auto">自动下载远程图片：</div>
                            <div class="be-col-auto">
                                <el-form-item prop="download_remote_image">
                                    <el-switch v-model.number="formData.download_remote_image" :active-value="1" :inactive-value="0" size="medium"></el-switch>
                                </el-form-item>
                            </div>
                        </div>
                        <?php $formData['download_remote_image'] = ($this->article && $this->article->download_remote_image !== 0 ? 1 : 0); ?>

                    </div>
                </div>

                <div class="be-col-24 be-xl-col-auto">
                    <div class="be-pl-200 be-pt-200"></div>
                </div>

                <div class="be-col-24 be-xl-col-auto">
                    <div class="be-p-150 be-bc-fff" style="max-width: 400px;">
                        <?php
                        if ($this->article && $this->article->is_enable === -1) {
                            $formData['is_enable'] = -1;
                            $formData['is_push_home'] = 1;
                            $formData['is_on_top'] = 0;
                        } else {
                            ?>
                            <div class="be-row">
                                <div class="be-col">是否发布：</div>
                                <div class="be-col-auto">
                                    <el-form-item prop="is_enable">
                                        <el-switch v-model.number="formData.is_enable" :active-value="1" :inactive-value="0" size="medium"></el-switch>
                                    </el-form-item>
                                </div>
                            </div>
                            <?php $formData['is_enable'] = ($this->article ? $this->article->is_enable : 0); ?>

                            <div class="be-row be-mt-100">
                                <div class="be-col">是否推送到首页：</div>
                                <div class="be-col-auto">
                                    <el-form-item prop="is_push_home">
                                        <el-switch v-model.number="formData.is_push_home" :active-value="1" :inactive-value="0" size="medium"></el-switch>
                                    </el-form-item>
                                </div>
                            </div>
                            <?php $formData['is_push_home'] = ($this->article ? $this->article->is_push_home : 1); ?>

                            <div class="be-row be-mt-100">
                                <div class="be-col">是否置项：</div>
                                <div class="be-col-auto">
                                    <el-form-item prop="is_on_top">
                                        <el-switch v-model.number="formData.is_on_top" :active-value="1" :inactive-value="0" size="medium"></el-switch>
                                    </el-form-item>
                                </div>
                            </div>
                            <?php $formData['is_on_top'] = ($this->article ? $this->article->is_on_top : 0); ?>
                            <?php
                        }
                        ?>

                        <div class="be-mt-100">封面图片：</div>
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


                        <div class="be-mt-100">
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

                        <div class="be-mt-100">
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
                        $uiItems->setVueData('formItems', [
                            'tags' => ['currentTag' => '']
                        ]);
                        ?>

                        <div class="be-mt-100">
                            作者：
                        </div>
                        <div class="be-mt-50">
                            <el-form-item prop="author">
                                <el-input v-model="formData.author" size="medium"></el-input>
                            </el-form-item>
                        </div>
                        <?php
                        $formData['author'] = ($this->article ? $this->article->author : \Be\Be::getAdminUser()->name);
                        ?>

                        <div class="be-mt-100">
                            发布时间：
                        </div>
                        <div class="be-mt-50">
                            <el-form-item prop="publish_time">
                                <el-date-picker type="datetime" v-model="formData.publish_time" size="medium" value-format="yyyy-MM-dd HH:mm:ss" placeholder="选择发布时间"></el-date-picker>
                            </el-form-item>
                        </div>
                        <?php
                        $formData['publish_time'] = ($this->article ? $this->article->publish_time : date('Y-m-d H:i:s'));
                        ?>

                        <div class="be-row be-mt-100">
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

                    <div class="be-p-150 be-bc-fff be-mt-200"  style="max-width: 400px;">

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

                        <div class="be-mt-100 be-t-break be-c-999 be-fs-80"><?php echo $rootUrl . $this->configArticle->urlPrefix; ?>{{formData.url}}</div>
                        <div class="be-mt-100">{{formData.seo_title}}</div>
                        <div class="be-mt-100 be-t-ellipsis-2">{{formData.seo_description}}</div>

                    </div>
                </div>
            </div>

        </el-form>

        <el-drawer
                :visible.sync="drawerSeo"
                title="搜索引擎优化"
                size="60%"
                :wrapper-closable="false"
                :destroy-on-close="true">

            <div class="be-px-150">

                <div class="be-row">
                    <div class="be-col-auto">
                        SEO标题：
                    </div>
                    <div class="be-col">
                        <div class="be-pl-100">
                            <el-switch v-model.number="formData.seo_title_custom" :active-value="1" :inactive-value="0" inactive-text="自动生成" active-text="自定义" size="medium" @change="seoUpdate"></el-switch>
                        </div>
                    </div>
                </div>
                <el-input
                        class="be-mt-50"
                        type="text"
                        placeholder="请输入SEO标题"
                        v-model = "formData.seo_title"
                        size="medium"
                        maxlength="120"
                        show-word-limit
                        :disabled="formData.seo_title_custom === 0">
                </el-input>
                <?php
                $formData['seo_title'] = ($this->article ? $this->article->seo_title : '');
                $formData['seo_title_custom'] = ($this->article ? $this->article->seo_title_custom : 0);
                ?>

                <div class="be-row be-mt-150">
                    <div class="be-col-auto">
                        SEO描述：
                    </div>
                    <div class="be-col">
                        <div class="be-pl-100">
                            <el-switch v-model.number="formData.seo_description_custom" :active-value="1" :inactive-value="0" inactive-text="自动生成" active-text="自定义" size="medium" @change="seoUpdate"></el-switch>
                        </div>
                    </div>
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
                        :disabled="formData.seo_description_custom === 0">
                </el-input>
                <?php
                $formData['seo_description'] = ($this->article ? $this->article->seo_description : '');
                $formData['seo_description_custom'] = ($this->article ? $this->article->seo_description_custom : 0);
                ?>

                <div class="be-row be-mt-150">
                    <div class="be-col-auto">
                        SEO友好链接：
                    </div>
                    <div class="be-col">
                        <div class="be-pl-100">
                            <el-switch v-model.number="formData.url_custom" :active-value="1" :inactive-value="0" inactive-text="自动生成" active-text="自定义" size="medium" @change="seoUpdate"></el-switch>
                        </div>
                    </div>
                </div>

                <el-input
                        class="be-mt-50"
                        type="text"
                        placeholder="请输入SEO友好链接"
                        v-model = "formData.url"
                        size="medium"
                        maxlength="150"
                        show-word-limit
                        :disabled="formData.url_custom === 0">
                    <template slot="prepend"><?php echo $rootUrl . $this->configArticle->urlPrefix; ?></template>
                </el-input>
                <?php
                $formData['url'] = ($this->article ? $this->article->url : '');
                $formData['url_custom'] = ($this->article ? $this->article->url_custom : 0);
                ?>

                <div class="be-mt-150">
                    SEO关键词：
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
    echo $uiItems->getJs();
    echo $uiItems->getCss();
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
                echo $uiItems->getVueData();
                ?>
            },
            methods: {
                save: function (command) {
                    let _this = this;
                    this.$refs["formRef"].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            vueNorth.loading = true;
                            _this.$http.post("<?php echo $this->formActionUrl; ?>", {
                                formData: _this.formData
                            }).then(function (response) {
                                _this.loading = false;
                                vueNorth.loading = false;
                                //console.log(response);
                                if (response.status === 200) {
                                    var responseData = response.data;
                                    if (responseData.success) {
                                        _this.$message.success(responseData.message);

                                        if (command === 'stay') {
                                            _this.formData.id = responseData.article.id;
                                        } else {
                                            setTimeout(function () {
                                                window.onbeforeunload = null;
                                                window.location.href = responseData.redirectUrl;
                                            }, 1000);
                                        }
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
                    window.location.href = "<?php echo $this->backUrl; ?>";
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
                    if (this.formData.seo_title_custom === 0) {
                        this.formData.seo_title = this.formData.title;
                    }

                    if (this.formData.seo_description_custom === 0) {
                        let seoDescription;
                        if (this.formData.summary) {
                            seoDescription = this.formData.summary;
                        } else {
                            seoDescription = this.formData.description;
                            seoDescription = seoDescription.replaceAll(/<[^>]*>/g,"");
                            seoDescription = seoDescription.replaceAll("\r", " ");
                            seoDescription = seoDescription.replaceAll("\n", " ");
                        }
                        if (seoDescription.length > 500) {
                            seoDescription = seoDescription.substr(0, 500);
                        }
                        this.formData.seo_description = seoDescription;
                    }

                    if (this.formData.url_custom === 0) {
                        let title = this.formData.title.toLowerCase();
                        let url = Pinyin.convert(title, "-");
                        if (url.length > 150) {
                            url = Pinyin.convert(title, "-", true);
                            if (url.length > 150) {
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
                }
                <?php
                echo $uiItems->getVueMethods();
                ?>
            }
            <?php
            $uiItems->setVueHook('mounted', 'window.onbeforeunload = function(e) {e = e || window.event; if (e) { e.returnValue = ""; } return ""; };');
            echo $uiItems->getVueHooks();
            ?>
        });

        function imageSelected(files) {
            vueCenter.imageSelected(files);
        }

    </script>

</be-page-content>