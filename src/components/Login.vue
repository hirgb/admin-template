<template lang="html">
<div class="login-wrap">
    <div class="ms-login">
        <div class="ms-title">
            登录「 智慧识屏 」后台
        </div>
        <el-form class="ms-content" ref="form" :model="form" :rules="rules"
        label-width="60px" label-position="right" :hide-required-asterisk="true">
            <el-form-item prop="loginName" label="用户名">
                <el-input v-model="form.loginName"></el-input>
            </el-form-item>
            <el-form-item prop="password" label="密码">
                <el-input type="password" v-model="form.password"></el-input>
            </el-form-item>
            <div class="login-btn">
                <el-button type="primary" @click="submit">登录</el-button>
            </div>
        </el-form>
    </div>
</div>
</template>

<script>
export default {
    data: function() {
        return {
            form: {
                loginName: '',
                password: this.$config.debug ? '123456' : '',
            },
            rules: {
                loginName: [{
                    required: true,
                    message: '请输入用户名',
                    trigger: 'blur'
                }],
                password: [{
                    required: true,
                    message: '请输入密码',
                    trigger: 'blur'
                }]
            }
        }
    },
    methods: {
        submit() {
            this.$refs.form.validate((isSuccess) => {
                if (isSuccess) {
                    this.$utils.post(
                        this,
                        this.$config.dataServer+'/web/user/login',
                        this.form,
                        (data) => {
                            this.$store.commit('user/set', {key: 'id', value: data.id})
                            this.$store.commit('user/set', {key: 'loginName', value: data.loginName})
                            this.$store.commit('user/set', {key: 'nickName', value: data.nickName})
                            this.$store.commit('user/set', {key: 'role', value: data.role})
                            this.$router.push({path: '/home'})
                        }
                    )
                }
            })
        },
    }
}
</script>

<style lang="stylus">
.login-wrap
    position: relative
    width: 100vw
    height: 100vh
    background-color: #f9f9f9

.ms-title
    position: absolute
    top: -50px
    width: 100%
    line-height: 50px
    text-align: center
    font-size: 20px

.ms-login
    position: absolute
    left: 0
    top: 0
    right: 0
    bottom: 0
    width: 400px
    height: 220px
    margin: auto
    border-radius: 5px
    background: #fff
    overflow: visible
    border: 1px #bbb solid

.ms-content
    padding: 30px 30px

.login-btn button
    width: 100%
</style>
