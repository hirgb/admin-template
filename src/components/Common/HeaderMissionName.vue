<template lang="html">
<div class="name" v-show="visible">
    {{ missionName }}
</div>
</template>

<script>
export default {
    computed: {
        app: function() {
            return this.$store.getters['app/getAll']
        },
        missionName: function() {
            return this.app.currentMission
        },
        visible: function() {
            return ['/term'].includes(this.$route.path)
        }
    }
}
</script>

<style lang="stylus" scoped>
.name
    color: #f56c6c
    font-size: 14px
    position: absolute
    bottom: 0
    line-height: 40px
    height: 40px
    left: 50%
    transform: translate(-50%, 0)
</style>
