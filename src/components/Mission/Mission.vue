<template lang="xtmin">
div
    mission-all
</template>

<script>
import MissionAll from './MissionAll'

export default {
    components: {
        MissionAll,
    },
    computed: {
        app: function(){
            return this.$store.getters['app/getAll']
        }
    },
    mounted(){
        this.$store.commit('app/set', {key: 'activeHeadMenu', value: 'missionAll'})
    }
}
</script>

<style lang="css">
</style>
