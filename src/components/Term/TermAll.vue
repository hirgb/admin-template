<!--
 * @Description: 'admin'管理员可以操作的页面 显示某任务中所有的实体
 * @Author: zhangkefei
 * @Date: 2019-08-09 15:48:20
 * @LastEditTime: 2019-09-16 10:45:13
 -->
<template lang="xtmin">
el-row .wrapper :gutter=20
    edit-ner-dialog ref=editNerDialog @update-success="refreshTerms(page.currentPage, page.size)"
    edit-word-dialog ref=editWordDialog @update-success="refreshTerms(page.currentPage, page.size)"
    add-new-term ref=AddNewTerm @add-new-success="refreshTerms(page.currentPage, page.size)"
    add-option-term ref=addOptionTerm @success="refreshTerms(page.currentPage, page.size)"
    operational-history ref=operationalHistory
    el-col .data :span=12
        el-row .opt :gutter=10
            el-col :span=4
                el-dropdown
                placement="bottom-start"
                trigger="click"
                :style="{width: '100%'}"
                @command="addData"
                    el-button type=primary :style="{width: '100%'}" "添加数据"
                    el-dropdown-menu slot=dropdown
                        el-dropdown-item command=addOptionTerms "添加已有Term"
                        el-dropdown-item command=addNewTerms "添加自定义Term"
            el-col :span=4
                el-button type=primary :disabled="scriptIsRunning" :style="{width: '100%'}" @click="submit" "{{scriptIsRunning ? '脚本运行' : '提交测试'}}"
            el-col :span=5
                el-select v-model=ner placeholder=NER类别 @change=searchByNer
                    el-option v-for="i in ners" :key=i.value :label=i.label :value=i.value
            el-col :span=10
                el-input v-model=searchWord @keyup.enter.native=refreshTerms :clearable=true placeholder=搜索...
        el-table
        :data=terms
        :border=true
        :height=tableHeight
        cell-class-name=table-cell
        header-cell-class-name=table-head-cell
        v-loading=dataLoading
        @selection-change="v => termsSelected = v"
            el-table-column prop=word label=请求实体 width=130
            el-table-column label=NER类别
                template slot-scope=scope
                    el-tag v-for="i in scope.row.ners" :type="i.deleted ? 'info' : ''" v-text=i.ner :key=i.ner
            el-table-column prop=stat label=状态 width=70
            el-table-column label=操作 width=130
                template slot-scope=scope
                    el-button type=text @click=editNer(scope.row) "标注"
                    el-button type=text @click=help(scope.row) "辅助"
                    el-dropdown trigger="click" @command="handleCommand"
                        span class="el-dropdown-link"
                            el-button type=text style="margin-left: 10px" "更多"
                        el-dropdown-menu slot="dropdown"
                            el-dropdown-item :command="{command: 'test', data: scope.row}" "测试"
                            el-dropdown-item :command="{command: 'editWord', data: scope.row}" "编辑word"
                            el-dropdown-item :command="{command: 'delete', data: scope.row}" "删除"
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
    el-col .data :span=12
        helper ref=helper
        :tabs="['search', 'test', 'request', 'resource']"
        :missionId=missionId
        @reject-success=rejectFromHelper
        @test-pass=passFromHelper
</template>
<script>
import EditNerDialog from './EditNerDialog'
import EditWordDialog from './EditWordDialog'
import AddNewTerm from './AddNewTerm'
import OperationalHistory from './OperationalHistory'
import AddOptionTerm from '../Common/AddDataDialog.vue'
import Helper from '../Common/Helper/Helper.vue'
import {
    testTableHeight
} from '@/assets/js/global.js'
export default {
    props: ['missionId'], //获取path中的动态路由传递的参数
    components: {
        EditNerDialog,
        EditWordDialog,
        AddNewTerm,
        AddOptionTerm,
        OperationalHistory,
        Helper
    },
    data() {
        return {
            terms: [],
            termsSelected: [],
            searchWord: '',
            page: {
                currentPage: 1,
                size: 100,
                total: 0
            },
            tableHeight: testTableHeight,
            ner: 'ALL',
            ners: [{ //存储所有的请求实体的类型
                label: '全部',
                value: 'ALL'
            }],
            dataLoading: false,
            sta: "进行中",
            status: [1, 2, 3, 5],
            testingTerm: {
                word: '',
                ner: '',
                ners: '',
            },
            currentRow: null,
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
        let sta = this.$route.query.sta //任务的状态
        this.sta = sta
    },
    methods: {
        //刷新页面 获取所有的实体terms
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
                    this.terms = []
                    this.$nextTick(() => {
                        this.terms = this.$utils.handleTerms(data.data)
                        this.page.total = data.total
                    })
                },
                null,
                () => {
                    this.dataLoading = false
                }
            )
        },
        submit() {
            this.$utils.submit.call(this)
        },
        handleSizeChange(v) {
            this.page.size = v
            this.page.currentPage = 1
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        handleCurrentChange(v) {
            this.page.currentPage = v
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        // 点击“辅助” helper弹窗组件(百度搜索)出现
        help(row) {
            this.$refs.helper.help(row)
        },
        // 获取当前任务下所有的请求实体
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
        // 选择不同的ner类别 筛选搜索出实体ner
        searchByNer(ner) {
            this.ner = ner
            this.page.currentPage = 1
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        editNer(row) {
            this.$refs.editNerDialog.show(this.missionId, row.id, row.word)
        },
        // 点击“编辑word” 编辑实体弹窗组件显示
        editWord(row) {
            this.$refs.editWordDialog.show(this.missionId, row.id, row.word)
        },
        // 点击“删除” 删除对应的term
        deleteTerm(row) {
            this.$confirm('此操作将永久删除该Term, 是否继续?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                let q = {
                    missionId: this.missionId,
                    words: row.word,
                }
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/mission/deleteTerms',
                    q,
                    () => {
                        this.$utils.Patch.success(this, '删除成功')
                        this.refreshTerms(this.page.currentPage, this.page.size)
                    }
                )
            }).catch(() => {});
        },
        addData(command) {
            if (command === 'addNewTerms') {
                this.addNewTerms()
            } else if (command === 'addOptionTerms') {
                this.addOptionTerms()
            }
        },
        addNewTerms() {
            this.$refs.AddNewTerm.show(this.missionId)
        },
        addOptionTerms() {
            this.$refs.addOptionTerm.show(this.missionId)
        },
        deleteTermFromMissoin(row) {
            this.$confirm('确定将此Term从任务删除吗?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                let q = {
                    missionId: this.missionId,
                    word: row.word,
                    ner: row.ner,
                }
                this.dataLoading = true
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/mission/deleteTermFromMission',
                    q,
                    () => {
                        this.$utils.Patch.success(this, '删除成功')
                        this.refreshTerms(this.page.currentPage, this.page.size)
                    },
                    (data) => {
                        this.$utils.Patch.error(this, data.errorMsg)
                    },
                    () => {
                        this.dataLoading = false
                    }
                )
            }).catch(() => {})
        },
        handleCommand(command) {
            let c = command.command
            let row = command.data
            if (c === 'delete') {
                this.deleteTermFromMissoin(row)
            } else if (c === 'editWord') {
                this.editWord(row)
            } else if (c === 'detail') {
                this.$refs.operationalHistory.show(row)
            } else if (c === 'test') {
                this.test(row)
            }
        },
        test(row) {
            this.setTestingItemDefault()
            this.currentRow = row
            this.testingTerm.word = row.word
            this.testingTerm.ner = row.ner
            this.$refs.helper.help(row)
            this.$refs.helper.showTest()
        },
        setTestingItemDefault() {
            this.testingTerm = {
                word: '',
                ner: '',
                ners: '',
            }
        },
        passFromHelper(e) {
            this.$utils.Patch.testPass.call(this, e)
        },
        rejectFromHelper() {
            this.$utils.Patch.testReject.call(this)
        },
    }
}
</script>

<style scoped lang="stylus">
.wrapper
    height: 100%
    .data
        height: 100%
        .opt
            margin-bottom: 10px
        .page
            text-align: center

</style>
