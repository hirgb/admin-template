<!--
 * @Description: marker标注员权限下可以操作的页面 头部导航栏中的‘测试’tab
 * @Author: 赵笑迎
 * @Date: 2019-09-11 17:07:08
 * @LastEditTime: 2019-09-16 10:38:57
 -->
<template lang="xtmin">
div style="height:100%"
    el-tabs v-model="activeName" class="edit-tabs" v-if="role === 'marker'"
        el-tab-pane label="测试中" name="first" "测试中"
            term-to-test-for-marker :missionId=missionId
        el-tab-pane label="已测试" name="second" "已测试"
            term-tested :missionId=missionId
    el-row v-if="role === 'tester'"
        term-to-test-for-tester :missionId=missionId
</template>

<script>
import TermToTestForTester from './TermToTestForTester.vue'
import TermToTestForMarker from './TermToTestForMarker.vue'
import TermTested from './TermTested'
export default {
    props: ['missionId'],
    components: {
        TermTested,
        TermToTestForTester,
        TermToTestForMarker,
    },
    data() {
        return {
            activeName: 'first',
        };
    },
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        },
        role: function() {
            return this.user.role
        }
    }
}
</script>
