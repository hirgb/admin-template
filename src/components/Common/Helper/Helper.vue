<template lang="html">
    <el-tabs id="helper" v-model="activeTabInner">
        <el-tab-pane label="百度搜索" v-if="tabs.includes('search')" name="search">
            <search ref="search" />
        </el-tab-pane>
        <el-tab-pane label="卡片详情" v-if="tabs.includes('webpage')" name="webpage">
            <web-page ref="webPage" />
        </el-tab-pane>
        <el-tab-pane label="测试" v-if="tabs.includes('test')" name="test">
            <test ref="test"
            :term="term"
            :missionId="missionId"
            @reject-success="testReject"
            @pass="testPass" />
        </el-tab-pane>
        <el-tab-pane label="用户请求" v-if="tabs.includes('request')" name="request" :style="{overflowY: 'auto'}">
            <request ref="request" />
        </el-tab-pane>
        <el-tab-pane label="卡片资源" v-if="tabs.includes('resource')" name="resource" :style="{overflowY: 'auto'}">
            <resource ref="resource" />
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import WebPage from './WebPage.vue'
import Search from './Search.vue'
import Request from './Request.vue'
import Resource from './Resource.vue'
import Test from './Test.vue'

export default {
    props: {
        tabs: {
            type: Array,
            required: true,
        },
        activeTab: String,
        missionId: [String, Number],
    },
    data() {
        return {
            term: {},
            activeTabInner: '',
        }
    },
    components: {
        WebPage,
        Search,
        Request,
        Resource,
        Test,
    },
    mounted() {
        this.activeTabInner = this.activeTab || this.tabs[0] || ''
    },
    methods: {
        help(row) {
            if (this.tabs.includes('search')) {
                this.$refs.search.search(row.word)
            }
            if (this.activeTab !== 'test' && this.tabs.includes('request')) {
                this.$refs.request.getUserRequest(row.word)
            }
            if (this.tabs.includes('resource')) {
                this.$refs.resource.getResource(row.word, row.ner)
            }
            if (this.tabs.includes('test')) {
                this.term = row
            }
            if (this.tabs.includes('webpage')) {
                this.$refs.webPage.url = row.ner_url
            }
        },
        showTest() {
            this.activeTabInner = 'test'
        },
        showSearch() {
            this.activeTabInner = 'search'
        },
        showWebPage() {
            this.activeTabInner = 'webpage'
        },
        testPass() {
            this.$emit('test-pass', this.term.word)
            this.term = {}
        },
        testReject() {
            this.$emit('reject-success')
            this.term = {}
        },
    },
}
</script>

<style lang="stylus">
#helper
    height: 100%
    background-color: #fff
    padding: 0 10px
    .el-tabs__content
        height: calc(100% - 60px)
    .el-tab-pane
        height: 100%
</style>
