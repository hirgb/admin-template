<template>
<div id="app">
    <router-view></router-view>
</div>
</template>

<script>
export default {
    name: 'app',
    mounted() {
        if (this.$config.debug) {
            return
        }
        this.checkTimer = window.setInterval(() => {
            if (this.$route.path !== '/term') {
                return
            }
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/checkSubmitStatus', {
                    mode: 'simple'
                },
                (data) => {
                    this.$store.commit('app/set', {
                        key: 'scriptIsRunning',
                        value: !!data.isRunning
                    })
                }
            )
        }, 3000)
    },
}
</script>

<style lang="stylus">
body
    margin: 0
    padding: 0
#app
    overflow-x: hidden
</style>
