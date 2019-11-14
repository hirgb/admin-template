<!--头部公共 活动导航条 组件-->
<template lang="xtmin">
el-tabs .tabs v-model="activeTab"
    el-tab-pane v-for="i in activeTabs" :label=i.label :name=i.name :key=i.name
</template>
 <!-- @tab-click=selectTab -->
<script>
import {
    menu
} from '@/assets/js/head-menu.js'
export default {
    data() {
        return {
            tabs: [], //导航条的内容
        }
    },
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        },
        app: function() {
            return this.$store.getters['app/getAll']
        },
        activeTab: {
            get: function(){
                return this.app.activeHeadMenu
            },
            set: function(v){
                this.$utils.Patch.setHeadActiveMenu.call(this, v)
            },
        },
        activeTabs: function() {
            let path = this.$route.path
            let tabs
            if (path === '/term' &&
                this.app.currentMission !== this.$config.everydayMissionName
            ) {
                tabs = this.tabs.filter(i => i.name !== 'termHot')
            } else {
                tabs = this.tabs
            }
            return tabs
        },
    },
    watch: {
        '$route.path': function(newVal) {
            this.genTabsData(newVal)
        },
    },
    mounted() {
        this.genTabsData(this.$route.path)
    },
    methods: {
        genTabsData(path) { //根据url中的路径和当前用户的角色 获取其相应的导航条
            let role = this.user.role
            path = path.slice(1)
            // console.log(path);
            if (path === 'tools/esmanager') {
                this.tabs = menu[path]
                return
            }
            if (menu[path] && menu[path][role]) {
                let tabs = menu[path][role]
                this.tabs = tabs
            } else {
                this.tabs = []
            }
        },
    }
}
</script>

<style lang="stylus">
.tabs
    position: absolute
    bottom: -16px
</style>
