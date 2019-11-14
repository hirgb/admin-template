<!--
 * @Description: 修改活动计划
 * @Author: 张克飞
 * @Date: 2019-09-16 17:24:33
 * @LastEditTime: 2019-09-16 17:52:00
 -->
<template lang="html">
    <el-dialog id="edit-activity-play" title="修改活动计划" :close-on-click-modal="false" :visible.sync="dialogVisible" width="50%">
    <el-form ref="editActivityPlan" :inline="false" :model="form" :rules="rules" label-width="80px">
        <el-row style="border-bottom: 1px solid #ddd; padding: 10px 0; margin: 10px 0">
            <h3>基本信息</h3>
        </el-row>
        <el-row>
            <el-col :span="8">
                <el-form-item prop="name" label="影视名">
                    <el-input v-model="form.name"></el-input>
                </el-form-item>
            </el-col>
            <el-col :span="8">
                <el-form-item prop="image" label="海报链接">
                    <el-input v-model="form.image"></el-input>
                </el-form-item>
            </el-col>
            <el-col :span="8">
                <el-form-item prop="score" label="评分">
                    <el-input-number v-model="form.score" :precision="1" :step="0.1" :max="10" :min="0" style="width: 100%"></el-input-number>
                </el-form-item>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="8">
                <el-form-item prop="type" label="类型">
                    <el-select
                        v-model="form.type"
                        multiple
                        filterable
                        allow-create
                        default-first-option
                        style="width: 100%">
                        <el-option
                            v-for="item in types"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>

            </el-col>
            <el-col :span="8">
                <el-form-item prop="director" label="导演">
                    <el-select
                        v-model="form.director"
                        multiple
                        filterable
                        allow-create
                        default-first-option
                        style="width: 100%">
                        <el-option
                            v-for="item in directors"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>

            </el-col>
            <el-col :span="8">
                <el-form-item prop="actor" label="主演">
                    <el-select
                        v-model="form.actor"
                        multiple
                        filterable
                        allow-create
                        default-first-option
                        style="width: 100%">
                        <el-option
                            v-for="item in actors"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>

            </el-col>
        </el-row>
        <el-row>
            <el-col :span="8">
                <el-form-item prop="area" label="国家">
                    <el-select
                        v-model="form.area"
                        multiple
                        filterable
                        allow-create
                        default-first-option
                        style="width: 100%">
                        <el-option
                            v-for="item in areas"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>

            </el-col>
            <el-col :span="8">
                <el-form-item prop="lang" label="语言">
                    <el-select
                        v-model="form.lang"
                        multiple
                        filterable
                        allow-create
                        default-first-option
                        style="width: 100%">
                        <el-option
                            v-for="item in langs"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>

            </el-col>
            <el-col :span="8">
                <el-form-item prop="intervene" label="干预项">
                    <el-select
                        v-model="form.intervene"
                        multiple
                        filterable
                        allow-create
                        default-first-option
                        style="width: 100%"
                        placeholder="数字转写，不含符号">
                        <el-option
                            v-for="item in intervenes"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
        </el-row>
        <el-row>
            <el-form-item prop="introduce" label="简介">
                <el-input
                  type="textarea"
                  :rows="4"
                  placeholder="请输入内容"
                  v-model="form.introduce">
                </el-input>
            </el-form-item>
        </el-row>
        <el-row style="border-bottom: 1px solid #ddd; padding: 10px 0; margin: 10px 0">
            <h3>跳转链接</h3>
        </el-row>
        <el-row>
            <el-col :span="16">
                <el-form-item prop="doubanUrl" label="豆瓣Url">
                    <el-input v-model="form.doubanUrl"></el-input>
                </el-form-item>

            </el-col>
        </el-row>
        <el-row>
            <el-col :span="16">
                <el-form-item prop="playUrl" label="播放Url">
                    <el-input v-model="form.playUrl"></el-input>
                </el-form-item>

            </el-col>
            <el-col :span="8">
                <el-form-item prop="playDp" label="播放Dp">
                    <el-input v-model="form.playDp"></el-input>
                </el-form-item>
            </el-col>
        </el-row>
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button @click="dialogVisible = false">取 消</el-button>
      <el-button type="primary" :loading="loading" :disabled="loading" @click="submit">确 定</el-button>
    </span>
  </el-dialog>
</template>

<script>
export default {
    props: ['detail', 'type', 'actName'],
    data() {
        return {
            loading: false,
            dialogVisible: false,
            form: {
                name: '',
                area: [],
                director: [],
                actor: [],
                image: '',
                lang: [],
                score: 0,
                type: [],
                introduce: '',
                doubanUrl: '',
                doubanDp: '',
                playUrl: '',
                playDp: '',
                intervene: [],
                uid: '',
            },
            rules: {
                name: [{required: true,trigger: 'blur'}],
                // image: [{type: 'url',required: true,trigger: 'blur'}],
                // score: [{type: 'number',required: true,trigger: 'blur'}],
                // type: [{required: true,trigger: 'blur'}],
                // director: [{required: true,trigger: 'blur'}],
                // actor: [{required: true,trigger: 'blur'}],
                // doubanUrl: [{type: 'url',required: true,trigger: 'blur'}],
            },
            langs: [{
                value: '英语',
                label: '英语'
            }, {
                value: '国语',
                label: '国语'
            }],
            directors: [{
                value: '张艺谋',
                label: '张艺谋'
            }, {
                value: '冯小刚',
                label: '冯小刚'
            }],
            actors: [{
                value: '苏菲·玛索',
                label: '苏菲·玛索'
            }, {
                value: '安吉丽娜·朱莉',
                label: '安吉丽娜·朱莉'
            }],
            types: [{
                value: '科幻',
                label: '科幻'
            }, {
                value: '言情',
                label: '言情'
            }],
            areas: [{
                value: '中国',
                label: '中国'
            }, {
                value: '美国',
                label: '美国'
            }],
            intervenes: []
        }
    },
    methods: {
        show() {
            this.form.name = this.detail.name
            this.form.area = this.detail.area ? this.detail.area.split(',') : []
            this.form.director = this.detail.director ? this.detail.director.split(',') : []
            this.form.actor = this.detail.actor ? this.detail.actor.split(',') : []
            this.form.image = this.detail.image
            this.form.introduce = this.detail.introduce
            this.form.lang = this.detail.lang ? this.detail.lang.split(',') : []
            this.form.score = this.detail.score
            this.form.doubanUrl = this.detail.doubanUrl
            this.form.doubanDp = this.detail.doubanDp
            this.form.playUrl = this.detail.playUrl
            this.form.playDp = this.detail.playDp
            this.form.intervene = this.detail.intervene
            this.form.type = this.detail.type ? this.detail.type.split(',') : []
            this.form.uid = this.detail.uid
            this.dialogVisible = true
            window.setTimeout(()=>{
                this.$refs.editActivityPlan.clearValidate()
            }, 0)
        },
        submit(){
            this.$refs.editActivityPlan.validate((isSuccess)=>{
                if (isSuccess) {
                    this.loading = true
                    this.$axios.post(
                        this.$config.dataServer+'web/plan/updatePlan',
                        this.$z.axios.urlEncode({
                            ...this.form,
                            cardType: this.type,
                            actName: this.actName
                        })
                    ).then(res => {
                        this.loading = false
                        let data = res.data
                        if (data.code) {
                            this.$utils.Patch.success(this, '修改成功')
                            this.$emit('edit-success')
                            this.dialogVisible = false
                        } else {
                            this.$utils.Patch.error(this, data.errorMsg)
                        }
                    })
                }
            })
        }
    }
}
</script>

<style lang="stylus">
#add-activity-play
    .el-dialog__body
        padding: 0px 20px
</style>
