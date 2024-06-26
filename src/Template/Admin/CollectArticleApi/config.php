<be-page-content>
    <div id="app" v-cloak>
        <div class="be-p-150 be-bc-fff">
            <div class="be-row be-lh-250 be-bb be-pb-50">
                <div class="be-col-auto">接收器开关：</div>
                <div class="be-col-auto be-px-100">
                    <el-switch v-model.number="formData.enable" :active-value="1" :inactive-value="0" size="medium" @change="toggleEnable"></el-switch>
                </div>
            </div>

            <div class="be-row be-lh-250 be-mt-50 be-bb be-pb-50">
                <div class="be-col-auto">接收器密钥：</div>
                <div class="be-col-auto be-px-100">
                    <?php echo $this->config->token; ?>
                </div>
                <div class="be-col-auto">
                    <el-link type="primary" icon="el-icon-refresh" :underline="false" href="<?php echo beAdminUrl('Cms.CollectArticleApi.resetToken'); ?>">重新生成</el-link>
                </div>
            </div>


            <div class="be-row be-lh-250  be-mt-50 be-bb be-pb-50">
                <div class="be-col-auto">接收网址（Form表单）：</div>
                <div class="be-col-auto be-px-100">
                    <el-tag>
                        <?php echo beUrl('Cms.Api.CollectArticle', ['token' => $this->config->token, 'format' => 'form']); ?>
                    </el-tag>
                </div>

                <div class="be-col-auto">
                    <el-link type="primary" icon="el-icon-document-copy" :underline="false" @click="copyUrl('form')">复制</el-link>
                </div>
            </div>

            <div class="be-row be-lh-250  be-mt-50 be-bb be-pb-50">
                <div class="be-col-auto">接收网址（Json数据）：</div>
                <div class="be-col-auto be-px-100">
                    <el-tag>
                        <?php echo beUrl('Cms.Api.CollectArticle', ['token' => $this->config->token, 'format' => 'json']); ?>
                    </el-tag>
                </div>

                <div class="be-col-auto">
                    <el-link type="primary" icon="el-icon-document-copy" :underline="false" @click="copyUrl('json')">复制</el-link>
                </div>
            </div>

            <div class="be-lh-250 be-mt-50">接收数据（Form表单或Json数据）：</div>
            <div class="be-mt-50">

                <el-table
                        :data="tableData"
                        border
                        style="width: 100%">
                    <el-table-column
                            prop="name"
                            label="字段名"
                            width="180">
                    </el-table-column>
                    <el-table-column
                            prop="required"
                            label="是否必传"
                            align="center"
                            width="180">
                        <template slot-scope="scope">
                            <el-link v-if="scope.row.required === 1" type="success" icon="el-icon-success" style="cursor:auto;font-size:24px;"></el-link>
                            <el-link v-else type="info" icon="el-icon-error" style="cursor:auto;font-size:24px;color:#bbb;"></el-link>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="description"
                            label="说明">
                    </el-table-column>
                </el-table>


            </div>

        </div>
    </div>
    <script>
        let vueCenter = new Vue({
            el: '#app',
            data: {
                formData : {
                    enable: <?php echo $this->config->enable; ?>
                },
                tableData: [
                    {
                        "name" : "unique_key",
                        "required" : 0,
                        "description" : "唯一值，有传值时可用于去重，可取采集的网址，标题等，未传值时不校验是否复复采集，200个字符以内"
                    },
                    {
                        "name" : "image",
                        "required" : 0,
                        "description" : "主图网址，200个字符以内"
                    },
                    {
                        "name" : "title",
                        "required" : 1,
                        "description" : "标题，200个字符以内"
                    },
                    {
                        "name" : "summary",
                        "required" : 0,
                        "description" : "摘要，500个字符以内"
                    },
                    {
                        "name" : "description",
                        "required" : 0,
                        "description" : "描述"
                    },
                    {
                        "name" : "categories",
                        "required" : 0,
                        "description" : "分类，多个分类用 \"|\" 分隔开，单个分类名称120个字符以内，分类不存在时将自动创建"
                    },
                    {
                        "name" : "tags",
                        "required" : 0,
                        "description" : "标签，多个标签用 \"|\" 分隔开，单个标签60个字符以内"
                    },
                    {
                        "name" : "author",
                        "required" : 0,
                        "description" : "作者，50个字符以内"
                    },
                    {
                        "name" : "publish_time",
                        "required" : 0,
                        "description" : "发布时间"
                    }
                ]
            },
            methods: {
                toggleEnable() {
                    let _this = this;
                    _this.$http.get("<?php echo beAdminUrl('Cms.CollectArticleApi.toggleEnable'); ?>", {
                        formData: _this.formData
                    }).then(function (response) {
                        if (response.status === 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                _this.$message.success(responseData.message);
                            } else {
                                if (responseData.message) {
                                    _this.$message.error(responseData.message);
                                } else {
                                    _this.$message.error("服务器返回数据异常！");
                                }
                            }
                        }
                    }).catch(function (error) {
                        _this.$message.error(error);
                    });
                },
                copyUrl: function (type) {
                    let _this = this;
                    let input = document.createElement('input');
                    if (type === "form") {
                        input.value = "<?php echo beUrl('Cms.Api.CollectArticle', ['token' => $this->config->token, 'format' => 'form']); ?>";
                    } else {
                        input.value = "<?php echo beUrl('Cms.Api.CollectArticle', ['token' => $this->config->token, 'format' => 'json']); ?>";
                    }
                    document.body.appendChild(input);
                    input.select();
                    try {
                        document.execCommand('Copy');
                        _this.$message.success("接收器网址已复制！");
                    } catch {
                    }
                    document.body.removeChild(input);
                }
            }
        });
    </script>
</be-page-content>