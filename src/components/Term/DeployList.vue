<!--
 * @Descripttion: 发布历史记录的展示，页面最初展示5条记录，加载数据之后可再增加5条记录，每次加载都是在原来基础上增加5条记录
 * @Author: 张克飞
 * @Date: 2019-07-18 20:38:18
 * @LastEditTime: 2019-09-16 10:42:16
 -->
<template lang="xtmin">
el-row .height100 :gutter="20" v-loading=loading
    el-col :span="16"
        el-timeline
            el-timeline-item v-for="i in deployList" :timestamp=i.title :key=i.title placement="top"
                el-card shadow=never v-if="i.action === 'sync'"
                    el-tooltip v-for="j in i.tags" :key=j.term :content=j.ner effect="light"
                        el-tag style="margin-right: 5px; cursor: pointer;"  "{{ j.term }}"
            el-button type=primary :loading=false @click=loadMore() style="margin-left:30px" "加载更多"
    el-col :span="4"
        el-button .block type=success v-if="['admin'].includes(role)"
        :disabled="deployList[0] && !deployList[0].tags.length"
        @click=syncGit
        "同步到Git"
        el-button .block type=danger v-if="['admin'].includes(role)"
        :disabled="!deployList[1] || deployList[1].action === 'deploy'"
        @click=deploy "发布"
</template>

<script>
export default {
    data() {
        return {
            loading: false,
            deployList: [],
            page: 1,
        }
    },
    computed: {
        user: function() {
            return this.$store.getters['user/getAll']
        },
        role: function() {
            return this.user.role
        },
        app: function() {
            return this.$store.getters['app/getAll']
        },
    },
    mounted() {
        this.getTimeLine()
    },
    methods: {
        syncGit() {
            this.$confirm('确定同步到Git吗？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.loading = true
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/mission/publish',
                    {
                        userId: this.user.id,
                        userName: this.user.loginName
                    },
                    () => {
                        this.$utils.Patch.success(this, '操作成功')
                        this.getTimeLine()
                    },
                    null,
                    () => {
                        this.loading = false
                    }
                )
            }).catch(err => {
                this.$utils.Patch.error(this, err)
            })
        },
        deploy() {
            this.$confirm('确定发布吗？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.loading = true
                this.$utils.post(
                    this,
                    this.$config.dataServer + 'web/mission/deploy',
                    null,
                    () => {
                        this.$utils.Patch.success(this, '操作成功')
                        this.getTimeLine()
                    },
                    null,
                    () => {
                        this.loading = false
                    }
                )
            }).catch(err => {
                this.$utils.Patch.error(this, err)
            })
        },
        async getTimeLine() {
            this.loading = true
            let sync = await this.$axios.post(this.$config.dataServer + 'web/mission/getTermsToSyncGit')
            let history = await this.$axios.post(this.$config.dataServer + 'web/mission/getLastDeployHistory?page=' + this.page)
            let timeLine = []
            let termsToSync = {
                title: '即将同步',
                action: 'sync',
                tags: []
            }
            sync.data.data.forEach(i => {
                termsToSync.tags.push({
                    term: i.term,
                    ner: i.ner.join(', ')
                })
            })
            timeLine.push(termsToSync)
            history.data.data.forEach(i => {
                let t = {
                    title: `${i.title} ${i.action === 'deploy' ? '上线' : '同步'}`,
                    action: i.action,
                    tags: []
                }
                if (i.action === 'sync' && Array.isArray(i.terms)) {
                    i.terms.forEach(j => {
                        t.tags.push({
                            term: j.term,
                            ner: j.ner.join(', ')
                        })
                    })
                }
                timeLine.push(t)
            })
            this.loading = false
            this.deployList = timeLine
        },
        async loadMore() {
            this.page++
            this.loading = false
            // let sync = await this.$axios.post(this.$config.dataServer+'web/mission/getTermsToSyncGit')
            let history = await this.$axios.post(this.$config.dataServer + 'web/mission/getLastDeployHistory?page=' + this.page)
            if (history.data.data.length === 0) {
                this.$message('没有更多数据')
            } else {
                history.data.data.forEach(i => {
                    let t = {
                        title: `${i.title} ${i.action === 'deploy' ? '上线' : '同步'}`,
                        action: i.action,
                        tags: []
                    }
                    if (i.action === 'sync' && Array.isArray(i.terms)) {
                        i.terms.forEach(j => {
                            t.tags.push({
                                term: j.term,
                                ner: j.ner.join(', ')
                            })
                        })
                    }
                    this.deployList.push(t)
                })
                this.loading = false
            }
        }
    }
}
</script>

<style scoped lang="stylus">
.block
    display: block
    width: 100%
    margin-bottom: 20px
    margin-left: 0
</style>
