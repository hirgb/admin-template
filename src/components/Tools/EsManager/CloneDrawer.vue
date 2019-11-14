<template lang="xtmin">
div .height100 style="padding: 10px"
    el-row :gutter=10
        el-col :span=12
            el-form label-width="80px"
                el-form-item label="ES 连接"
                    el-select
                    v-model.trim=esConnect
                    :filterable=true
                    :allow-create=true
                    :default-first-option=true
                        el-option
                        v-for="i in connects"
                        :key=i
                        :label=i
                        :value=i
                el-form-item label="ES type"
                    el-select
                    v-model.trim=esType
                    :filterable=true
                    :allow-create=true
                    :default-first-option=true
                        el-option
                        v-for="i in esTypes"
                        :key=i
                        :label=i
                        :value=i
        el-col :span=12
            el-form :model=form2 label-width="80px" :rules=rules
                el-form-item label="ES index"
                    el-select
                    v-model.trim=form2.esIndex
                    :filterable=true
                    :allow-create=true
                    :default-first-option=true
                        el-option
                        v-for="i in esIndexs"
                        :key=i
                        :label=i
                        :value=i
                el-form-item prop=docId label="DocId"
                    el-input v-model.trim=form2.docId placeholder="如不填，系统可自动生成"
    el-row
        ace-editor v-model=content @init="$utils.Tools.jsonEditorInit" lang="json" theme="chrome" width="100%" :height="realViewHeight.height - 190"
    el-row .center
        el-button type=default @click=cancel "取消"
        el-button type=primary @click=addData "创建"
</template>

<script>
import AceEditor from 'vue2-ace-editor'
import {
    mapGetters
} from 'vuex'

export default {
    components: {
        AceEditor,
    },
    data() {
        let validateDocId = (rule, value, callback) => {
            if (value) {
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/esmanager/isDocIdExist', {
                        docId: value,
                        host: this.esConnect,
                        index: this.form2.esIndex,
                        type: this.esType,
                    },
                    data => {
                        if (data.exist) {
                            callback(new Error('docId已存在,如果存储将覆盖'))
                        } else {
                            callback()
                        }
                    },
                    (msg) => {
                        callback(new Error(msg))
                    }
                )
            } else {
                callback()
            }
        }
        return {
            esConnect: '',
            connects: [],
            esType: '',
            esTypes: [],
            esIndexs: [],
            form2: {
                esIndex: '',
                docId: '',
            },
            content: '',
            realViewHeight: new this.$utils.RealViewHeight(),
            rules: {
                docId: [{
                    validator: validateDocId,
                    trigger: 'blur'
                }],
            }
        }
    },
    computed: {
        ...mapGetters({
            app: 'app/getAll',
            user: 'user/getAll',
        })
    },
    beforeDestroy() {
        this.realViewHeight.unregist()
    },
    methods: {
        setData(tab, esData) {
            let [host, index, type] = tab.url.split('/')
            this.esConnect = host
            this.form2.esIndex = index
            this.esType = type
            this.form2.docId = ''
            this.content = JSON.stringify(esData._source, null, 4)
        },
        addData() {
            let content
            try {
                content = JSON.parse(this.content)
            } catch (e) {
                this.$utils.Patch.error(this, e.message)
                return
            }

            let q = {
                host: this.esConnect,
                index: this.form2.esIndex,
                type: this.esType,
                docId: this.form2.docId,
                data: JSON.stringify(content),
                userId: this.user.id,
            }
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/esmanager/addData',
                q,
                () => {
                    this.$utils.Patch.success(this, '添加成功')
                    this.$emit('success')
                }
            )
        },
        cancel() {
            this.$emit('cancel')
        },
    }
}
</script>

<style lang="css" scoped>
</style>
