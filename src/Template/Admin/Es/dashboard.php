<be-page-content>
    <div class="be-bc-fff be-p-150" id="app" v-cloak>
        <?php
        $formDatas = [];

        if (!$this->configEs->enable) {
            ?>
            <div class="be-fw-bold">
                ES搜索引擎未启用！
                <el-link class="be-ml-100" type="primary" href="<?php echo beAdminUrl('Cms.Config.dashboard', ['configName' => 'Es']); ?>">修改</el-link>
            </div>
            <?php
        } else {
            if (count($this->indexes) > 0) {
                $i = 0;
                foreach ($this->indexes as $index) {
                    ?>
                    <div class="be-bb-eee be-pb-50">
                        <div class="be-row be-fs-110 <?php echo $i > 0 ? 'be-mt-300' : ''; ?>">
                            <div class="be-col-auto">
                                <div class="be-pr-100">
                                    <?php echo $index['label']; ?>：
                                </div>
                            </div>
                            <div class="be-col">
                                <?php echo $index['value']; ?>
                                <el-link class="be-ml-100" type="primary" href="<?php echo beAdminUrl('Cms.Config.dashboard', ['configName' => 'Es']); ?>">修改</el-link>
                            </div>
                        </div>
                    </div>

                    <div class="be-mt-150">
                    <?php
                    if ($index['exists']) {
                        ?>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">UUID：</div>
                            <div class="be-col">
                                <?php echo $index['settings']['index']['uuid'] ?? ''; ?>
                            </div>
                        </div>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">分片数：</div>
                            <div class="be-col">
                                <?php echo $index['settings']['index']['number_of_shards'] ?? ''; ?>
                            </div>
                        </div>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">副本数：</div>
                            <div class="be-col">
                                <?php echo $index['settings']['index']['number_of_replicas'] ?? ''; ?>
                            </div>
                        </div>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">数据量：</div>
                            <div class="be-col">
                                <?php echo $index['count'] ?? ''; ?>
                            </div>
                        </div>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">创建于：</div>
                            <div class="be-col">
                                <?php echo isset($index['settings']['index']['creation_date']) ? date('Y-m-d H:i:s', (int)$index['settings']['index']['creation_date'] / 1000) : ''; ?>
                            </div>
                        </div>

                        <div class="be-bt-eee be-mt-100 be-pt-50">
                            <el-button type="danger" size="medium" @click="deleteIndex('<?php echo $index['name']; ?>')">删除索引</el-button>
                        </div>
                        <?php
                    } else {
                        ?>
                        <el-form ref="<?php echo $index['name']; ?>FormRef" :model="<?php echo $index['name']; ?>FormData">
                            <el-form-item class="be-mt-50" prop="number_of_replicas" label="分片数" :rules="[{required: true, message: '请输入分片数', trigger: 'change' }]">
                                <el-input-number
                                        :min="1"
                                        :step="1"
                                        placeholder="请输入分片数"
                                        v-model = "<?php echo $index['name']; ?>FormData.number_of_shards"
                                        size="medium">
                                </el-input-number>
                            </el-form-item>

                            <el-form-item class="be-mt-50" prop="number_of_replicas" label="副本数" :rules="[{required: true, message: '请输入分片数', trigger: 'change' }]">
                                <el-input-number
                                        :min="1"
                                        :step="1"
                                        placeholder="请输入副本数"
                                        v-model = "<?php echo $index['name']; ?>FormData.number_of_replicas"
                                        size="medium">
                                </el-input-number>
                            </el-form-item>
                        </el-form>

                        <el-button type="primary" size="medium" @click="createIndex('<?php echo $index['name']; ?>FormRef', '<?php echo $index['name']; ?>FormData')">创建索引</el-button>
                        <?php
                        $formDatas[$index['name'] . 'FormData'] = [
                            'name' => $index['name'],
                            'number_of_shards' => 1,
                            'number_of_replicas' => 1,
                        ];
                    }
                    ?>
                    </div>

                    <?php
                    $i++;
                }
            }
        }

        if (count($formDatas) > 0) {
            ?>
            <ul class="be-mt-200">
                <li class="be-c-999">推荐：分片数 * 副本数 = 集群的CPU总核数 * N (N 可取 1,2 不要取太大，太大了效率反而不高)</li>
                <li class="be-c-999">副本数 越多，占用的空间越多，是倍数关系，跟据数量量，空间大小确定。</li>
            </ul>
            <?php

        }
        ?>


    </div>
    <script>
        let vueCenter = new Vue({
            el: '#app',
            data: {
                loading: false,
                <?php
                if (count($formDatas) > 0) {
                    foreach ($formDatas as $key => $formData) {
                        echo $key . ':' . json_encode($formData) . ',';
                    }
                }
                ?>
                t: false
            },
            methods: {
                createIndex: function (formRef, formData) {
                    let _this = this;
                    this.$refs[formRef].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            _this.$http.post("<?php echo beAdminUrl('Cms.Es.createIndex'); ?>", {
                                formData: _this[formData]
                            }).then(function (response) {
                                _this.loading = false;
                                if (response.status === 200) {
                                    var responseData = response.data;
                                    if (responseData.success) {
                                        _this.$message.success(responseData.message);
                                        setTimeout(function () {
                                            window.location.reload();
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
                        } else {
                            return false;
                        }
                    });
                },
                deleteIndex: function (indexname) {

                    let _this = this;
                    _this.$confirm("本操作将删除索引中的所有数据，确认要岫除么？", "删除确认", {
                        confirmButtonText: "确定",
                        cancelButtonText: "取消",
                        type: "warning"
                    }).then(function(){

                        _this.loading = true;
                        _this.$http.post("<?php echo beAdminUrl('Cms.Es.deleteIndex'); ?>", {
                            formData: {
                                name: indexname
                            }
                        }).then(function (response) {
                            _this.loading = false;
                            if (response.status === 200) {
                                var responseData = response.data;
                                if (responseData.success) {
                                    _this.$message.success(responseData.message);
                                    setTimeout(function () {
                                        window.location.reload();
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

                    }).catch(function(){});
                },
                t: function () {
                }
            },
        });
    </script>
</be-page-content>