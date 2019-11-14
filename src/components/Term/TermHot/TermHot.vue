<template lang="xtmin">
el-row .height100 :gutter=10
    el-col .height100 :span=4
        el-card shadow=never .height100
            div slot=header
                span "线上识别类型"
            el-date-picker .width100
            v-model=date
            value-format="yyyy-MM-dd"
            type=date
            placeholder="选择日期"
            el-menu
            .content-nav
            :default-active="defaultNer"
            @select="onNerChange"
                el-menu-item v-for="i in ners" :index=i :key=i
                    span slot=title "{{i}}"
    el-col :span=20
        edit-ner-dialog ref=editNerDialog @update-success="refreshTerms(defaultNer)"
        el-drawer :visible.sync="drawerVisible" size=40%
            helper ref="helper" :tabs="['webpage', 'search', 'request']"
        el-card shadow=never .height100
            el-row :gutter=10
                el-col :span=8
                    el-input placeholder="请输入内容" prefix-icon="el-icon-search"
                    v-model.trim="searchWord"
                    @keyup.enter.native="refreshTerms"
                        el-button slot="append" icon="el-icon-search" @click=refreshTerms
                el-col :span=3 .inner-center :style="{height: '32px', alignItems: 'unset'}"
                    el-checkbox v-model=exactSearch "全字匹配"
            el-row :style="{marginTop: '10px', fontSize: '12px', color: '#666'}"
                div v-show="searchMode"
                    span "共"
                    span .highlight " {{page.total}} "
                    span "条搜索结果"
            el-row :gutter=10
                el-table :data="terms" style="width: 100%"
                cell-class-name=table-cell
                header-cell-class-name=table-head-cell
                v-loading=dataLoading
                :height="searchMode ? tableHeight : tableHeight + 20"
                :default-sort="{prop: 'ner_display_pv', order: 'descending'}"
                @sort-change=sort
                    el-table-column prop=ner_name label=Term width=120
                        template slot-scope=scope
                            span v-html="searchMode ? scope.row.ner_name_search : scope.row.ner_name" style="cursor: pointer" @click="help(scope.row)"
                    el-table-column prop=detail label=简介
                        template slot-scope=scope
                            el-popover placement=top trigger=click width=400 :content="scope.row.ner_detail"
                                p slot=reference .ner-detail "{{ scope.row.ner_detail }}"
                    el-table-column prop=ner_type label=NER类别 width=120
                    el-table-column prop=ner_display_pv label=PV数 width=80 sortable=custom
                    el-table-column prop=ner_uv label=UV数 width=80 sortable=custom
                    el-table-column prop=ner_rank_index label=上升指数 width=100 sortable=custom
                    el-table-column label=操作 width=150 v-if="allowRoles.includes(role)"
                        template slot-scope=scope
                            el-button type=text @click="help(scope.row)" "辅助"
                            el-button type=text @click="editNer(scope.row)" "干预"
                            el-button type=text v-show="scope.row.status" @click="revoke(scope.row)" "取消干预"
            el-row .center
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
import Helper from '@/components/Common/Helper/Helper.vue'

export default {
    components: {
        EditNerDialog,
        Helper,
    },
    data() {
        return {
            searchWord: '',
            searchMode: false,
            drawerVisible: false,
            tableHeight: window.innerHeight - 216,
            date: this.$z.timeFormat('yyyy-MM-dd', -1),
            ners: [],
            defaultNer: '',
            terms: [],
            page: {
                currentPage: 1,
                size: 100,
                total: 0
            },
            exactSearch: false,
            allowRoles: ['marker', 'admin'],
            everydayMission: null,
            dataLoading: false,
            order: 'ner_display_pv desc',
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
    },
    watch: {
        date(n) {
            this.page.currentPage = 1
            this.getAllNers(n)
        },
    },
    mounted() {
        this.getEverydayMissionInfo()
        this.getAllNers()
    },
    beforeRouteLeave(to, from, next) {
        window.clearInterval(this.checkTimer)
        next()
    },
    methods: {
        onNerChange(ner) {
            this.defaultNer = ner
            this.page.currentPage = 1
            this.searchWord = ''
            // this.exactSearch = false
            this.order = 'ner_display_pv desc'
            this.refreshTerms()
        },
        refreshTerms() {
            let q = {
                ner: this.defaultNer,
                date: this.date,
                page: this.page.currentPage,
                size: this.page.size,
                word: this.searchWord,
                exact: this.exactSearch && !!this.searchWord ? 1 : 0,
                order: this.order,
            }
            let url = 'web/everydaymission/getTerms'
            let searchMode = !!this.searchWord
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + url,
                q,
                (data) => {
                    this.terms = data.terms.data.map(i => {
                        searchMode && (i.ner_name_search = i.ner_name.replace(this.searchWord, `<i class="highlight">${this.searchWord}</i>`))
                        return i
                    })
                    this.page.total = data.terms.total
                },
                null,
                () => {
                    this.searchMode = searchMode
                    this.dataLoading = false
                }
            )
        },
        handleSizeChange(v) {
            this.page.size = v
            this.page.currentPage = 1
            this.refreshTerms()
        },
        handleCurrentChange(v) {
            this.page.currentPage = v
            this.refreshTerms()
        },
        getEverydayMissionInfo() {
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/Mission/getEverydayMissionInfo',
                null,
                (data) => {
                    if (data) {
                        this.everydayMission = data
                    } else {
                        this.$utils.Patch.error(this, '未获取到日常线上巡检任务信息')
                    }
                }
            )
        },
        editNer(row) {
            if (this.everydayMission) {
                let missionId = this.everydayMission.id
                let date = this.date
                let missionName = this.everydayMission.name
                this.$refs.editNerDialog.show(missionId, row.id, row.ner_name, true, {
                    date,
                    missionName,
                })
            } else {
                this.$utils.Patch.error(this, '未获取到日常线上巡检任务信息')
            }
        },
        revoke(row) {
            if (!this.everydayMission) {
                this.$utils.Patch.error(this, '未获取到日常线上巡检任务信息')
                return
            }
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/everydaymission/revoke', {
                    word: row.ner_name,
                    missionId: this.everydayMission.id
                },
                () => {
                    this.$utils.Patch.success(this, '操作成功')
                    this.refreshTerms(this.defaultNer)
                }
            )
        },
        getAllNers(date) {
            let q = {
                date: (date || this.date)
            }
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/everydaymission/getNersOfDate',
                q,
                (data) => {
                    if (Array.isArray(data)) {
                        this.ners = data.map(i => i.ner_type)
                        this.defaultNer = this.ners[0]
                        this.refreshTerms()
                    }
                },
                null,
                () => {
                    this.dataLoading = false
                }
            )
        },
        help(row) {
            this.drawerVisible = true
            row.word = row.ner_name
            this.$nextTick(() => {
                this.$refs.helper.help(row)
                this.$refs.helper.showSearch()
            })
        },
        sort({ prop, order }) {
            if (order === 'ascending') {
                this.order = `${prop} asc`
            } else if (order === 'descending') {
                this.order = `${prop} desc`
            } else {
                this.order = ''
            }
            this.refreshTerms()
        },
    },
}
</script>
<style scoped lang="stylus">
.ner-detail
    cursor: pointer
    display: -webkit-box
    overflow: hidden
    -webkit-box-orient: vertical
    -webkit-line-clamp: 2
</style>
