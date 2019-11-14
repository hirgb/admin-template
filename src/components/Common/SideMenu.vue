<template lang="html">
    <div class="">
        <el-menu
        default-active="dashboard"
        class="el-menu-vertical-demo"
        background-color="#545c64"
        text-color="#FFF"
        active-text-color="#ffd04b"
        :default-openeds="defaultOpeneds"
        @select="goto">
        <el-menu-item index="home">
            <i class="el-icon-s-home"></i>
            <span slot="title">首页</span>
        </el-menu-item>
        <el-submenu index="1">
            <template slot="title">
                <i class="el-icon-s-order"></i>
                <span>活动</span>
            </template>
            <el-menu-item index="activity">运营活动</el-menu-item>
            <el-menu-item index="hotnews">H5新闻活动</el-menu-item>
        </el-submenu>
        <el-submenu index="2">
            <template slot="title">
                <i class="el-icon-menu"></i>
                <span>标注</span>
            </template>
            <el-menu-item index="mission">NER标注</el-menu-item>
            <el-menu-item index="deploylist">发布管理</el-menu-item>
        </el-submenu>
        <el-submenu index="3">
            <template slot="title">
                <i class="el-icon-view"></i>
                <span>快速干预</span>
            </template>
            <el-menu-item index="redisintervene">Redis干预</el-menu-item>
        </el-submenu>
        <el-submenu index="4">
            <template slot="title">
                <i class="el-icon-s-help"></i>
                <span>工具</span>
            </template>
            <el-menu-item index="tools/esmanager">ES管理</el-menu-item>
        </el-submenu>
        <!-- <el-menu-item index="developer">
            <i class="el-icon-setting"></i>
            <span slot="title">开发者工具</span>
        </el-menu-item> -->
    </el-menu>
</div>
</template>

<script>
export default {
    name: 'SideMenu',
    data() {
        return {
            defaultOpeneds: ['1','2','3','4']
        }
    },
    methods: {
        //点击侧边栏导航条 路由跳转到响应页面
        goto(index) {
            // console.log(index, indexPath);
            let indexpath = '/' + index
            this.$router.push({
                path: indexpath
            })
        },
    }
}
</script>
