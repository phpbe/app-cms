<be-head>

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
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Cms.Article.articles'); ?>">返回采集的文章列表</el-link>
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
                                        show-word-limit>
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
                                        show-word-limit>
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

                        </div>

                    </div>
                </div>

            </el-form>
        </div>


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
                            _this.$http.post("<?php echo beAdminUrl('Cms.CollectArticle.' . ($this->article ? 'edit' :'create')); ?>", {
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
                                            window.location.href = "<?php echo beAdminUrl('Cms.CollectArticle.articles'); ?>";
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
                    window.location.href = "<?php echo beAdminUrl('Cms.CollectArticle.articles'); ?>";
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