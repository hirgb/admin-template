<!--
 * @Description: 'marker'标注员可以操作的页面 显示某任务中所有未标注的实体
 * @Author: zhangkefei
 * @Date: 2019-08-09 15:48:20
 * @LastEditTime: 2019-09-16 14:50:19
 -->
<template lang="xtmin">
el-row .wrapper
    edit-ner-dialog ref=editNerDialog @update-success="refreshTerms(page.currentPage, page.size)"
    edit-word-dialog ref=editWordDialog @update-success="refreshTerms(page.currentPage, page.size)"
    mark-term-dialog ref=markTermDialog @success="refreshTerms(page.currentPage, page.size)"
    operational-history ref=operationalHistory
    el-row .opt :gutter=10
        el-col :span=4
            el-button
            type=primary
            :style="{width: '100%'}"
            :loading=dataLoading
            :disabled="scriptIsRunning"
            @click="submit"
            "{{scriptIsRunning ? '脚本运行' : '提交测试'}}"
        el-col :span=5
            el-select v-model=ner placeholder=NER类别 @change=searchByNer
                el-option v-for="i in ners" :key=i.value :label=i.label :value=i.value
        el-col :span=3
        el-col :span=10
            el-input v-model=searchWord @keyup.enter.native=refreshTerms :clearable=true placeholder=搜索...
    el-table
    v-loading=dataLoading
    cell-class-name=table-cell
    header-cell-class-name=table-head-cell
    :data=terms
    :border=true
    :height=tableHeight
    @selection-change="v => termsSelected = v"
        el-table-column prop=word label=请求实体 width=130
        el-table-column prop=ner label=NER类别
            template slot-scope=scope
                el-tag v-for="i in scope.row.ners" :type="i.deleted ? 'info' : ''" v-text=i.ner :key=i.ner
        el-table-column prop=stat label=标注状态 width=70
        el-table-column label=操作 width=130
            template slot-scope=scope
                el-button type=text @click=editNer(scope.row) "标注"
                el-button type=text @click="$emit('help', scope.row)" "辅助"
                el-dropdown trigger="click" @command="handleCommand"
                    span class="el-dropdown-link"
                        el-button type=text style="margin-left: 10px" "更多"
                    el-dropdown-menu slot="dropdown"
                        el-dropdown-item :command="{command: 'editWord', data: scope.row}" "编辑word"
                        el-dropdown-item :command="{command: 'mark', data: scope.row}" "标记"
                        el-dropdown-item :command="{command: 'detail', data: scope.row}" "详情"
    el-row .page
        el-pagination
        :current-page=page.currentPage
        :page-size=100
        :page-sizes="[100, 300, 500, 1000]"
        :total=page.total
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        layout="total, sizes, prev, pager, next, jumper"
</template>

<script>
import EditNerDialog from '../EditNerDialog'
import OperationalHistory from '../OperationalHistory'
import EditWordDialog from '../EditWordDialog'
import MarkTermDialog from '../MarkTermDialog'

import {
    testTableHeight
} from '@/assets/js/global.js'

export default {
    props: ['missionId'],
    components: {
        EditNerDialog,
        EditWordDialog,
        OperationalHistory,
        MarkTermDialog
    },
    data() {
        return {
            terms: [], //存储所有的实体term
            termsSelected: [],
            searchWord: '',
            page: {
                currentPage: 1,
                size: 100,
                total: 0
            },
            tableHeight: testTableHeight,
            ner: 'ALL',
            ners: [{ //存储所有的ner类别
                label: '全部',
                value: 'ALL'
            }],
            dataLoading: false,
            status: [1, 2, 5],
            res: '',
            words: [], //提交的words组
            termsId: [],
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
        scriptIsRunning: function() {
            return this.app.scriptIsRunning
        },
    },
    mounted() {
        this.refreshTerms(this.page.currentPage, this.page.size)
        this.getNers()
    },
    methods: {
        // 刷新页面 获取该任务中某ner类别的所有的term
        refreshTerms(page = 1, size = 100) {
            let missionId = this.missionId
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getTermOfNer', {
                    missionId,
                    status: this.status,
                    page,
                    size,
                    ner: this.ner,
                    word: this.searchWord,
                },
                (data) => {
                    this.terms = this.$utils.handleTerms(data.data)
                    this.page.total = data.total
                },
                null,
                () => {
                    this.dataLoading = false
                }
            )
        },
        // 分页
        handleSizeChange(v) {
            this.page.size = v
            this.page.currentPage = 1
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        handleCurrentChange(v) {
            this.page.currentPage = v
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        //获取所有的ner类别
        getNers() {
            let missionId = this.missionId
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getNersOfMission', {
                    missionId
                },
                (data) => {
                    data.forEach(i => {
                        let t = {
                            label: i.ner,
                            value: i.ner,
                        }
                        this.ners.push(t)
                    })
                }
            )
        },
        // 根据所选的ner类别 获取该类别的所有ner
        searchByNer(ner) {
            this.ner = ner
            let q = {
                ner,
                missionId: this.missionId,
                size: this.page.size,
                page: this.page.currentPage,
                status: this.status,
            }
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getTermOfNer',
                q,
                (data) => {
                    this.terms = this.$utils.handleTerms(data.data)
                    this.page.total = data.total
                },
                null,
                () => {
                    this.dataLoading = false
                }
            )
        },
        // 点击“编辑ner” 编辑ner弹窗组件显示
        editNer(row) {
            this.$refs.editNerDialog.show(this.missionId, row.id, row.word)
        },
        // 点击“编辑word” 编辑实体弹窗组件显示
        editWord(row) {
            this.$refs.editWordDialog.show(this.missionId, row.id, row.word)
        },
        submit() {
            this.$utils.submit.call(this)
        },
        handleCommand(command) {
            let c = command.command
            let row = command.data
            if (c === 'editWord') {
                this.editWord(row)
            } else if (c === 'mark') {
                this.markATerm(row)
            } else if (c === 'detail') {
                this.$refs.operationalHistory.show(row)
            }
        },
        markATerm(row) {
            this.$refs.markTermDialog.show(this.missionId, row.id, row.word)
        }
    }
}
</script>

<style scoped lang="stylus">
.wrapper
    height: 100%
    .opt
        margin-bottom: 10px
    .page
        text-align: center
</style>
