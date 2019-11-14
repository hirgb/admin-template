<template lang="xtmin">
div .height100
    el-row
        el-col :span=8
            el-input
            .input-with-select
            placeholder="请输入内容"
            v-model.trim="searchWord"
            :clearable=true
            @keyup.enter.native=search
            @clear=clearSearch
                el-select v-model=searchMode slot="prepend" placeholder="请选择"
                    el-option label="docId" value="docId"
                    el-option label="标题" value="docTitle"
                el-button slot="append" icon="el-icon-search" @click=search
        el-col :span=3 .inner-center :style="{height: '32px', alignItems: 'unset', paddingLeft: '20px'}"
            el-checkbox v-model=exactSearch "全字匹配"
    el-row .info style="padding-top: 10px" v-show=underSearch
        span v-html=resultText
    el-row style="padding-top: 10px"
        el-table :data="history"
        cell-class-name=table-cell
        header-cell-class-name=table-head-cell
        v-loading=dataLoading
        :height="tabHeight"
            el-table-column prop=host label=服务器地址 width=150
            el-table-column prop=index label=index width=180 :filters="indexFilter" :filter-method="filterIndex"
            el-table-column prop=type label=type width=80 :filters="typeFilter" :filter-method="filterType"
            el-table-column prop=docId label=Doc_id width=200
                template slot-scope=scope
                    span v-html=scope.row.docId
            el-table-column prop=docTitle label=标题 :show-overflow-tooltip=true
                template slot-scope=scope
                    span v-html=scope.row.docTitle
            el-table-column prop=optType label=操作类型 width=80 :filters="optFilter" :filter-method="filterOpt"
            el-table-column prop=user.nickName label=修改人 width=100
            el-table-column prop=optTime label=修改时间 width=150
            el-table-column label=操作 width=80
                template slot-scope=scope
                    el-button type=text @click="deleteHistory(scope.row)" "删除"
        el-row .center
            el-pagination
            :current-page=page.currentPage
            :page-size=page.size
            :page-sizes=page.sizes
            :total=page.total
            @size-change=sizeChange
            @current-change=currentPageChange
            layout="total, sizes, prev, pager, next, jumper"
</template>

<script>
export default {
    data() {
        return {
            searchWord: '',
            searchMode: 'docTitle',
            exactSearch: false,
            underSearch: false,
            resultText: '',
            history: [],
            dataLoading: false,
            page: new this.$utils.Page([100, 300, 500, 1000]),
            optFilter: [{
                text: '删除',
                value: '删除'
            }, {
                text: '添加',
                value: '添加'
            }, {
                text: '修改',
                value: '修改'
            }],
            indexFilter: [],
            typeFilter: [],
            realViewHeight: new this.$utils.RealViewHeight()
        }
    },
    computed: {
        tabHeight() {
            return this.underSearch ? this.realViewHeight.height - 160 - 22 : this.realViewHeight.height - 160
        }
    },
    mounted() {
        this.page.registRefresh(this.getHistory)
        this.getHistory()
    },
    beforeDestroy() {
        this.realViewHeight.unregist()
    },
    methods: {
        getHistory() {
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/esmanager/getHistory', {
                    searchWord: this.searchWord,
                    searchMode: this.searchMode,
                    exactSearch: this.exactSearch ? 1 : 0,
                    page: this.page.currentPage,
                    size: this.page.size
                },
                (data) => {
                    this.page.total = data.total

                    data.data.forEach(i => {
                        if (this.searchWord) {
                            i[this.searchMode] = i[this.searchMode].replace(this.searchWord, `<i class="search-highlight">${this.searchWord}</i>`)
                        }
                    })
                    this.history = data.data
                    this.underSearch = !!this.searchWord
                    this.resultText = `共 ${data.total} 条搜索结果`
                    this.indexFilter = Array.from(new Set(this.history.map(i => i.index))).map(i => ({
                        text: i,
                        value: i
                    }))
                    this.typeFilter = Array.from(new Set(this.history.map(i => i.type))).map(i => ({
                        text: i,
                        value: i
                    }))
                },
                null,
                () => {
                    this.dataLoading = false
                }
            )
        },
        search(){
            if (!this.searchWord) {
                this.$utils.Patch.error(this, '搜索关键词不能为空')
                return
            }
            if (!this.searchMode) {
                this.$utils.Patch.error(this, '请选择搜索方式')
                return
            }
            this.getHistory()
        },
        clearSearch() {
            this.searchWord = ''
            this.searchMode = 'docTitle'
            this.exactSearch = false
            this.getHistory()
        },
        deleteHistory(row) {
            this.$confirm('确定要删除该记录吗?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                let q = {
                    id: row.id
                }

                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/esmanager/deleteHistoryById',
                    q,
                    () => {
                        this.$utils.Patch.success(this, '删除成功')
                        this.getHistory()
                    }
                )
            })
        },
        filterOpt(value, row) {
            return row.optType === value;
        },
        filterIndex(value, row) {
            return row.index === value;
        },
        filterType(value, row) {
            return row.type === value;
        },
        currentPageChange(v) {
            this.page.currentPageChange(v)
        },
        sizeChange(v) {
            this.page.sizeChange(v)
        }
    }
}
</script>
