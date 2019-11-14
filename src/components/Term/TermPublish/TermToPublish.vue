<!--
 * @Description:导航条选中‘待发布’tab时 对应该TermToPublish组件 显示所有待发布的实体
 * @Author: zhangkefei
 * @LastEditTime: 2019-09-16 10:06:15
 -->
<template lang="xtmin">
div
    operational-history ref=operationalHistory
    edit-ner-dialog ref=editNerDialog @update-success="refreshTerms(page.currentPage, page.size)"
    el-row .opt :gutter=10
        el-col :span=5
            el-select v-model=ner placeholder=NER类别 @change=searchByNer
                el-option v-for="i in ners" :key=i.value :label=i.label :value=i.value
        el-col :span=10
            el-input v-model=searchWord @keyup.enter.native=refreshTerms :clearable=true placeholder=搜索...
    el-table
    v-loading=dataLoading
    cell-class-name=table-cell
    header-cell-class-name=table-head-cell
    :data=terms
    :border=true
    :height=tableHeight
    @selection-change="(v)=>{termsSelected = v}"
        el-table-column prop=word label=请求实体 width=130
        el-table-column label=NER类别
            template slot-scope=scope
                el-tag v-for="i in scope.row.ners" :type="i.deleted ? 'info' : ''" v-text=i.ner :key=i.ner
        el-table-column prop=pv label=PV width=60
        el-table-column label=提交时间 width=160
            template slot-scope=scope
                span v-text="scope.row.lastHistory.time"
        el-table-column label=操作 width=130
            template slot-scope=scope
                el-button type=text v-if="role === 'admin'" @click="$emit('help', scope.row)" "辅助"
                el-button type=text v-if="role === 'admin'" @click="$emit('test', scope.row)" "测试"
                el-button type=text v-if="role === 'tester'" @click="$refs.operationalHistory.show(scope.row)" "详情"
                el-dropdown v-if="role === 'admin'" trigger="click" @command="handleCommand"
                    span class="el-dropdown-link"
                        el-button type=text style="margin-left: 10px" "更多"
                    el-dropdown-menu slot="dropdown"
                        el-dropdown-item :command="{command: 'editNer', data: scope.row}" "标注"
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
</template>

<script>
import OperationalHistory from '../OperationalHistory'
import EditNerDialog from '../EditNerDialog'
import {
    editTableHeight
} from '@/assets/js/global.js'

export default {
    props: ['missionId'],
    components: {
        OperationalHistory,
        EditNerDialog,
    },
    data() {
        return {
            terms: [],
            termsSelected: [],
            dataLoading: false,
            searchWord: '',
            tableHeight: editTableHeight,
            ner: 'ALL',
            ners: [{
                label: '全部',
                value: 'ALL'
            }],
            page: {
                currentPage: 1,
                size: 100,
                total: 0
            },
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
        refreshTerms(page = 1, size = 100) {
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getTermOfNer', {
                    missionId: this.missionId,
                    status: 4,
                    page,
                    size,
                    ner: this.ner,
                    word: this.searchWord
                },
                (data) => {
                    this.terms = this.$utils.handleTerms(data.data)
                    this.terms = this.$utils.handleHistory(this.terms)
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
        searchByNer(ner) {
            this.ner = ner
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
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
        publish(row) {
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/publish', {
                    missionId: this.missionId,
                    role: this.user.role,
                    words: row.word
                },
                () => {
                    this.$utils.Patch.success(this, '发布成功')
                    this.refreshTerms(this.page.currentPage, this.page.size)
                }
            )
        },
        handleCommand(command) {
            let act = command.command
            let row = command.data
            if (act === 'editNer') {
                this.editNer(row)
            } else if (act === 'delete') {
                this.deleteTerm(row)
            } else if (act === 'detail') {
                this.$refs.operationalHistory.show(row)
            }
        },
        editNer(row) {
            this.$refs.editNerDialog.show(this.missionId, row.id, row.word)
        },
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
            }).catch(() => {});
        },
        deleteTerms() {
            this.$confirm('此操作将永久删除该Term, 是否继续?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                let words = this.termsSelected.map(i => i.word).join(',')
                let q = {
                    missionId: this.missionId,
                    words,
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
    }
}
</script>

<style scoped lang="stylus">
.opt
    margin-bottom: 10px
.page
    text-align: center
</style>
