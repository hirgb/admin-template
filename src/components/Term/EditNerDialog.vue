<!--
 * @Description: 编辑实体词的类型（ner）
 * @Author: zhangkefei
 * @Date: 2019-08-09 15:48:20
 * @LastEditTime: 2019-09-16 10:43:53
 -->
<template lang="xtmin">
el-dialog id="editNer" title="标注" :visible.sync=dialogFormVisible width=40%
    el-tabs type="card" width=100% height=100% v-model="activeName"
        el-tab-pane label="编辑NER" name="first" "编辑NER"
            el-form ref=editNerForm :model=form :close-on-click-modal=false style="width:90%;text-align:left;"
                el-form-item label="请求实体:" :label-width='100px'
                    el-input :disabled=true :value=form.word
                el-form-item label="所属类别:" :label-width='100px'
                    el-select @change="onNersChange" v-model=form.ners :multiple=true :filterable=true :allow-create=true :default-first-option=false style="width:100%"
                        el-option v-for="i in ners" :key=i :label=i :value=i
                el-form-item label="主意项:"  :label-width='100px'
                    el-radio-group v-model="form.mainEntry" style="border:1px solid #ddd; width:100%; height:100px; padding:10px;"
                        el-radio v-for="i in mainEntries" :key=i :label=i
                el-form-item label="去歧模型:"  :label-width='100px'
                    el-checkbox v-model="form.distribution" "分发"
        el-tab-pane label="pattern标注" name="second" "pattern标注"
    div slot=footer
        el-button :disabled=loading @click="dialogFormVisible = false" "取消"
        el-button type=primary :disabled=loading :loading=loading @click=submit "保存"
</template>

<script>
export default {
    data() {
        return {
            dialogFormVisible: false,
            activeName: 'first',
            loading: false,
            form: {
                word: '',
                ners: [],
                missionId: null,
                termId: null,
                mainEntry: "",
                distribution: false,
            },
            formStr: '',
            ners: [], //存储所有的ner类别
            originNers: [], //编辑之前的ner类别，作为对比
            mainEntries: [],
        }
    },
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        },
        app: function() {
            return this.$store.getters['app/getAll']
        }
    },
    mounted() {
        this.genNers()
    },
    methods: {
        onNersChange(e) {
            this.mainEntries = e
        },
        // 获取所有的ner类别
        async genNers() {
            if (!this.ners.length) {
                this.ners = await this.$utils.getAllNers.call(this)
            }
        },
        show(missionId, termId, termWord, fromTermDo = false, extra = null) {
            this.form.missionId = missionId
            this.form.termId = termId
            this.form.word = termWord
            this.getDetailOfTerm(termWord, fromTermDo)
            this.form.distribution = false
            this.form.mainEntry = ''
            this.form.extra = extra
            this.mainEntries = []
            this.dialogFormVisible = true
        },
        //获取term的详细信息
        getDetailOfTerm(word, fromTermDo = false) {
            let url = fromTermDo ? 'web/mission/getDetailOfTermFromTermdo' : 'web/mission/getDetailOfTerm'
            this.$utils.post(
                this,
                this.$config.dataServer + url, {
                    word,
                    missionId: this.form.missionId
                },
                (data) => {
                    this.form.ners = data.ners.map(i => i.ner + (i.is_amb ? '(amb)' : ''))
                    this.form.distribution = data.isDedup

                    let mainEntry = data.ners.find(i => i.main_entry === 1)
                    if (mainEntry) {
                        this.form.mainEntry = mainEntry.ner
                    }

                    this.originNers = this.form.ners
                    this.mainEntries = this.form.ners
                    this.formStr = JSON.stringify(this.form)
                }
            )
        },
        submit() {
            if (this.formStr === JSON.stringify(this.form)) {
                this.dialogFormVisible = false
                this.$message({
                    message: '没有发生修改',
                    type: 'warning'
                });
            } else {
                this.save()
            }
        },
        save() {
            let opt = this.compareNers()
            let role = this.user.role
            let mainEntry = this.form.ners.includes(this.form.mainEntry) ? this.form.mainEntry : ''
            let history = {
                userId: this.user.id,
                userName: this.user.loginName,
                optType: 'editNer',
                optData: opt
            }
            history = JSON.stringify(history)
            let extra = ''
            try {
                extra = (this.form.extra && JSON.stringify(this.form.extra))
            } catch (e) {
                throw e
            }

            let q = {
                userId: this.user.id,
                word: this.form.word,
                missionId: this.form.missionId,
                termId: this.form.termId,
                ner: this.form.ners.join('|'),
                distribution: this.form.distribution,
                mainEntry,
                role,
                history,
                extra,
            }

            this.loading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/editNer',
                q,
                () => {
                    this.$utils.Patch.success(this, '修改成功')
                    this.$emit('update-success')
                    this.dialogFormVisible = false
                },
                null,
                () => {
                    this.loading = false
                }
            )
        },
        compareNers() {
            let originNers = this.originNers
            let finalNers = this.form.ners
            let deleteNers = originNers.filter(i => !finalNers.includes(i))
            let addNers = finalNers.filter(i => !originNers.includes(i))
            return {
                deleteNers,
                addNers
            }
        }
    }
}
</script>

<style lang="css">
#editNer .el-dialog__header{
    display: none;
}
#editNer .el-dialog__body{
    padding: 0;
}

</style>
