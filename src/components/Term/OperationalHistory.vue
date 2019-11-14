<!--
 * @Description: 操作详情组件，点击页面中的“详情”按钮，即可展示每个操作对应的操作人，操作时间和操作类型。
 * @Author: sunshuixian
 * @Date: 2019-09-02 11:27:22
 * @LastEditTime: 2019-09-16 14:46:32
 -->
<template lang="html">
    <el-drawer id="history-drawer" title="NER 历史详情" :visible.sync="drawer" size="30%">
        <el-timeline :reverse="true" class="el-timeline-item__wrapper">
            <el-timeline-item v-for="i in historyList" :timestamp="i.time" :key="i.time" placement="top">
                <el-card>
                    <p v-html="i.content"></p>
                </el-card>
            </el-timeline-item>
        </el-timeline>
    </el-drawer>
</template>

<script>
import {
    optType
} from '@/assets/js/global.js'

export default {
    data() {
        return {
            drawer: false,
            historyList: [],
        }
    },
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        },
        role: function() {
            return this.user.role
        },
        app: function() {
            return this.$store.getters['app/getAll']
        },
    },
    methods: {
        show(row) {
            this.resetHistory()
            try {
                if (row.history) {
                    this.getTimeLine(JSON.parse(row.history))
                    this.drawer = true
                } else {
                    this.$utils.Patch.error(this, '目前没有历史记录')
                }
            } catch (e) {
                this.$utils.Patch.error(this, '历史记录字段内容不规范')
                throw e
            }
        },
        getTimeLine(history) {
            let historyList = []
            history.forEach(i => {
                i.content = this.genContent(i)
                historyList.push(i)
            })
            this.historyList = historyList
        },
        resetHistory() {
            this.historyList = []
        },
        genContent(h) {
            let html = ''
            html += `修改人：${h.userName}<br>`
            html += `${optType[h.optType] ? optType[h.optType] : h.optType}`
            switch (h.optType) {
                case 'editNer':
                    html += `<br>增加ner：${h.optData.addNers.join(',')}`
                    html += `<br>删除ner：${h.optData.deleteNers.join(',')}`
                    break;
                case 'editWord':
                    html += `<br>修改前：${h.optData.before}`
                    html += `<br>修改后：${h.optData.after}`
                    break
                case 'mark':
                    html += `<br>标记类型：${h.optData.type}`
                    html += `<br>标记原因：${h.optData.reason}`
                    break
                case 'reject':
                    html += `<br>驳回类型：${h.optData.type}`
                    html += `<br>驳回原因：${h.optData.reason}`
                    break
                case 'add':
                    html += `<br>来源：${h.optData.source}`
                    break
                default:
                    break
            }
            return html
        }
    }
}
</script>

<style lang="stylus">
#history-drawer
    .el-timeline-item__wrapper
        text-align: left
        padding-top: 15px
        box-sizing: border-box
    .el-drawer__body
        overflow-y: auto
    .el-timeline-item__content
        width: 95%
</style>
