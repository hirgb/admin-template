<template lang="xtmin">
el-form label-width=80px :model=form :rules=rules ref=rejectForm
    el-form-item prop=type label=驳回类型
        el-radio-group v-model=form.type
            el-radio label=NER缺失
            el-radio label=pattern干预
            el-radio label=非term
    el-form-item prop=reason label=驳回原因
        el-input v-model.trim=form.reason type=textarea
    el-form-item
        el-button "返回" @click="$emit('back')"
        el-button "提交" type=primary :loading=loading @click="submit"
</template>

<script>
export default {
    props: ['missionId', 'word'],
    data() {
        return {
            loading: false,
            form: {
                type: '',
                reason: '',
            },
            rules: {
                type: [{
                    required: true,
                    trigger: 'blur',
                }],
                // reason: [{
                //     required: true,
                //     trigger: 'blur',
                // }]
            },
        }
    },
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        }
    },
    methods: {
        reset() {
            this.$refs.rejectForm.resetFields()
        },
        submit() {
            this.$refs.rejectForm.validate((isSuccess) => {
                if (isSuccess) {
                    if (this.missionId && this.word) {
                        const history = {
                            userId: this.user.id,
                            userName: this.user.loginName,
                            optType: 'reject',
                            optData: {
                                type: this.form.type,
                                reason: this.form.reason,
                            },
                        }
                        const q = {
                            missionId: this.missionId,
                            userId: this.user.id,
                            word: this.word,
                            history: JSON.stringify(history)
                        }
                        this.loading = true
                        this.$emit('reject')
                        this.$utils.post(
                            this,
                            this.$config.dataServer + 'web/mission/testReject',
                            q,
                            () => {
                                this.$utils.Patch.success(this, '提交成功')
                                this.$emit('success')
                            },
                            null,
                            () => {
                                this.loading = false
                            }
                        )
                    } else {
                        this.$utils.Patch.error(this, '未获取到任务Id和term')
                    }
                }
            })
        },
    }
}
</script>
