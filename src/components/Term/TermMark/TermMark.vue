<!--
 * @Description: marker标注员权限下可以操作的页面 头部导航栏中的‘测试’tab
 * @Author: zhangkefei
 * @Date: 2019-08-19 10:39:14
 * @LastEditTime: 2019-09-16 10:04:24
 -->
<template lang="xtmin">
div style="height:100%"
    el-tabs v-model="activeName" class="edit-tabs" v-if="role !== 'admin'"
        el-tab-pane label="待处理" name="first" "待处理"
            term-mark-to-handle :missionId=missionId
        el-tab-pane label="已处理" name="second" "已处理"
            term-mark-handled :missionId=missionId
    el-row v-if="role === 'admin'"
        term-mark-for-admin :missionId=missionId
</template>

<script>
import TermMarkForAdmin from './TermMarkForAdmin.vue'
import TermMarkHandled from './TermMarkHandled.vue'
import TermMarkToHandle from './TermMarkToHandle.vue'

export default {
    props: ['missionId'],
    components: {
        TermMarkForAdmin,
        TermMarkHandled,
        TermMarkToHandle
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
