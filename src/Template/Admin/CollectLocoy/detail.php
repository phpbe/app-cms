<be-center>
    <div id="app" v-cloak>
        <div class="be-center">
            <div class="be-center-title"><?php echo $this->title; ?></div>
            <div class="be-center-body">
                <div class="be-p-200">
                    <div class="be-row be-bb be-pb-50 be-mb-100">
                        <div class="be-col-auto">接口开关：</div>
                        <div class="be-col-auto be-px-100">
                            <el-switch v-model.number="enable" :active-value="1" :inactive-value="0" size="medium" disabled></el-switch>
                        </div>
                        <div class="be-col-auto">
                            <el-link type="primary" icon="el-icon-setting" :underline="false" href="<?php echo beAdminUrl('Cms.Config.dashboard'); ?>?configName=Locoy">修改</el-link>
                        </div>
                    </div>

                    <div class="be-row be-mt-100 be-bb be-pb-50 be-mb-100">
                        <div class="be-col-auto">接口密码：</div>
                        <div class="be-col-auto be-px-100">
                            <?php echo $this->configLogoy->password; ?>
                        </div>
                        <div class="be-col-auto">
                            <el-link type="primary" icon="el-icon-setting" :underline="false" href="<?php echo beAdminUrl('Cms.Config.dashboard'); ?>?configName=Locoy">修改</el-link>
                        </div>
                    </div>

                    <div class="be-mt-100 be-bb be-pb-50 be-mb-50">接口网址：</div>
                    <div class="be-mt-50">
                        <?php echo beUrl('Cms.Api.locoy', ['password' => $this->configLogoy->password]); ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <script>
        let vueCenter = new Vue({
            el: '#app',
            data: {
                enable: <?php echo $this->configLogoy->enable; ?>
            },
            methods: {

            },
        });
    </script>
</be-center>