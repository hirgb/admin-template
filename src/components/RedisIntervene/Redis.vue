<template lang="xtmin">
    div style="height: 100%"
        redis-intervene v-if="app.activeHeadMenu === 'redisIntervene'"
</template>

<script>
import RedisIntervene from './RedisIntervene'


export default {
    components: {
        RedisIntervene,
    },
    computed: {
        app: function(){
            return this.$store.getters['app/getAll']
        }
    },
    mounted(){
        this.$store.commit('app/set', {key: 'activeHeadMenu', value: 'redisIntervene'})
    }
}
</script>

<style lang="css">
</style>
