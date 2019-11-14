<!--
 * @Description: 进行标记的页面，选择标记的类型与填写标记的原因
 * @Author: sunshuixian
 * @Date: 2019-08-29 11:47:02
 * @LastEditTime: 2019-09-16 10:52:21
 -->
<template lang="html">
    <el-dialog title="标记" :visible.sync="dialogFormVisible" width="40%" >
        <el-form  :rules="rules" ref="termToSign"  :label-position="labelPosition" label-width="100px" :model="form" class="demo-ruleForm">
            <el-form-item label="标记类型:" style="width:100%" prop="type">
                <el-select v-model="form.type" style="width:40%;">
                    <el-option v-for="i in signTypes" :key="i.value" :label="i.label" :value="i.value"></el-option></el-select>
            </el-form-item>
            <el-form-item label="标记原因:" prop="reason">
                <el-input type="textarea" :rows="4" placeholder="请输入标记原因" v-model="form.reason"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer">
            <el-button @click="dialogFormVisible = false">取消</el-button>
            <el-button type="primary" :loading="submitLoading" @click="submit">确定</el-button>
        </div>
    </el-dialog>
</template>
<script>
export default {
    data() {
        return {
            dialogFormVisible: false,
            loading: false,
            labelPosition: 'right',
            form: {
                word: '',
                missionId: null,
                termId: null,
                type: '无法确定',
                reason: ''
            },
            signTypes: [{
                    lable: '无法确定',
                    value: '无法确定'
                },
                {
                    lable: '无意义语料',
                    value: '无意义语料'
                },
                {
                    able: '无法解决',
                    value: '无法解决'
                },
                {
                    lable: '其他',
                    value: '其他'
                }
            ],
            submitLoading: false,
            rules: {
                type: [{
                    required: true,
                    message: '请选择标记类型',
                    trigger: 'change'
                }],
            },
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
    methods: {
        show(missionId, termId, termWord) {
            this.dialogFormVisible = true
            this.$nextTick(() => {
                this.resetForm()
                this.form.missionId = missionId
                this.form.termId = termId
                this.form.word = termWord
            })
        },
        submit() {
            this.$refs.termToSign.validate((valid) => {
                if (valid) {
                    let role = this.user.role
                    let history = {
                        userId: this.user.id,
                        userName: this.user.loginName,
                        optType: 'mark',
                        optData: {
                            'type': this.form.type,
                            'reason': this.form.reason,
                        }
                    }
                    history = JSON.stringify(history)
                    let q = {
                        userId: this.user.id,
                        missionId: this.form.missionId,
                        termId: this.form.termId,
                        word: this.form.word,
                        history,
                        role,
                    }

                    this.submitLoading = true
                    this.$utils.post(
                        this,
                        this.$config.dataServer + 'web/mission/markATerm',
                        q,
                        () => {
                            this.$utils.Patch.success(this, '标记成功')
                            this.$emit('success')
                            this.dialogFormVisible = false
                        },
                        null,
                        () => {
                            this.submitLoading = false
                        }
                    )
                }
            })
        },
        resetForm() {
            this.$refs.termToSign.resetFields()
        }
    }
}
</script>
