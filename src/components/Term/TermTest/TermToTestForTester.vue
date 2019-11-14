<!--
 * @Description: 'tester'测试员可以操作的页面 显示某任务中所有待测试的实体
 * @Author: zhangkefei
 * @Date: 2019-08-09 15:48:20
 * @LastEditTime: 2019-09-16 10:40:21
 -->
<template lang="xtmin">
div style="height: 100%"
    operational-history ref=operationalHistory
    mark-term-dialog ref=markTermDialog @success="refreshTerms(page.currentPage, page.size)"
    el-row .wrapper :gutter=10
        el-col .data :span=10
            el-row .opt :gutter=10
                el-col :span=5
                    el-select v-model=ner placeholder=NER类别 @change=searchByNer
                        el-option v-for="i in ners" :key=i.value :label=i.label :value=i.value
                el-col :span=4 v-if="role === 'tester'"
                el-col :span=10
                    el-input v-model=searchWord @keyup.enter.native=refreshTerms :clearable=true placeholder=搜索...
            el-table
            ref=termTable
            v-loading=dataLoading
            cell-class-name=table-cell
            header-cell-class-name=table-head-cell
            :data=terms
            :border=true
            :height=tableHeight
            :highlight-current-row=true
            @current-change="v => currentRow = v"
            @selection-change="v => termsSelected = v"
                el-table-column prop=word label=请求实体 width=150
                el-table-column label=NER类别
                    template slot-scope=scope
                        el-tag v-for="i in scope.row.ners" :type="i.deleted ? 'info' : ''" v-text=i.ner :key=i.ner
                el-table-column label=操作 width=130
                    template slot-scope=scope
                        el-button type=text @click=test(scope.row) "测试"
                        el-button type=text @click=markATerm(scope.row) "标记"
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
        el-col .data :span=14 :style="{height: tableHeight + 40 + 'px'}"
            helper ref=helper
            :tabs="['search', 'test', 'resource']"
            activeTab=test
            :missionId=missionId
            @reject-success=rejectFromHelper
            @test-pass=passFromHelper
</template>

<script>
import OperationalHistory from '../OperationalHistory'
import Helper from '@/components/Common/Helper/Helper.vue'
import MarkTermDialog from '../MarkTermDialog'
import {
    testTableHeight
} from '@/assets/js/global.js'
export default {
    props: ['missionId'],
    components: {
        OperationalHistory,
        MarkTermDialog,
        Helper,
    },
    data(){
        return {
            terms: [],
            termsSelected: [],
            dataLoading: false,
            searchWord: '',
            tableHeight: testTableHeight,
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
            helperShow: false,
            testingTerm: {
                word: '',
                ner: '',
                ners: '',
            },
            currentRow: null,
        }
    },
    computed: {
        user: function(){
            return this.$store.getters['user/getAll']
        },
        role: function(){
            return this.user.role
        }
    },
    mounted(){
        this.refreshTerms(this.page.currentPage, this.page.size)
        this.getNers()
    },
    methods: {
        hideHelper(){
            this.helperShow = false
        },
        setTestingItemDefault(){
            this.testingTerm = {
                word: '',
                ner: '',
                ners: '',
            }
        },
        test(row){
            this.setTestingItemDefault()
            this.currentRow = row
            this.testingTerm.word = row.word
            this.testingTerm.ner = row.ner
            this.$refs.helper.help(row)
            this.$refs.helper.showTest()
        },
        detail(row){
            this.$refs.operationalHistory.show(row)
        },
        helpFromHelper(e){
            if (this.currentRow && e && this.currentRow.word === e) {
                this.search(this.currentRow)
            } else {
                this.$utils.Patch.success(this, '当前行与测试term不一致')
            }
        },
        editNerFromHelper(e){
            if (this.currentRow && e && this.currentRow.word === e) {
                this.editNer(this.currentRow)
            } else {
                this.$utils.Patch.success(this, '当前行与测试term不一致')
            }
        },
        passFromHelper(e){
            this.$utils.Patch.testPass.call(this, e)
        },
        rejectFromHelper() {
            this.$utils.Patch.testReject.call(this)
        },
        refreshTerms(page = 1, size = 100) {
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getTermOfNer', {
                    missionId: this.missionId,
                    status: 3,
                    page,
                    size,
                    ner: this.ner,
                    word: this.searchWord
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
            this.page.currentPage = 1
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        getNers() {
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getNersOfMission', {
                    missionId: this.missionId
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
        editNer(row) {
            this.$refs.editNerDialog.show(this.missionId, row.id, row.word)
        },
        markATerm(row) {
            this.$refs.markTermDialog.show(this.missionId, row.id, row.word)
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
