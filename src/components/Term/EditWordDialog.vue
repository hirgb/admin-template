<template lang="xtmin">
el-dialog title=编辑Word :visible.sync=dialogFormVisible width=30%
    el-form ref=editNerForm :model=form label-width=120px :close-on-click-modal=false
        el-form-item label=请求实体
            el-input v-model=form.word
    div slot=footer
        el-button :disabled=loading @click="dialogFormVisible = false" "取消"
        el-button type=primary :disabled=loading :loading=loading @click=submit "保存"
</template>

<script>
    export default {
        data(){
            return {
                dialogFormVisible: false,
                loading: false,
                form: {
                    oldWord: '',
                    word: '',
                    missionId: null,
                    termId: null,
                },
            }
        },
        computed: {
            user: function(){
                return this.$store.getters['user/getAll']
            },
            app: function(){
                return this.$store.getters['app/getAll']
            }
        },
        methods: {
            show(missionId, termId, termWord){
                this.form.missionId = missionId
                this.form.termId = termId
                this.form.word = termWord
                this.form.oldWord = termWord
                this.dialogFormVisible = true
            },
            submit(){
                const q = {
                    missionId: this.form.missionId,
                    word: this.form.word,
                    oldWord: this.form.oldWord,
                    userId: this.user.id,
                    history: JSON.stringify({
                        userId: this.user.id,
                        userName: this.user.loginName,
                        optType: 'editWord',
                        optData: {
                            before: this.form.oldWord,
                            after: this.form.word,
                        }
                    })
                }
                this.loading = true
                this.$utils.post(
                    this,
                    this.$config.dataServer+'/web/mission/editWord',
                    q,
                    ()=>{
                        this.$utils.Patch.success(this, '修改成功')
                        this.$emit('update-success')
                    },
                    (data)=>{
                        this.$utils.Patch.error(this, data.errorMsg)
                    },
                    ()=>{
                        this.loading = false
                        this.dialogFormVisible = false
                    }
                )
            },
        }
    }
</script>
