<be-head>
    <link rel="stylesheet" href="<?php echo \Be\Be::getProperty('App.System')->getWwwUrl(); ?>/admin/theme-editor/css/edit-position.css" type="text/css"/>
</be-head>

<be-body>
    <?php
    $formData = [];
    ?>
    <div id="app" v-cloak>

        <div style="position: absolute; left:0; right:0 ;top: 0; bottom: 50px; overflow-y: auto;">

            <el-form class="be-p-50" size="small" label-position="top" :disabled="loading">
                <el-form-item label="<?php echo $this->positionDescription; ?>">
                    <el-radio-group v-model="formData.enable">
                        <div><el-radio :label="-1">继承公共页面</el-radio></div>
                        <div class="be-mt-100"><el-radio :label="0">关闭</el-radio></div>
                        <div class="be-mt-100"><el-radio :label="1">启用</el-radio></div>
                    </el-radio-group>
                </el-form-item>
                <?php
                $position = $this->position;
                if ($this->page->config->$position < 0) {
                    $formData['enable'] = -1;
                } elseif ($this->page->config->$position === 0) {
                    $formData['enable'] = 0;
                } else {
                    $formData['enable'] = 1;
                }
                ?>

                <?php
                if (in_array($this->position, ['west', 'center', 'east'])) {
                    ?>
                    <el-form-item label="指定宽度" v-if="formData.enable != 0">
                        <el-input-number v-model="formData.width" :min="1" :max="100" :step="1" label="描述文字" <?php echo $this->page->config->$position < 0 ? ':disabled="true"':''; ?>></el-input-number>
                    </el-form-item>
                    <div class="be-mt-50 be-c-999" v-if="formData.enable != 0">左，中，右将按照宽度比例分配</div>
                    <?php
                    $formData['width'] = abs($this->page->config->$position);
                }
                ?>

            </el-form>
        </div>

        <div style="position: absolute; left:0; right:0 ;bottom: 0; height: 50px; overflow: hidden;">
            <div class="be-pt-50 be-ta-center">
                <el-button type="primary" :disabled="loading" @click="save" size="small">保存</el-button>
                <el-button type="danger" :disabled="loading" @click="reset" size="small">恢复默认值</el-button>
            </div>
        </div>
    </div>

    <script>
        var vueForm = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>,
                loading: false
            },
            methods: {
                save: function () {
                    this.loading = true;

                    let _this = this;
                    _this.$http.post("<?php echo beAdminUrl('Cms.Page.editPosition', ['pageId' => $this->pageId, 'position' => $this->position]); ?>", {
                        formData: _this.formData,
                    }).then(function (response) {
                        _this.loading = false;
                        if (response.status === 200) {
                            if (response.data.success) {
                                parent.reload();
                            } else {
                                alert(response.data.message);
                            }
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        alert(error);
                    });
                },
                reset: function () {
                    this.loading = true;

                    let _this = this;
                    _this.$http.get(
                        "<?php echo beAdminUrl('Cms.Page.resetPosition', ['pageId' => $this->pageId, 'position' => $this->position]); ?>"
                    ).then(function (response) {
                        _this.loading = false;
                        if (response.status === 200) {
                            if (response.data.success) {
                                parent.reloadPreviewFrame();
                                window.location.reload();
                            } else {
                                alert(response.data.message);
                            }
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        alert(error);
                    });
                }
            }
        });
    </script>

</be-body>
