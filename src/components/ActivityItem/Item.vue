<template>
    <div class="">
        <el-row>当前活动：<span style="font-weight: 600">{{ actName }}</span></el-row>
        <el-row>
            <el-button @click="goback">返回</el-button>
            <el-button type="primary" @click="addItem">新建活动项</el-button>
            <el-button @click="refreshPlan">刷新</el-button>
        </el-row>
        <el-row>
            <el-table
            v-loading="dataLoading"
            :data="plans"
            border
            style="width: 100%">
            <el-table-column fixed prop="name" label="名称" show-overflow-tooltip width="150" />
            <el-table-column prop="image" label="图片" width="120" show-overflow-tooltip> </el-table-column>
            <el-table-column prop="score" label="评分" width="80"> </el-table-column>
            <el-table-column prop="type" label="类型" width="150" show-overflow-tooltip> </el-table-column>
            <el-table-column prop="director" label="导演" width="150" show-overflow-tooltip> </el-table-column>
            <el-table-column prop="actor" label="主演" width="150" show-overflow-tooltip> </el-table-column>
            <el-table-column prop="area" label="国家" width="80" show-overflow-tooltip> </el-table-column>
            <el-table-column prop="lang" label="语言" width="80" show-overflow-tooltip> </el-table-column>
            <el-table-column prop="uid" label="uid" width="250" show-overflow-tooltip> </el-table-column>
            <el-table-column fixed="right" label="操作">
                <template slot-scope="scope">
                    <el-button @click="editPlan(scope.row)" type="text" size="small">编辑</el-button>
                    <el-button @click="editPlanES(scope.row)" type="text" size="small">编辑ES</el-button>
                    <el-button @click="deletePlan(scope.row)" type="text" size="small">删除</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination style="text-align: center" v-show="!!page.total"
            background
          :page-size="10"
          :pager-count="11"
          layout="prev, pager, next"
          :total="page.total"
          @current-change = "pageChange">
        </el-pagination>
    </el-row>
    <add-item ref="addItemDialog" :actName="actName" @add-success="refreshPlan" />
    </div>
</template>

<script>
import AddItem from './AddItem.vue'

export default {
    name: '',
    components: {
        AddItem,
    },
    props: ['actName'],
    data() {
        return {
            dataLoading: false,
            plans: [],
            planDetail: null,
            page: {
                total: 0,
            }
        }
    },
    mounted(){
        this.refreshPlan()
    },
    methods: {
        addItem(){
            this.$refs.addItemDialog.show()
        },
        goback(){
            this.$router.go(-1)
        },
        deletePlan(row){
            // console.log();
            let uid = row.uid
            let type = this.actType
            if (!uid || !type) {
                this.$utils.Patch.error(this, '没有获取到uid或type')
                return
            }

            this.$confirm('此操作将永久删除该计划, 是否继续?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$axios.post(this.$config.dataServer + '/web/plan/deletePlan', this.$z.axios.urlEncode({uid, type}))
                    .then(res => {
                        let data = res.data
                        if (data.code) {
                            this.$utils.Patch.success(this, '删除成功!')
                            this.refreshPlan()
                        } else {
                            this.$utils.Patch.error(this, data.errorMsg)
                        }
                    })
                    .catch(err => {
                        throw err
                    })
            })
        },
        editPlan(row){
            // console.log(row);
            this.planDetail = row
            window.setTimeout(() => {
                this.$refs.editPlanDialog.show()
            }, 0)
        },
        editPlanES(row){
            this.$router.push({
                path: 'developer',
                query: {
                    uid: row.uid,
                }
            })
        },
        refreshPlan(from = 0, size = 10){
            let q = {
                type: this.actType,
                from,
                size,
            }
            this.dataLoading = true
            this.$axios.post(this.$config.dataServer+'web/plan/getplan', this.$z.axios.urlEncode(q))
            .then(res => {
                let data = res.data
                if (data.code) {
                    this.dataLoading = false
                    let plans = data.data.hits
                    this.page.total = data.data.total
                    if (Array.isArray(plans)) {
                        let temp = plans.map((item) => {
                            // console.log(item._source.details.play_link[0]);
                            return {
                                uid: item._source.details.uid,
                                name: item._source.details.name,
                                image: item._source.details.image,
                                score: item._source.details.score,
                                introduce: item._source.details.intro,
                                type: item._source.details.type.join(','),
                                director: item._source.details.director.join(','),
                                actor: item._source.details.actor.join(','),
                                area: item._source.details.area.join(','),
                                lang: item._source.details.lang.join(','),
                                doubanUrl: item._source.details.url,
                                doubanDp: item._source.details.deeplink,
                                playUrl: item._source.details.play_link[0] ? item._source.details.play_link[0].url : '',
                                playDp: item._source.details.play_link[0] ? item._source.details.play_link[0].deeplink : '',
                                intervene: item._source.normalized.intervene,
                            }
                        })
                        this.plans = temp
                    } else {
                        this.$utils.Patch.error(this, '数据格式错误')
                    }
                } else {
                    this.dataLoading = false
                    this.$utils.Patch.error(this, data.errorMsg)
                }
            })
            .catch(err => {
                throw err
            })
        },
        pageChange(currentPage){
            let size = 10
            let from = (currentPage - 1) * size
            this.refreshPlan(from, size)
        }
    }
}
</script>

<style scoped lang="stylus">
.el-row
    padding: 10px 0
</style>
