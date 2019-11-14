<!--
 * @Description: marker标注员权限下可以操作的页面 头部导航栏中的‘标注’tab
 * @Author: zhangkefei
 * @Date: 2019-08-19 10:39:14
 -->
<template lang="xtmin">
div style="height: 100%"
    el-row :gutter=20 style="height: 100%"
        el-col :span=12
            term-to-edit ref=termToEdit :missionId=missionId @help=help
        el-col :span=12 style="height: 100%"
            helper ref="editHelper" :tabs="['search', 'request', 'resource']"
</template>

<!-- el-tabs v-model="activeName" class="edit-tabs"
    el-tab-pane label="标注中" name="first" "标注中"
    el-tab-pane label="被驳回" name="second" "被驳回"
        term-test-rejected :missionId=missionId @help=help @edit-ner-success="refreshTerms" -->
<script>
import TermToEdit from './TermToEdit'
import TermTestRejected from './TermTestRejected'
import Helper from '@/components/Common/Helper/Helper.vue'
export default {
    props: ['missionId'],
    components: {
        TermToEdit,
        TermTestRejected,
        Helper
    },
    data() {
        return {
            activeName: 'first',
            name: this.$route.query.name,
        };
    },
    mounted() {
    },
    methods: {
        help(row){
            this.$refs.editHelper.help(row)
        },
        refreshTerms(){
            this.$refs.termToEdit.refreshTerms()
        }
    },
}
</script>
