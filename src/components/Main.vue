<template lang="html">
<el-container id="page">
    <el-aside>
        <div id="logo">
            智慧识屏运营
        </div>
        <side-menu />
    </el-aside>
    <el-container>
        <el-header>
            <header-menu />
            <header-mission-name />
            <header-user />
        </el-header>
        <el-main>
            <router-view></router-view>
        </el-main>
    </el-container>
</el-container>
</template>

<script>
import SideMenu from './Common/SideMenu.vue'
import HeaderMenu from './Common/HeaderMenu.vue'
import HeaderUser from './Common/HeaderUser'
import HeaderMissionName from './Common/HeaderMissionName.vue'

export default {
    components: {
        SideMenu,
        HeaderMenu,
        HeaderUser,
        HeaderMissionName,
    },
    mounted(){
        // eslint-disable-next-line
        console.log('当前版本：' + this.$config.version);
    }
}
</script>

<style lang="stylus">
@import '../assets/css/global.styl'
</style>
