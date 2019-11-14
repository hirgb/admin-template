<!--公共头部-右边切换‘用户’类别和退出-->
<template lang="xtmin">
div .user-menu
    el-row :gutter=20
        el-col :span=20 style="text-align: right"
            span .user-name "{{user.loginName}}"
        el-col :span=4
            i .fa .fa-sign-out .user-name .btn @click=logout
</template>

<script>
export default {
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        }
    },
    methods: {
        logout() {
            this.$store.commit('user/clear') //点击退出(注销) 清除用户信息
            this.$router.push({
                path: '/'
            }) //且路由跳转到登陆页面
        }
    }
}
</script>

<style scoped lang="stylus">
.user-menu
    position: absolute
    top: 0
    right: 0
    width: 300px
    height: 60px
    border: none
.user-name
    color: #333
    line-height: 60px
.btn
    cursor: pointer
    &:hover
        color: #666
</style>
