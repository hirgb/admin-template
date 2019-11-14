<template lang="xtmin">
el-row .wrapper :gutter=20
    edit-ner-dialog ref=editNerDialog @update-success="refreshTerms(page.currentPage, page.size)"
    edit-word-dialog ref=editWordDialog @update-success="refreshTerms(page.currentPage, page.size)"
    operational-history ref=operationalHistory
    el-col .data :span=12
        el-row .opt :gutter=10
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
            el-table-column prop=stat label=状态 width=80
            el-table-column label=操作 width=120
                template slot-scope=scope
                    el-button type=text @click=editNer(scope.row) "标注"
                    el-button type=text @click=help(scope.row) "辅助"
                    el-dropdown trigger="click" @command="handleCommand"
                        span class="el-dropdown-link"
                            el-button type=text style="margin-left: 10px" "更多"
                        el-dropdown-menu slot="dropdown"
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
    el-col .data :span=12 :style="{height: tableHeight + 40 + 'px'}"
        helper ref=editHelper :tabs="['search', 'request', 'resource']"
</template>

<script>
import OperationalHistory from '../OperationalHistory'
import EditNerDialog from '../EditNerDialog'
import EditWordDialog from '../EditWordDialog'
import Helper from '@/components/Common/Helper/Helper.vue'
import {
    testTableHeight
} from '@/assets/js/global.js'

export default {
    props: ['missionId'],
    components: {
        OperationalHistory,
        EditNerDialog,
        EditWordDialog,
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
            sta: "进行中"
        }
    },
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        },
        role: function() {
            return this.user.role
        }
    },
    mounted() {
        this.refreshTerms(this.page.currentPage, this.page.size)
        this.getNers()
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
                    status: 7,
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
        handleSizeChange(v) {
            this.page.size = v
            this.page.currentPage = 1
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        handleCurrentChange(v) {
            this.page.currentPage = v
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        // 点击“辅助” editHelper弹窗组件(百度搜索)出现
        help(row) {
            this.$refs.editHelper.help(row)
            // this.$refs.editHelper.search(row)
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
        editNer(row) {
            this.$refs.editNerDialog.show(this.missionId, row.id, row.word)
        },
        editWord(row) {
            this.$refs.editWordDialog.show(this.missionId, row.id, row.word)
        },
        handleCommand(command) {
            let c = command.command
            let row = command.data
            if (c === 'delete') {
                this.deleteTerm(row)
            } else if (c === 'editWord') {
                this.editWord(row)
            } else if (c === 'detail') {
                this.$refs.operationalHistory.show(row)
            }
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
                    word: row.word,
                }
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/mission/deleteTermFromMission',
                    q,
                    () => {
                        this.$utils.Patch.success(this, '删除成功')
                        this.refreshTerms(this.page.currentPage, this.page.size)
                    }
                )
            })
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
