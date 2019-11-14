<template lang="xtmin">
div style="height:100%"
    el-tabs v-model="activeName" class="edit-tabs"
        el-tab-pane label="待处理" name="first" "待处理"
            term-reject-to-handle :missionId=missionId
        el-tab-pane label="已处理" name="second" "已处理"
            term-reject-handled :missionId=missionId
</template>

<script>
import TermRejectToHandle from './TermRejectToHandle.vue'
import TermRejectHandled from './TermRejectHandled.vue'

export default {
    props: ['missionId'],
    components: {
        TermRejectToHandle,
        TermRejectHandled
    },
    data() {
        return {
            activeName: 'first',
        };
    },
}
</script>
