<!--
 * @Description: 在marker权限下，导航条选中‘待测试’tab时 显示所有正在测试的实体
 * @Author: zhangkefei
 * @Date: 2019-08-09 15:48:20
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
        refreshTerms(page = 1, size = 100) {
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getTermOfNer', {
                    missionId: this.missionId,
                    status: 3,
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
.transition
    position: absolute
    height: 100vh
    width: 800px
    top: 0
    bottom: 0
    background-color: #ffffff
    color: #000
    z-index: 100
    box-shadow: 1px 1px 5px #00000099
    transition: all .3s ease
.in
    right: 0px
.out
    right: -810px
.close
    position: absolute
    top: 0
    left: 0
    width: 50px
    height: 50px
    background-color: #00000099
    color: #fff
    cursor: pointer
    font-size: 40px
    text-align: center
    line-height: 50px
</style>
