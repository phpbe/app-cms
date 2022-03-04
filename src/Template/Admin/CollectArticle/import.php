<be-head>
    <style>
        .collect-article-header {
            color: #666;
            background-color: #EBEEF5;
            height: 3rem;
            line-height: 3rem;
            margin-bottom: .5rem;
        }

        .collect-article-op {
            color: #666;
            background-color: #EBEEF5;
            height: 3rem;
            line-height: 3rem;
            margin-bottom: .5rem;
        }

        .collect-article {
            line-height: 2.5rem;
            border-bottom: #EBEEF5 1px solid;
            padding-top: .5rem;
            padding-bottom: .5rem;
            margin-bottom: 2px;
        }
    </style>
</be-head>

<be-center>
    <?php
    $rootUrl = \Be\Be::getRequest()->getRootUrl();
    $formData = ['collectArticles' => $this->collectArticles];
    ?>

    <div id="app" v-cloak>
        <div style="position: absolute; left: 0; right: 0; top: 0; bottom: 6rem; overflow-y: auto;">
            <div class="be-row collect-article-header">
                <div class="be-col-8 be-fw-bold be-pl-100">
                    采集的文章
                </div>
                <div class="be-col-8 be-fw-bold">
                    导入到文章
                </div>
                <div class="be-col-8 be-fw-bold be-ta-center">
                    设置分类
                </div>
            </div>

            <div class="be-row collect-article-op">
                <div class="be-col-8 be-pl-100">
                    批量设置
                </div>
                <div class="be-col-8">
                </div>
                <div class="be-col-8 be-fw-bold be-ta-center">
                    <el-select
                            v-model="categoryIds"
                            multiple
                            placeholder="请选择分类"
                            size="medium"
                            style="min-width: 90%"
                            @change="setCategoryIds">
                        <?php
                        foreach ($this->categoryKeyValues as $key => $val) {
                            echo '<el-option value="'. $key .'" key="'. $key .'" label="' .$val . '"></el-option>';
                        }
                        ?>
                    </el-select>
                </div>
            </div>


            <div class="be-row collect-article" v-for="collectArticle, collectArticleIndex in formData.collectArticles" :key="collectArticle.id">
                <div class="be-col-8 be-pl-100">
                    {{collectArticle.title}}
                </div>
                <div class="be-col-8">
                    {{collectArticle.article_title}}
                </div>
                <div class="be-col-8 be-ta-center">
                    <el-select
                            v-model="collectArticle.category_ids"
                            multiple
                            placeholder="请选择分类"
                            size="medium" style="min-width: 90%">
                        <?php
                        foreach ($this->categoryKeyValues as $key => $val) {
                            echo '<el-option value="'. $key .'" key="'. $key .'" label="' .$val . '"></el-option>';
                        }
                        ?>
                    </el-select>
                </div>
            </div>
        </div>

        <div class="be-row be-bt" style="position: absolute; left: 0; right: 0; bottom: 0; height: 5rem; line-height: 4rem;">
            <div class="be-col"></div>
            <div class="be-col-auto">
                <el-button type="primary" icon="el-icon-check" @click="importConfirm" :disable="loading">确认导入&同步</el-button>
            </div>
        </div>

    </div>

    <script>
        let vueCenter = new Vue({
            el: '#app',
            data: {
                categoryIds: [],
                formData: <?php echo json_encode($formData); ?>,
                loading: false,
                t: false
            },
            methods: {
                importConfirm: function () {
                    let _this = this;
                    _this.loading = true;
                    _this.$http.post("<?php echo beAdminUrl('Cms.CollectArticle.importSave'); ?>", {
                        formData: _this.formData
                    }).then(function (response) {
                        _this.loading = false;
                        //console.log(response);
                        if (response.status === 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                _this.$message.success(responseData.message);
                                setTimeout(function () {
                                    parent.closeAndReload();
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
                        _this.$message.error(error);
                    });
                },
                setCategoryIds(val) {
                    for (let article of this.formData.collectArticles) {
                        article.category_ids = val;
                    }
                }
            }
        });
    </script>

</be-center>