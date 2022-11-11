<be-head>
    <link rel="stylesheet" href="<?php echo \Be\Be::getProperty('App.Cms')->getWwwUrl(); ?>/admin/css/page/edit.css" type="text/css"/>
</be-head>

<be-body>
    <?php
    $formData = [];
    $uiItems = new \Be\AdminPlugin\UiItem\UiItems();
    ?>
    <div id="app" v-cloak>

        <div style="position: absolute; left:0; right:0 ;top: 0; bottom: 50px; overflow-y: auto;">

            <?php if (count($this->drivers) > 0) { ?>
            <el-form class="be-p-50" size="small" label-position="top" :disabled="loading">
                <?php
                foreach ($this->drivers as $driver) {

                    echo $driver->getHtml();

                    if ($driver instanceof \Be\AdminPlugin\Form\Item\FormItems) {
                        if ($driver->name !== null) {
                            $formData[$driver->name] = $driver->value;
                        }
                    } else {
                        if ($driver->name !== null) {
                            if (is_array($driver->value) || is_object($driver->value)) {
                                $formData[$driver->name] =  json_encode($driver->value, JSON_PRETTY_PRINT);
                            } else {
                                $formData[$driver->name] = $driver->value;
                            }
                        }
                    }

                    $uiItems->add($driver);
                }
                ?>
            </el-form>
            <?php } ?>
        </div>

        <?php if (count($this->drivers) > 0) { ?>
        <div style="position: absolute; left:0; right:0 ;bottom: 0; height: 50px; overflow: hidden;">
            <div class="be-pt-50 be-ta-center">
                <el-button type="primary" :disabled="loading" @click="save" size="small">保存</el-button>
                <el-button type="danger" :disabled="loading" @click="reset" size="small">恢复默认值</el-button>
            </div>
        </div>
        <?php } ?>
    </div>

    <?php
    echo $uiItems->getJs();
    echo $uiItems->getCss();
    ?>
    <script>
        var vueForm = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>,
                loading: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {
                save: function () {
                    this.loading = true;

                    let _this = this;
                    _this.$http.post("<?php echo $this->editUrl; ?>", {
                        formData: _this.formData,
                    }).then(function (response) {
                        _this.loading = false;
                        if (response.status === 200) {
                            if (response.data.success) {
                                parent.reloadPreviewFrame();
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
                        "<?php echo $this->resetUrl; ?>"
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
                <?php
                echo $uiItems->getVueMethods();
                ?>
            }
            <?php
            echo $uiItems->getVueHooks();
            ?>
        });
    </script>

</be-body>
