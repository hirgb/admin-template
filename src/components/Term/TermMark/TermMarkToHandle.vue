<!--
 * @Description: 导航栏中“我的标记”下的“待处理”按钮，选中按钮，即可显示对应操作人的标记记录
 * @Author: sunshuixian
 * @Date: 2019-08-30 14:28:20
 * @LastEditTime: 2019-09-16 11:58:10
 -->
<template lang="xtmin">
div style="height: 100%"
    operational-history ref=operationalHistory
    el-col :span=24 .wrapper
        el-row .opt :gutter=10
            el-col :span=4
                el-select v-model=ner placeholder=NER类别 @change=searchByNer
                    el-option v-for="i in ners" :key=i.value :label=i.label :value=i.value
            el-col :span=5
                el-input v-model.trim=searchWord @keyup.enter.native="refreshTerms" :clearable=true placeholder=搜索...
        el-table
        ref=termTable
        v-loading=dataLoading
        cell-class-name=table-cell
        header-cell-class-name=table-head-cell
        :data=terms
        :border=true
        :height=tableHeight
            el-table-column prop=word label=请求实体 width=160
            el-table-column label=NER类别
                template slot-scope=scope
                    el-tag v-for="i in scope.row.ners" :type="i.deleted ? 'info' : ''" v-text=i.ner :key=i.ner
            el-table-column label=标记类型 width=120
                template slot-scope=scope
                    span v-text="scope.row.lastHistory.optData.type"
            el-table-column label=标记原因 :show-overflow-tooltip=true width=200
                template slot-scope=scope
                    span v-text="scope.row.lastHistory.optData.reason"
            el-table-column label=标记时间 width=150
                template slot-scope=scope
                    span v-text="scope.row.lastHistory.time"
            el-table-column label=操作 width=130
                template slot-scope=scope
                    el-button type=text @click=detail(scope.row) "详情"
                    el-button type=text @click=revoke(scope.row) "撤销"
        el-row .page v-show=false
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
import {
    editTableHeight
} from '@/assets/js/global.js'

export default {
    props: ['missionId'],
    components: {
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
        refreshTerms(page = 1, size = 1000000000) {
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getMyMarkTermOfNer', {
                    missionId: this.missionId,
                    page,
                    size,
                    ner: this.ner,
                    word: this.searchWord,
                    userId: this.user.id
                },
                (data) => {
                    this.terms = this.$utils.handleTerms(data)
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
        revoke(row) {
            this.$confirm('确定要撤消此项实体词的标记吗?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                let history = {
                    userId: this.user.id,
                    userName: this.user.loginName,
                    optType: 'revokeMark',
                }
                let q = {
                    userId: this.user.id,
                    role: this.user.role,
                    missionId: this.missionId,
                    word: row.word,
                    history: JSON.stringify(history),
                }
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/mission/revokeMark',
                    q,
                    () => {
                        this.$utils.Patch.success(this, '操作成功')
                        this.refreshTerms()
                    },
                )
            })
        },
        detail(row) {
            this.$refs.operationalHistory.show(row)
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
