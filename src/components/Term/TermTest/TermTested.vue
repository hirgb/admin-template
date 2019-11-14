<!--
 * @Description: 导航条选中‘已测试’tab时 对应该TermTested组件 显示所有已测试的实体
 * @Author: zhangkefei
 * @Date: 2019-08-09 15:48:20
 * @LastEditTime: 2019-09-16 15:11:23
 -->
<template lang="xtmin">
el-row
    operational-history ref=operationalHistory
    el-col :span=24
        el-row .opt :gutter=10
            el-col :span=4
                el-select v-model=ner placeholder=NER类别 @change=searchByNer
                    el-option v-for="i in ners" :key=i.value :label=i.label :value=i.value
            el-col :span=5
                el-input v-model.trim=searchWord @keyup.enter.native="refreshTerms" :clearable=true placeholder=搜索...
        el-table
        v-loading=dataLoading
        cell-class-name=table-cell
        header-cell-class-name=table-head-cell
        :data=terms
        :border=true
        :height=tableHeight
            el-table-column prop=word label=请求实体 width=230
            el-table-column prop=ner label=NER类别
                template slot-scope=scope
                    el-tag v-for="i in scope.row.ners" :type="i.deleted ? 'info' : ''" v-text=i.ner :key=i.ner
            el-table-column prop=pv label=PV width=130
            el-table-column prop=lastHistory.userName label=测试人 width=130
            el-table-column prop=stat label=测试状态 width=130
            el-table-column label=操作 width=130
                template slot-scope=scope
                    el-button type=text @click=detail(scope.row) "详情"
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
import {
    editTableHeight
} from '@/assets/js/global.js'

export default {
    props: ['missionId'],
    components: {
        EditNerDialog,
        OperationalHistory,
    },
    data() {
        return {
            terms: [],
            dataLoading: false,
            searchWord: '',
            tableHeight: editTableHeight,
            ner: 'ALL',
            ners: [{
                label: '全部',
                value: 'ALL'
            }],
            ifraceSrc: 'https://www.baidu.com/s?wd=',
            page: {
                currentPage: 1,
                size: 100,
                total: 0
            },
            helperShow: false
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
    beforeDestroy() {},
    methods: {
        hideHelper() {
            this.helperShow = false
        },
        refreshTerms(page = 1, size = 100) {
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getTestedTermOfNer', {
                    missionId: this.missionId,
                    word: this.searchWord,
                    page,
                    size,
                    ner: this.ner
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
        editNer(row) {
            this.$refs.editNerDialog.show(this.missionId, row.id, row.word)
        },
        search(row) {
            this.helperShow = true
            this.$refs.editHelper.search(row)
        },
        submit(row) {
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/submit', {
                    missionId: this.missionId,
                    role: this.role,
                    words: row.word
                },
                () => {
                    this.$utils.Patch.success(this, '提交成功')
                    this.refreshTerms(this.page.currentPage, this.page.size)
                }
            )
        },
        detail(row){
            this.$refs.operationalHistory.show(row)
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
