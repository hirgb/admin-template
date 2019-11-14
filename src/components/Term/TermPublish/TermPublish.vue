<!--
 * @Description: 发布页面
 * @Author: 张克飞
 * @Date: 2019-08-31 10:39:14
 * @LastEditTime: 2019-08-31 10:41:44
 * @LastEditors: 张克飞
 -->
<template lang="xtmin">
div style="height:100%"
    el-row :gutter=20 style="height: 100%"
        el-col :span="user.role === 'admin' ? 12 : 24"
            el-tabs v-model="activeName" class="edit-tabs"
                el-tab-pane label="待发布" name="first" "待发布"
                    term-to-publish
                    ref=termToPublish
                    :missionId=missionId
                    @help="help"
                    @test="test"
                el-tab-pane label="已发布" name="second" "已发布"
                    term-published :missionId=missionId
        el-col :span=12 style="height: 100%" v-if="user.role === 'admin'"
            helper ref="helper"
            :tabs="['search', 'test', 'request', 'resource']"
            :activeTab="helperActiveTab"
            :missionId=missionId
            @reject-success=rejectFromHelper
            @test-pass=passFromHelper
</template>

<script>
import TermToPublish from './TermToPublish.vue'
import TermPublished from './TermPublished.vue'
import Helper from '@/components/Common/Helper/Helper.vue'

export default {
    props: ['missionId'],
    components: {
        TermToPublish,
        TermPublished,
        Helper
    },
    data() {
        return {
            activeName: 'first',
            helperActiveTab: 'search',
            testingTerm: {
                word: '',
                ner: '',
                ners: '',
            },
            currentRow: null,
        };
    },
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        }
    },
    methods: {
        help(row) {
            this.$refs.helper.help(row)
            this.$refs.helper.showSearch()
        },
        setTestingItemDefault(){
            this.testingTerm = {
                word: '',
                ner: '',
                ners: '',
            }
        },
        test(row) {
            this.setTestingItemDefault()
            this.currentRow = row
            this.testingTerm.word = row.word
            this.testingTerm.ner = row.ner
            this.$refs.helper.help(row)
            this.$refs.helper.showTest()
        },
        refreshTerms() {
            this.$refs.termToPublish.refreshTerms()
        },
        passFromHelper(e){
            if (this.currentRow && e && this.currentRow.word === e) {
                //在待发布页面中的测试通过，其实不用做任何工作
                this.$utils.Patch.success(this, '操作成功')
                this.setTestingItemDefault()
            } else {
                this.$utils.Patch.success(this, '当前行与测试term不一致')
            }
        },
        rejectFromHelper(){
            this.refreshTerms()
            this.setTestingItemDefault()
        },
    },
}
</script>
