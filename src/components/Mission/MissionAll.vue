<template lang="xtmin">
div
    create-mission-dialog ref=createMissionDialog @create-success=refreshMissions
    add-data-dialog ref=addDataDialog @success=refreshMissions
    el-row :gutter=10 :style="{marginBottom: '10px'}"
        el-col :span=2
            el-button .width100 type=primary v-if="['admin'].includes(role)" @click=createMission "新建任务"
        el-col :span=6
            el-input v-model=searchWord @keyup.enter.native=refreshMissions placeholder=搜索...
    el-table
    v-loading=dataLoading
    cell-class-name=table-cell
    header-cell-class-name=table-head-cell
    :data=missions
        el-table-column prop=name label=名称 :show-overflow-tooltip="true"
        el-table-column prop=status label=状态 width=100 v-if="['admin'].includes(role)"
        el-table-column prop=marker_number label=标注人数 width=100 v-if="['admin'].includes(role)"
        el-table-column prop=total label=任务量 width=80
        el-table-column prop=marked label=标注数 width=100 v-if="['marker'].includes(role)"
        el-table-column prop=to_test label=待测试数 width=100  v-if="['marker', 'tester'].includes(role)"
        el-table-column prop=test_pass label=测试通过数 width=100
        el-table-column prop=test_reject label=测试驳回数 width=100
        el-table-column prop=published label=已发布数 width=100
        el-table-column label=进度 width=180
            template slot-scope=scope
                el-progress :percentage=calcProgress(scope.row)
        el-table-column prop=create_time label=创建时间 width=150
        el-table-column label=操作 width=120
            template slot-scope=scope
                el-button type=text @click=viewMission(scope.row) "查看"
                el-button type=text @click=markToFinished(scope.row) v-if="['admin'].includes(role) && scope.row.status === '进行中' && !scope.row.protect" "标记完成"
                el-button type=text @click=deleteMission(scope.row) v-if="['admin'].includes(role) && !scope.row.protect && (scope.row.status === '已完成' || scope.row.status === '待初始化')" "删除"
</template>

<script>
import CreateMissionDialog from './CreateMissionDialog'
import AddDataDialog from '../Common/AddDataDialog'

export default {
    components: {
        CreateMissionDialog,
        AddDataDialog,
    },
    data() {
        return {
            dataLoading: false,
            searchWord: '',
            missions: [],
        }
    },
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        },
        role: function(){
            return this.user.role
        },
        app: function() {
            return this.$store.getters['app/getAll']
        },
    },
    watch: {
        'app.activeHeadMenu': function(){
            this.refreshMissions()
        }
    },
    mounted() {
        this.refreshMissions()
    },
    methods: {
        createMission() {
            this.$refs.createMissionDialog.show()
        },
        refreshMissions(e) {
            let q = {
                userId: this.user.id,
                role: this.user.role,
                type: this.app.activeHeadMenu,
                name: this.searchWord,
            }
            this.dataLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/getMissions',
                q,
                (data) => {
                    if (!data) {
                        this.missions = []
                        return
                    }
                    this.missions = data.map(i => {
                        let marker = i.marker
                        if (!marker) {
                            i.marker_number = 0
                        } else {
                            i.marker_number = marker.slice(1, -1).split(',').length
                        }
                        return i
                    })
                    //是否需要添加数据
                    if (e && e.act === 'addData') {
                        let missionId = e.missionId
                        let row = this.missions.find(i => i.id === missionId)
                        if (row) {
                            this.addData(missionId)
                        } else {
                            this.$utils.Patch.error(this, '未找到对应mission')
                        }
                    }
                },
                null,
                ()=>{
                    this.dataLoading = false
                }
            )
        },
        statusFilter(value, row) {
            return row.status === value
        },
        viewMission(row) {
            this.$router.push({path: 'term', query: {missionId: row.id, sta:row.status, name:row.name}})
            this.$store.commit('app/set', {key: 'currentMission', value: row.name})
        },
        deleteMission(row) {
            if (row.test_pass) {
                this.$utils.Patch.error(this, '还有测试成功的term未发布，不能删除')
                return
            }
            let msg = '此操作将永久删除该任务, 是否继续?'
            this.$confirm(msg, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$utils.post(
                    this,
                    this.$config.dataServer+'web/Missionmanage/deleteMission',
                    {missionId: row.id},
                    ()=>{
                        this.$utils.Patch.success(this, '删除成功')
                        this.refreshMissions()
                    }
                )
            })
        },
        addData(missionId){
            this.$refs.addDataDialog.show(missionId)
        },
        calcProgress(row){
            let p = Math.floor(parseInt(row.published) / parseInt(row.total) * 100)
            return isNaN(p) ? 0 : p
        },
        markToFinished(row){
            if (row.test_pass) {
                this.$utils.Patch.error(this, '还有测试成功的term未发布，不能标记完成')
                return
            }
            this.$confirm('确认要将此任务标记为“已完成”吗？此操作不可恢复', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$utils.post(
                    this,
                    this.$config.dataServer+'web/missionmanage/markToFinished',
                    {missionId: row.id},
                    ()=>{
                        this.$utils.Patch.success(this, '操作成功')
                        this.refreshMissions()
                    }
                )
            })
        },
    }
}
</script>

<style lang="css">
</style>
