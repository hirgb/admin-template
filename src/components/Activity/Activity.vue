<template lang="xtmin">
div
    el-row
        el-col :span=3
            el-button type="primary" @click="addActivity" "新建活动"
            el-button @click="refreshActivity" "刷新"
        el-col :span=4
            el-input v-model=searchWord @keyup.enter.native=refreshMissions placeholder=搜索...
    el-row
        el-table
        v-loading="dataLoading"
        :data="activitys"
        :border=true
        cell-class-name=table-cell
        header-cell-class-name=table-head-cell
            el-table-column fixed="left" prop="name" label="活动名称"
            el-table-column prop="status" label="状态" width="100"
            el-table-column prop="channels" label="渠道"
            el-table-column prop="total" label="内容数量" width=80
            el-table-column prop="startTime" label="开始时间" width=150
            el-table-column prop="endTime" label="结束时间" width=150
            el-table-column prop="createTime" label="创建时间" width=150
            el-table-column fixed="right" label="操作" width="150"
                template slot-scope="scope"
                    el-button @click="showDetail(scope.row)" type="text" size="small" "查看"
                    el-button @click="showEditDialog(scope.row)" type="text" size="small" "编辑"
                    el-button @click="deleteActivity(scope.row)" type="text" size="small" "删除"
    add-activity ref="addActivityDialog" @add-success="refreshActivity"
    edit-activity ref="editActivityDialog" :detail="editDetail" @edit-success="refreshActivity"
</template>

<script>
import AddActivity from './AddActivity.vue'
import EditActivity from './EditActivity.vue'

export default {
    components: {
        AddActivity,
        EditActivity,
    },
    data() {
        return {
            dataLoading: false,
            activitys: [],
            editDetail: null,
            searchWord: '',
        }
    },
    mounted() {
        this.refreshActivity()
    },
    methods: {
        addActivity() {
            this.$refs.addActivityDialog.show()
        },
        showDetail(row) {
            let activityName = row.name
            let activityType = row.cardType
            this.$router.push({
                path: 'activityitem',
                query: {
                    actName: activityName,
                    actType: activityType,
                }
            })
        },
        showEditDialog(row) {
            this.editDetail = row
            window.setTimeout(() => {
                this.$refs.editActivityDialog.show()
            }, 0)
        },
        refreshActivity() {
            this.dataLoading = true
            this.$axios.post(this.$config.dataServer + '/web/activity/getAllActivitys')
                .then(res => {
                    let data = res.data
                    this.activitys = data.data
                    this.dataLoading = false
                })
        },
        deleteActivity(row) {
            let id = row.id
            if (!id) {
                this.$utils.Patch.error(this, '未获取到计划id')
                return
            }
            this.$confirm('此操作将永久删除该活动, 是否继续?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$axios.post(this.$config.dataServer + '/web/activity/deleteActivity', this.$z.axios.urlEncode({id}))
                    .then(res => {
                        let data = res.data
                        if (data.code) {
                            this.$utils.Patch.success(this, '删除成功!')
                            this.refreshActivity()
                        } else {
                            this.$utils.Patch.error(this, data.errorMsg)
                        }
                    })
            })
        }
    }
}
</script>

<style scoped lang="stylus">
.el-row
    padding: 10px 0
</style>
