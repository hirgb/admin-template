<!--
 * @Description: 点击当前任务中的‘查看’后跳转到该term页面
 * @Author: zhangkefei
 * @Date: 2019-08-09 15:48:20
 * @LastEditTime: 2019-09-16 10:46:03
 -->
<template lang="xtmin">
div style="height: 100%"
    keep-alive
        content-data v-if="app.activeHeadMenu === 'contentData'"
    content-history v-if="app.activeHeadMenu === 'contentHistory'"
</template>

<script>
import ContentData from './ContentData.vue'
import ContentHistory from './ContentHistory.vue'

export default {
    components: {
        ContentData,
        ContentHistory,
    },
    computed: {
        app: function() {
            return this.$store.getters['app/getAll']
        },
        user: function() {
            return this.$store.getters['user/getAll']
        },
        role: function() {
            return this.user.role
        }
    },
    mounted() {
        this.$utils.Patch.setHeadActiveMenu.call(this, 'contentData')
    }
}
</script>
