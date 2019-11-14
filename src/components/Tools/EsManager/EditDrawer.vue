<template lang="xtmin">
div .height100 style="padding: 10px"
    el-tabs v-model=activeTab :before-leave=leaveTab
        el-tab-pane label=完整 name=all
            ace-editor v-if=forceUpdate v-model=all @init="$utils.Tools.jsonEditorInit" lang="json" theme="chrome" width="100%" :height=editorHeight
        el-tab-pane label=details name=details v-if="esData._source.details"
            ace-editor v-if=forceUpdate v-model=details @init="$utils.Tools.jsonEditorInit" lang="json" theme="chrome" width="100%" :height=editorHeight
        el-tab-pane label=keyword name=keyword v-if="esData._source.keyword"
            ace-editor v-if=forceUpdate v-model=keyword @init="$utils.Tools.jsonEditorInit" lang="json" theme="chrome" width="100%" :height=editorHeight
        el-tab-pane label=normalized name=normalized v-if="esData._source.normalized"
            ace-editor v-if=forceUpdate v-model=normalized @init="$utils.Tools.jsonEditorInit" lang="json" theme="chrome" width="100%" :height=editorHeight
    el-row .center
        el-button type=default @click=cancel "取消"
        el-button type=primary @click=updateDataById "保存"
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
        return {
            activeTab: 'all',
            esData: {
                _source: {
                    details: {},
                    keyword: {},
                    normalized: {},
                }
            },
            realViewHeight: new this.$utils.RealViewHeight(),
            forceUpdate: true,
            all: '',
            details: '',
            keyword: '',
            normalized: '',
        }
    },
    computed: {
        ...mapGetters({
            app: 'app/getAll',
            user: 'user/getAll',
        }),
        editorHeight() {
            return this.realViewHeight.height - 150
        },
    },
    beforeDestroy() {
        this.realViewHeight.unregist()
    },
    methods: {
        setData(tab, row){
            let [host, index, type] = tab.url.split('/')
            this.activeTab = 'all'
            this.esHost = host
            this.esIndex = index
            this.esType = type
            this.esData = JSON.parse(JSON.stringify(row))

            this.all = JSON.stringify(this.esData._source, null, 4)
            this.details = this.esData._source.details
                ? JSON.stringify(this.esData._source.details, null, 4)
                : ''
            this.keyword = this.esData._source.keyword
                ? JSON.stringify(this.esData._source.keyword, null, 4)
                : ''
            this.normalized = this.esData._source.normalized
                ? JSON.stringify(this.esData._source.normalized, null, 4)
                : ''

            this.row = row
        },
        cancel() {
            this.$emit('cancel')
        },
        updateDataById() {
            try {
                const data = JSON.parse(this.all)
                if (this.activeTab !== 'all') {
                    data[this.activeTab] = JSON.parse(this[this.activeTab])
                }
                let q = {
                    host: this.esHost,
                    index: this.esIndex,
                    type: this.esType,
                    userId: this.user.id,
                    docId: this.esData._id,
                    data: JSON.stringify(data),
                }
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/esmanager/updateDataById',
                    q,
                    () => {
                        this.$utils.Patch.success(this, '修改成功')
                        this.row._source = this.esData._source
                        this.$emit('success')
                    }
                )
            } catch (e) {
                this.$utils.Patch.error(this, 'JSON数据有误，请改正后再提交')
                throw e
            }
        },
        leaveTab(activeName, oldActiveName) {
            try {
                const all = JSON.parse(this.all)
                if (oldActiveName === 'all') {
                    this[activeName] = JSON.stringify(all[activeName], null, 4)
                } else {
                    const obj = JSON.parse(this[oldActiveName])
                    all[oldActiveName] = obj
                    this.all = JSON.stringify(all, null, 4)
                }
                this.$utils.Patch.forceUpdate.call(this)
                return true
            } catch (e) {
                this.$utils.Patch.error(this, '当前标签页数据有误')
                return false
            }
        }
    }
}
</script>
