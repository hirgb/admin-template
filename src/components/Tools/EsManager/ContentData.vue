<template lang="xtmin">
el-row .height100 :gutter=10
    el-drawer title=内容编辑 :visible.sync="editDrawerVisible" size=40%
        edit-drawer ref=editDrawer @success="editDrawerVisible = false" @cancel="editDrawerVisible = false"
    el-drawer title=新增内容 :visible.sync="cloneDrawerVisible" size=40%
        clone-drawer ref=cloneDrawer @success="cloneDrawerVisible = false" @cancel="cloneDrawerVisible = false"
    el-col .height100 :span=4
        el-card shadow=never .height100
            div slot=header
                span "ES连接"
            el-form label-width=55px
                el-form-item label=地址：
                    el-select
                    placeholder=选择一个ES连接
                    v-model=esConnect
                    :filterable=true
                    :allow-create=true
                    :default-first-option=true
                    @change=getAllEsIndex
                        el-option
                        v-for="i in connects"
                        :key=i
                        :label=i
                        :value=i
            el-tree
            icon-class="el-icon"
            :style="{height: realViewHeight.height - 210 + 'px',overflow: 'auto', marginTop: '20px'}"
            v-loading=indexLoading
            :data=esIndex
            :props=props
            :lazy=true
            :load=loadNode
            :render-content="renderContent"
            @node-click=selectEsType
    el-col .height100 :span=20
        el-card shadow=never .height100
            el-tabs type=card
            #esmanager-query-pane
            ref=queryTabs
            :editable=true
            v-model=activeTab
            @edit="handleTabsEdit"
                el-tab-pane v-for="i in tabs"
                :key=i.name
                :label=i.name
                :name=i.name
                    el-row .info "{{i.url ? `当前地址：${i.url}` : '未选择es数据类别'}}"
                    ace-editor
                    lang="json"
                    theme="chrome"
                    width="100%"
                    height="200"
                    style="margin: 10px 0"
                    v-model=i.content
                    @init="$utils.Tools.jsonEditorInit"
                    el-row
                        el-button type=primary :disabled="!i.url" :loading=dataLoading @click="submit(i)" "执行"
                    el-row .info style="margin-top: 20px;" v-show=i.underSearch
                        span v-html="i.searchText"
                    el-row style="border-top: 1px solid #edf0f4; margin-top: 10px"
                        el-table :data="i.searchResult"
                        cell-class-name=table-cell
                        header-cell-class-name=table-head-cell
                        v-loading=dataLoading
                        :height="i.underSearch ? realViewHeight.height - 470 - 23 : realViewHeight.height - 470"
                            el-table-column prop=_id label=id width=240
                            el-table-column prop=_score label=score width=100
                            el-table-column label=标题名 width=120 :show-overflow-tooltip=true
                                template slot-scope=scope
                                    span "{{scope.row._source.details && scope.row._source.details.name ? scope.row._source.details.name : ''}}"
                            el-table-column prop=_type label=类型 width=80
                            el-table-column label=简介
                                template slot-scope=scope
                                    el-popover placement=top trigger=click width=400 :content="scope.row._source.details && scope.row._source.details.intro ? scope.row._source.details.intro : ''"
                                        p slot=reference .intro "{{ scope.row._source.details && scope.row._source.details.intro ? scope.row._source.details.intro : '' }}"
                            el-table-column label=操作 width=120
                                template slot-scope=scope
                                    el-button type=text @click="showEditDrawer(i, scope.row)" "编辑"
                                    el-button type=text @click="showCloneDrawer(i, scope.row)" "克隆"
                                    el-button type=text @click="deleteEsData(i, scope.row)" "删除"
</template>

<script>
import AceEditor from 'vue2-ace-editor'
import CloneDrawer from './CloneDrawer.vue'
import EditDrawer from './EditDrawer.vue'
import {
    mapGetters
} from 'vuex'

const query = JSON.parse('{"query":{"match_all":{}},"from":0,"size":10}')
// const query = JSON.parse('{"query":{"bool":{"must":{"term":{"details.name":"变形金刚"}}}},"from":0,"size":100}')

export default {
    components: {
        AceEditor,
        CloneDrawer,
        EditDrawer,
    },
    data() {
        return {
            editDrawerVisible: false,
            cloneDrawerVisible: false,
            ners: ['1', '2'],
            esConnect: '',
            connects: [
                '39.100.115.215:8405',
                '47.92.221.26:28405',
                '47.106.253.231:28405',
            ],
            esIndex: [],
            props: {
                label: 'label',
                children: 'children',
                isLeaf: 'leaf'
            },
            tabs: [{
                name: '查询1',
                url: '',
                content: JSON.stringify(query, null, 4),
                searchUrl: '',
                searchResult: [],
                searchText: '',
                underSearch: false,
            }],
            activeTab: '查询1',
            tabIndex: 1,
            dataLoading: false,
            indexLoading: false,
            realViewHeight: new this.$utils.RealViewHeight()
        }
    },
    computed: {
        ...mapGetters({
            app: 'app/getAll',
            user: 'user/getAll',
        }),
    },
    mounted() {
        let connect = window.localStorage.getItem('esConnect')
        if (connect) {
            this.esConnect = connect
            this.getAllEsIndex(connect)
        }

        this.$utils.Patch.tabAddBtnFollow(this.$refs.queryTabs.$el)
    },
    beforeDestroy() {
        this.realViewHeight.unregist()
    },
    methods: {
        showEditDrawer(tab, row) {
            this.editDrawerVisible = true
            this.$nextTick(() => {
                this.$refs.editDrawer.setData(tab, row)
            })
        },
        showCloneDrawer(tab, row) {
            this.cloneDrawerVisible = true
            this.$nextTick(() => {
                this.$refs.cloneDrawer.setData(tab, row)
            })
        },
        getAllEsIndex(connect) {
            if (!/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}:\d{1,5}$/.test(connect)) {
                this.$utils.Patch.error(this, '连接地址不正确')
                return
            }
            this.indexLoading = true
            this.esIndex = []
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/esmanager/getEsIndex', {
                    connect
                },
                (data) => {
                    if (data.length) {
                        window.localStorage.setItem('esConnect', connect)
                        this.esIndex = data.map(i => {
                            return {
                                label: i,
                                children: [],
                                leaf: false,
                            }
                        })
                    }
                },
                null,
                () => {
                    this.indexLoading = false
                }
            )
        },
        loadNode(node, resolve) {
            // console.log(node);
            if (node.level === 0) return resolve([])
            if (node.level > 1) return resolve([])
            let esIndex = node.label,
                host = this.esConnect
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/esmanager/getEsIndexType', {
                    esIndex,
                    host
                },
                (data) => {
                    let t = data.map(i => {
                        return {
                            label: i,
                            children: [],
                            leaf: true
                        }
                    })
                    resolve(t)
                }
            )
        },
        renderContent(h, {
            node
        }) {
            return ( <
                span class = {
                    node.level > 1 ? 'is-leaf' : ''
                } >
                <
                i class = {
                    node.level > 1 ? 'el-icon-notebook-2' : 'el-icon-coin'
                }
                style = {
                    node.level > 1 ? 'color: #369dd9' : 'color: #f9be78'
                } > < /i> <
                span class = "el-tree-node__label"
                style = "padding: 0 5px; line-height: 26px" > {
                    node.label
                } < /span> < /
                span >
            )
        },
        selectEsType(data, node) {
            if (node.level > 1) {
                let tab = this.tabs.find(i => i.name === this.activeTab)
                if (tab) {
                    tab.url = `${this.esConnect}/${node.parent.data.label}/${data.label}`
                }
            }
        },
        handleTabsEdit(targetName, action) {
            if (action === 'add') {
                let tab = this.tabs.find(i => i.name === this.activeTab)
                let newTabName = '查询' + (this.tabIndex + 1)
                let t = {
                    name: newTabName,
                    url: tab.url,
                    content: JSON.stringify(query, null, 4),
                    searchResult: [],
                    searchText: '',
                }
                this.tabs.push(t)
                this.activeTab = newTabName
                this.tabIndex++
            }
            if (action === 'remove') {
                if (this.tabs.length === 1) {
                    return
                }
                let tabs = this.tabs;
                let activeTab = this.activeTab;
                if (activeTab === targetName) {
                    tabs.forEach((tab, index) => {
                        if (tab.name === targetName) {
                            let nextTab = tabs[index + 1] || tabs[index - 1]
                            if (nextTab) {
                                activeTab = nextTab.name
                            }
                        }
                    })
                }
                this.activeTab = activeTab
                this.tabs = tabs.filter(tab => tab.name !== targetName)
            }
        },
        submit(tab) {
            try {
                let [host, index, type] = tab.url.split('/')
                let query = JSON.stringify(JSON.parse(tab.content))
                this.dataLoading = true
                tab.underSearch = true
                tab.searchResult = []
                tab.searchText = `正在执行...`
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/esmanager/searchByQuery', {
                        host,
                        index,
                        type,
                        query
                    },
                    (data) => {
                        if (data.status) {
                            tab.searchResult = data.hits
                            tab.searchUrl = tab.url
                            tab.searchText = `执行成功，共 ${data.hits.length} 条结果`
                        } else {
                            tab.searchResult = []
                            tab.searchText = `<span style="color: red;">执行失败，原因为：${data.msg}</span>`
                        }
                    },
                    (msg) => {
                        tab.searchResult = []
                        tab.searchText = `<span style="color: red;">执行失败，原因为：${msg}</span>`
                    },
                    () => {
                        this.dataLoading = false
                        tab.searchResult = []
                    }
                )
            } catch (e) {
                this.$utils.Patch.error(this, e.message)
            }
        },
        deleteEsData(tab, row) {
            if (!tab.searchUrl) {
                this.$utils.Patch.error(this, '未获取到es数据库信息')
                return
            }
            this.$utils.Patch.confirm(
                this,
                '确定要删除该数据吗?',
                () => {
                    let [host, index, type] = tab.searchUrl.split('/'),
                        docId = row._id
                    let q = {
                        host,
                        index,
                        type,
                        docId,
                        userId: this.user.id
                    }
                    this.$utils.post(
                        this,
                        this.$config.dataServer + 'web/esmanager/deleteDataById',
                        q,
                        () => {
                            this.$utils.Patch.success(this, '删除成功')
                            tab.searchResult = tab.searchResult.filter(i => i !== row)
                        }
                    )
                }
            )
        },
    },
}
</script>

<style scoped lang="stylus">
.intro
    cursor: pointer
    display: -webkit-box
    overflow: hidden
    -webkit-box-orient: vertical
    -webkit-line-clamp: 1
</style>
