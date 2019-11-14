<!--
 * @Descripttion:动态添加自定义实体，即用户通过手动输入实体词，将实体添加到Mission任务表中
 * @Author: 孙水仙
 * @Date: 2019-07-23 10:30:40
 * @LastEditTime: 2019-09-16 15:19:48
 -->
<template lang="xtmin">
el-dialog title=添加自定义实体 :visible.sync=dialogFormVisible :close-on-click-modal=false width=50%
    el-form :model="newTermForm" ref="addNewTermForm" label-width="100px" class="demo-dynamic"
        el-form-item
        v-for="(term, index) in newTermForm.terms"
        :label="'实体' + index"
        :key="term.key"
        :prop="'terms.' + index + '.value'"
        :rules="{required: true, message: '实体名不能为空', trigger: 'blur'}"
            el-input placeholder="请输入请求实体名称" style="width:280px" v-model="term.value"
            el-select placeholder="请选择请求实体类型" style="width:180px" v-model=term.ner :filterable=true :default-first-option=true
                el-option v-for="i in ners" :key=i :label=i :value=i
            el-button @click.prevent="removeTerm(term)" "删除"
        el-form-item
            el-button @click="addTerm" "增加一条实体"
            el-button type="primary" @click="submitForm()" "提交"

</template>

<script>
import TermAll from './TermAll'

export default {
    components: {
        TermAll,
    },
    data() {
        return {
            missionId: 0,
            dialogFormVisible: false,
            loading: false,
            ners: [],
            responseTerm: {},
            newTermForm: {
                terms: [{
                    value: '',
                    ner: '',
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
        },
    },
    mounted() {
        this.genNers()
    },
    methods: {
        /**
         * @name: 提交表单
         * @test: test font
         * @msg:点击表单提交按钮，用户手动输入的term进行接口请求，请求后返回的数据再请求
         到后端mission任务表中，将正确的term添加到mission表中。
         * @param {type}
         * @return:
         */
        submitForm() {
            this.$refs.addNewTermForm.validate((valid) => {
                if (valid) {
                    let terms = this.newTermForm.terms
                    let newTerms = []
                    let l = terms.length
                    for (let i = 0; i < l; i++) {
                        let p = [terms[i].value, terms[i].ner]
                        newTerms.push(p)
                    }

                    let nerIsNull = newTerms.some(i => !i[1])
                    if (nerIsNull) {
                        this.$utils.Patch.error(this, 'ner不能为空')
                        return
                    }
                    this.dataLoading = true
                    this.$axios.post(
                        'http://10.0.4.117:1314/query_normalize', {
                            value: newTerms
                        }, {
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8;'
                            }
                        }
                    ).then(responseTerm => {
                        if (responseTerm.data.status_code) {
                            this.postDataFromMission(responseTerm)
                        } else {
                            this.$utils.Patch.error(this, '获取接口数据失败')
                        }
                    })
                } else {
                    this.$utils.Patch.error(this, 'error submit!')
                }
            });
        },
        postDataFromMission(responseTerm) {
            if (responseTerm &&
                responseTerm.data &&
                responseTerm.data.value &&
                responseTerm.data.value.length
            ) {
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/missionmanage/addCustomTerm', {
                        missionId: this.missionId,
                        newTerms: responseTerm.data.value,
                        userId: this.user.id,
                        userName: this.user.loginName
                    },
                    () => {
                        this.dialogFormVisible = false
                        this.$utils.Patch.success(this, '添加自定义实体成功')
                        this.$emit('add-new-success')
                    },
                    null,
                    () => {
                        this.dataLoading = false
                    }
                )
            } else {
                this.$utils.Patch.error(this, '禁止输入无意义的实体词')
            }
        },
        removeTerm(item) {
            var index = this.newTermForm.terms.indexOf(item)
            if (index !== 0) {
                this.newTermForm.terms.splice(index, 1)
            }
        },
        addTerm() {
            this.newTermForm.terms.push({
                value: '',
                ner: '',
            });
        },
        async genNers() {
            if (!this.ners.length) {
                this.ners = await this.$utils.getAllNers.call(this)
            }
        },
        show(missionId) {
            this.missionId = missionId
            this.dialogFormVisible = true
            this.$nextTick(() => {
                this.$refs.addNewTermForm.resetFields()
            })
        },
    }
}
</script>
