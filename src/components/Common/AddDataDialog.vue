<!--
 * @Description: 添加已有实体
 * @Author: sunshuixian
 * @Date: 2019-07-11 17:07:08
 * @LastEditTime: 2019-09-12 15:55:45
 -->
<template lang="xtmin">
el-dialog
title=添加数据
:visible.sync=dialogFormVisible
:fullscreen=true
:close-on-press-escape=false
custom-class=add-data-dialog
    el-row :style="{height: contentHeight + 'px'}"
        el-col :span=24 style="height: 100%"
            el-row :gutter=10 style="margin-bottom: 15px"
                el-col :span=3
                    el-select v-model=ner placeholder=NER类别 :multiple=true :filterable=true :collapse-tags=true style="width: 100%"
                        el-option v-for="i in ners" :key=i.value :label=i.label :value=i.value
                el-col :span=3
                    el-select v-model=source
                        el-option label=全部来源 value=all
                        el-option label=来源1 value=online
                        el-option label=来源2 value=grab
                el-col :span=3
                    el-select v-model=orderByPV placeholder=请求次数排序
                        el-option label=PV升序 value=pv
                        el-option label=PV降序 value="pv desc"
                        el-option label=取消PV排序 value=""
                el-col :span=3
                    el-select v-model=orderByCreateTime placeholder=创建时间排序
                        el-option label=创建时间升序 value=created_at
                        el-option label=创建时间降序 value="created_at desc"
                        el-option label=取消创建时间排序 value=""
                el-col :span=3
                    el-select v-model=orderByUpdateTime placeholder=更新时间排序
                        el-option label=更新时间升序 value=updated_at
                        el-option label=更新时间降序 value="updated_at desc"
                        el-option label=取消更新时间排序 value=""
                el-col :span=5
                    el-input v-model=searchWord placeholder=关键词...
                el-col :span=2
                    el-button type=primary :style="{width: '100%'}" @click="refreshTerms(1, 100)" "搜索"
                el-col :span=2
                    el-button type=primary :style="{width: '100%'}" @click=selectAllThisPage "全选当前页"
            el-row :gutter=10
                el-col :span=10
                    el-table
                    :data=terms
                    :border=true
                    :height=tableHeight
                    cell-class-name=table-cell
                    header-cell-class-name=table-head-cell
                    v-loading=dataLoading
                        el-table-column prop=word label=请求实体 width=150 fixed=left :show-overflow-tooltip=true
                        el-table-column prop=ner label=NER类别 width=100 :show-overflow-tooltip=true
                        el-table-column prop=pv label=请求次数 width=70
                        el-table-column prop=created_at label=创建时间 width=150
                        el-table-column prop=updated_at label=更新时间 width=150
                        el-table-column label=操作 width=110 fixed=right
                            template slot-scope=scope
                                el-button type=text @click=help(scope.row) "辅助"
                                el-button type=text v-show="!scope.row.checked" @click=select(scope.row) "选择"
                                el-button type=text v-show="scope.row.checked" @click=unselect(scope.row) "取消选择"
                    el-row style="text-align: center"
                        el-pagination
                        :current-page=page.currentPage
                        :page-size=100
                        :page-sizes="[100, 300, 500, 1000]"
                        :total=page.total
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
                        layout="total, sizes, prev, next, jumper"
                el-col :span=6
                    el-table
                    :data=termsSelected
                    :border=true
                    :height=tableHeight
                    cell-class-name=table-cell
                    header-cell-class-name=table-head-cell
                        el-table-column prop=word label=请求实体 :show-overflow-tooltip=true
                        el-table-column prop=ner label=NER类别 width=100 :show-overflow-tooltip=true
                        el-table-column label=操作 width=80
                            template slot-scope=scope
                                el-button type=text @click="noSelect(scope.$index, scope.row)" "取消选择"
                el-col :span=8 :style="{height: tableHeight + 'px'}"
                    helper ref=editHelper :tabs="['search', 'request', 'resource']"
    div slot=footer
        el-button :disabled=dataLoading @click="dialogFormVisible = false" "取消"
        el-button type=primary :disabled=dataLoading @click=addData "确定"
</template>
<script>
import Helper from '../Common/Helper/Helper.vue'

export default {
    components: {
        Helper,
    },
    data() {
        return {
            terms: [],
            termsSelected: [],
            dialogFormVisible: false,
            dataLoading: false,
            initting: false,
            searchWord: '',
            form: {
                missionId: 0,
            },
            page: {
                currentPage: 1,
                size: 100,
                total: 0
            },
            tableHeight: window.innerHeight - 210,
            contentHeight: window.innerHeight - 54 - 62 - 30,
            markers: [],
            testers: [],
            markerLoading: false,
            testerLoading: false,
            ner: ['ALL'],
            ners: [],
            orderByPV: 'pv desc',
            orderByUpdateTime: '',
            orderByCreateTime: '',
            source: 'all',
        }
    },
    computed: {
        user: function(){
            return this.$store.getters['user/getAll']
        },
    },
    watch: {
        ner: function(v) {
            if (v.length > 1 && v[0] === 'ALL') {
                this.ner.shift()
            } else if (v.length > 1 && v.includes('ALL')) {
                this.ner = ['ALL']
            }
        },
    },
    methods: {
        show(id) {
            if (id) {
                this.form.missionId = id
                this.termsSelected = []
                this.ner = ['ALL']
                this.dialogFormVisible = true
                this.getNers()
                this.refreshTerms()
            } else {
                this.$utils.Patch.error(this, '未获取到missionId')
            }
        },
        addData() {
            if (!this.termsSelected.length) {
                this.$utils.Patch.error(this, '未选择数据')
                return
            }
            this.dataLoading = true
            let words = [...new Set(this.termsSelected.map(i => i.word))].join(',')
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/Missionmanage/addData', {
                    missionId: this.form.missionId,
                    words,
                    userId: this.user.id,
                    userName: this.user.loginName,
                },
                () => {
                    this.dialogFormVisible = false
                    this.$utils.Patch.success(this, '操作成功')
                    this.$emit('success')
                },
                null,
                () => {
                    this.dataLoading = false
                }
            )
        },
        getNers() {
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/term/getAllNers',
                null,
                (data) => {
                    this.ners = data.filter(i => {
                        return !!i.ner
                    }).map(i => {
                        return {
                            label: i.ner,
                            value: i.ner,
                        }
                    })
                    this.ners.unshift({
                        label: 'ALL',
                        value: 'ALL'
                    })
                }
            )
        },
        genWorkerStr(worker) {
            if (Array.isArray(worker)) {
                let t = worker.join(',')
                return ',' + t + ','
            } else {
                return ''
            }
        },
        searchByNer(ner) {
            this.ner = ner
            this.page.currentPage = 1
            this.refreshTerms(this.page.currentPage, this.page.size)
        },
        refreshTerms(page = 1, size = 100) {
            if (this.ner.length === 0) {
                this.ner = ['ALL']
            }
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/term/getTermOfNer', {
                    page,
                    size,
                    ner: this.ner,
                    word: this.searchWord,
                    orderByPV: this.orderByPV,
                    orderByUpdateTime: this.orderByUpdateTime,
                    orderByCreateTime: this.orderByCreateTime,

                },
                (data) => {
                    this.terms = data.data.map(i => {
                        i.checked = false
                        return i
                    })
                    this.page.total = data.total
                },
                null,
                () => {
                    this.dataLoading = false
                }
            )
        },
        help(row) {
            this.$refs.editHelper.help(row)
        },
        select(row) {
            row.checked = true
            let word = row.word,
                ner = row.ner

            let exist = this.termsSelected.some(i => i.word === word && i.ner === ner)
            if (!exist) {
                this.termsSelected.push(row)
            }
        },
        noSelect(index, row) {
            this.termsSelected.splice(index, 1)
            let target = this.terms.find(i => (i.word === row.word && i.ner === row.ner))
            if (target) {
                target.checked = false
            }
        },
        unselect(row) {
            let targetIndex = this.termsSelected.findIndex(i => (i.word === row.word && i.ner === row.ner))
            if (targetIndex != -1) {
                row.checked = false
                this.termsSelected.splice(targetIndex, 1)
            }
        },
        selectAllThisPage() {
            this.terms.forEach(i => {
                this.select(i)
            })
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
    }
}
</script>

<style lang="stylus">
.add-data-dialog
    .el-dialog__body
        padding: 15px 20px
        background-color: #edf0f4
    .el-dialog__footer
        text-align: center
</style>
