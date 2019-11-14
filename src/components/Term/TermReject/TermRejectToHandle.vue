<!--
 * @Description: "我的驳回"中的“待处理”
 * @Author: zhangkefei
 * @Date: 2019-09-10 17:07:08
 * @LastEditTime: 2019-09-16 10:08:26
 -->
<template lang="xtmin">
div
    el-row .opt :gutter=10
        el-col :span=3
            el-select v-model=ner placeholder=NER类别 @change=searchByNer
                el-option v-for="i in ners" :key=i.value :label=i.label :value=i.value
        el-col :span=5
            el-input v-model=searchWord @keyup.enter.native=refreshTerms :clearable=true placeholder=搜索...
    el-table
    v-loading=dataLoading
    cell-class-name=table-cell
    header-cell-class-name=table-head-cell
    :data=terms
    :border=true
    :height=tableHeight
    @selection-change="v => termsSelected = v"
        el-table-column prop=word label=请求实体 width=200
        el-table-column label=NER类别
            template slot-scope=scope
                el-tag v-for="i in scope.row.ners" :type="i.deleted ? 'info' : ''" v-text=i.ner :key=i.ner
        el-table-column label=驳回类型 width=150
            template slot-scope=scope
                span v-text="scope.row.lastHistory.optData.type"
        el-table-column label=驳回原因 width=200
            template slot-scope=scope
                span v-text="scope.row.lastHistory.optData.reason"
        el-table-column label=驳回时间 width=250
            template slot-scope=scope
                span v-text="scope.row.lastHistory.time"
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
import {
    editTableHeight
} from '@/assets/js/global.js'

export default {
    props: ['missionId'],
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
    mounted(){
        this.refreshTerms(this.page.currentPage, this.page.size)
        this.getNers()
    },
    methods: {
        refreshTerms(page = 1, size = 100) {
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getMyRejectTermOfNer', {
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
    },
}
</script>
<style scoped lang="stylus">
.opt
    margin-bottom: 10px
.page
    text-align: center
</style>
