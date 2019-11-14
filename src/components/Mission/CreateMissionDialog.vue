<template lang="xtmin">
el-dialog title=新建任务 :visible.sync=dialogFormVisible width=40% :close-on-click-modal=false
    el-form ref=CreateMissionForm :model=form label-width=120px :close-on-click-modal=false :rules=rules
        el-form-item label=名称 prop=name
            el-input v-model.trim=form.name
        el-form-item label=标注员 prop=marker
            el-select
            style="width: 100%"
            placeholder="请选择"
            v-model=form.marker
            :multiple=true
            :loading=markerLoading
            :filterable=true
            @visible-change=getMarkers
                el-option v-for="i in markers" :key="i.id" :label="i.nickName" :value="i.id"
        el-form-item label=测试员 prop=tester
            el-select
            style="width: 100%"
            placeholder="请选择"
            v-model=form.tester
            :multiple=true
            :loading=testerLoading
            :filterable=true
            @visible-change=getTesters
                el-option v-for="i in testers" :key="i.id" :label="i.nickName" :value="i.id"
        el-form-item label=备注
            el-input type=textarea v-model.trim=form.remark :rows=3
    div slot=footer
        el-button :disabled=loading @click="dialogFormVisible = false" "取消"
        el-button type=primary :loading=loading @click=createMission(false) "创建"
        el-button type=primary :loading=loading @click=createMission(true) "创建并添加数据"
</template>

<script>
export default {
    data() {
        let missionNameIsExist = (rule, value, callback) => {
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/Missionmanage/isMissionExist', {
                    name: value
                },
                (data) => {
                    if (data) { // data为真，则任务存在
                        callback(new Error('任务名称已存在'))
                    } else {
                        callback()
                    }
                },
                () => {
                    callback(new Error('服务器错误'))
                }
            )
        }
        return {
            dialogFormVisible: false,
            loading: false,
            form: {
                name: '',
                remark: '',
                marker: [],
                tester: [],
            },
            rules: {
                name: [{
                        required: true,
                        message: '请输入任务名称',
                        trigger: 'blur'
                    },
                    {
                        validator: missionNameIsExist,
                        trigger: 'blur'
                    }
                ],
                marker: [{
                    required: true,
                    message: '请选择标注员',
                    trigger: 'blur'
                }],
                tester: [{
                    required: true,
                    message: '请输入测试员',
                    trigger: 'blur'
                }],
            },
            markers: [],
            testers: [],
            markerLoading: false,
            testerLoading: false,
        }
    },
    methods: {
        show() {
            this.dialogFormVisible = true
            this.$nextTick(() => {
                this.$refs.CreateMissionForm.resetFields()
                this.form.name = '内容管理-' + this.$z.timeFormat('yyyy-MM-dd')
            })
        },
        createMission(addData = false) {
            this.$refs.CreateMissionForm.validate(isSuccess => {
                if (isSuccess) {
                    this.$utils.post(
                        this,
                        this.$config.dataServer + 'web/Missionmanage/createMission', //与数据库操作
                        {
                            name: this.form.name,
                            marker: this.genWorkerStr(this.form.marker),
                            tester: this.genWorkerStr(this.form.tester),
                            status: 0,
                            remark: this.form.remark,
                        },
                        (data) => {
                            this.dialogFormVisible = false
                            this.$utils.Patch.success(this, '创建成功')
                            if (addData) {
                                this.$emit('create-success', {
                                    act: 'addData',
                                    missionId: data
                                })
                            } else {
                                this.$emit('create-success')
                            }
                        },
                        null,
                        () => {
                            this.loading = false
                        }
                    )
                }
            })
        },
        getMarkers() {
            if (this.markers.length > 0) {
                return
            }
            this.markerLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/user/getUsersOfRole', {
                    role: 'marker'
                },
                (data) => {
                    this.markers = data
                },
                null,
                () => {
                    this.markerLoading = false
                }
            )
        },
        getTesters() {
            if (this.testers.length > 0) {
                return
            }
            this.testerLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/user/getUsersOfRole', {
                    role: 'tester'
                },
                (data) => {
                    this.testers = data
                },
                null,
                () => {
                    this.testerLoading = false
                }
            )
        },
        genWorkerStr(worker) {
            if (Array.isArray(worker)) {
                let t = worker.join(',')
                return ',' + t + ','
            } else {
                return ''
            }
        }
    }
}
</script>

<style lang="css">
</style>
