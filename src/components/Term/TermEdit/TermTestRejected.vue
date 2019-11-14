<!--
 * @Description: "标注"中“被驳回”的term
 * @Author: sunshuixian
 * @Date: 2019-09-11 17:07:08
 * @LastEditTime: 2019-09-16 10:02:30
 -->
<template lang="xtmin">
el-row .wrapper
    edit-ner-dialog ref=editNerDialog @update-success=onNerEditSuccess
    edit-word-dialog ref=editWordDialog @update-success="refreshTerms(page.currentPage, page.size)"
    mark-term-dialog ref=markTermDialog @success="refreshTerms(page.currentPage, page.size)"
    operational-history ref=operationalHistory
    el-row .opt :gutter=10
        el-col :span=5
            el-select v-model=ner placeholder=NER类别 @change=searchByNer
                el-option v-for="i in ners" :key=i.value :label=i.label :value=i.value
        el-col :span=3
        el-col :span=10
            el-input v-model=searchWord @keyup.enter.native=searchTerms :clearable=true placeholder=搜索...
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
        el-table-column label=驳回类型 width=70
            template slot-scope=scope
                span v-text="scope.row.rejectHistory.optData.type"
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
    editTableHeight
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
            terms: [],
            termsSelected: [],
            searchWord: '',
            page: {
                currentPage: 1,
                size: 100,
                total: 0
            },
            tableHeight: editTableHeight,
            ner: 'ALL',
            ners: [{
                label: '全部',
                value: 'ALL'
            }],
            dataLoading: false,
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
            const q = {
                missionId: this.missionId,
                page,
                size,
                ner: this.ner,
                word: this.searchWord,
                userId: this.user.id
            }
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getRejectedTermOfNer',
                q,
                (data) => {
                    let terms = this.$utils.handleTerms(data)
                    this.findRejectHistory(terms)
                    this.terms = terms
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
        searchByNer(ner) {
            this.ner = ner
            this.page.currentPage = 1
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        editNer(row) {
            this.$refs.editNerDialog.show(this.missionId, row.id, row.word)
        },
        editWord(row) {
            this.$refs.editWordDialog.show(this.missionId, row.id, row.word)
        },
        onNerEditSuccess() {
            this.refreshTerms(this.page.currentPage, this.page.size)
            this.$emit('edit-ner-success')
        },
        searchTerms() {
            this.page.currentPage = 1
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        findRejectHistory(terms) {
            terms.forEach(i =>{
                if(i.history){
                    const history = JSON.parse(i.history).reverse()
                    i.rejectHistory = history[0]
                }
            })
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
