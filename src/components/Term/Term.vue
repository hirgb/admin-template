<!--
 * @Description: 点击当前任务中的‘查看’后跳转到该term页面
 * @Author: zhangkefei
 * @Date: 2019-08-09 15:48:20
 * @LastEditTime: 2019-09-16 10:46:03
 -->
<template lang="xtmin">
div style="height: 100%"
    term-all v-if="app.activeHeadMenu === 'termAll'" :missionId=missionId
    term-hot v-if="app.activeHeadMenu === 'termHot'" :missionId=missionId
    term-edit v-if="app.activeHeadMenu === 'termEdit'" :missionId=missionId
    term-test v-if="app.activeHeadMenu === 'termTest'" :missionId=missionId
    term-publish v-if="app.activeHeadMenu === 'termPublish'" :missionId=missionId
    term-reject v-if="app.activeHeadMenu === 'termReject'" :missionId=missionId
    term-mark v-if="app.activeHeadMenu === 'termMark'" :missionId=missionId
</template>

<script>
import TermAll from './TermAll'
import TermHot from './TermHot/TermHot.vue'
import TermEdit from './TermEdit/TermEdit.vue'
import TermTest from './TermTest/TermTest.vue'
import TermPublish from './TermPublish/TermPublish.vue'
import TermReject from './TermReject/TermReject.vue'
import TermMark from './TermMark/TermMark.vue'

export default {
    props: ['missionId'],
    components: {
        TermAll,
        TermHot,
        TermEdit,
        TermTest,
        TermPublish,
        TermReject,
        TermMark,
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
        if (this.role === 'admin') {
            this.$utils.Patch.setHeadActiveMenu.call(this, 'termAll')
            if (this.app.currentMission === this.$config.everydayMissionName) {
                this.$utils.Patch.setHeadActiveMenu.call(this, 'termHot')
            }
        } else if (this.role === 'marker') {
            this.$utils.Patch.setHeadActiveMenu.call(this, 'termEdit')
            if (this.app.currentMission === this.$config.everydayMissionName) {
                this.$utils.Patch.setHeadActiveMenu.call(this, 'termHot')
            }
        } else if (this.role === 'tester') {
            this.$utils.Patch.setHeadActiveMenu.call(this, 'termTest')
        }
    }
}
</script>
