<template lang="html">
    <el-dialog title="添加活动" :visible.sync="dialogFormVisible" width="30%">
          <el-form ref="addActivityForm" label-width="80px" :model="form" :rules="rules">
            <el-form-item prop="name" label="活动名称">
              <el-input v-model="form.name"></el-input>
            </el-form-item>
            <el-form-item prop="channels" label="渠道选择">
                <el-select
                    v-model="form.channels"
                    multiple
                    filterable
                    allow-create
                    default-first-option
                    placeholder="请选择" style="width: 100%">
                    <el-option
                        v-for="item in channels"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value">
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item prop="startTime" label="开始时间">
                <el-date-picker v-model="form.startTime" type="datetime" placeholder="选择开始时间" style="width: 100%"> </el-date-picker>
            </el-form-item>
            <el-form-item prop="endTime" label="结束时间">
                <el-date-picker v-model="form.endTime" type="datetime" placeholder="选择结束时间" style="width: 100%"> </el-date-picker>
            </el-form-item>
            <el-form-item prop="remark" label="备注">
                <el-input type="textarea" v-model="form.remark"></el-input>
            </el-form-item>
          </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button type="primary" @click="addActivity">确 定</el-button>
      </div>
    </el-dialog>
</template>

<script>

export default {
    data() {
        return {
            dialogFormVisible: false,
            formLabelWidth: '80',
            form: {
                name: '',
                channels: '',
                startTime: null,
                endTime: null,
                // cardType: null,
                remark: '',
            },
            rules: {
                name: [{
                    required: true,
                    trigger: 'blur'
                }],
                channels: [{
                    required: true,
                    trigger: 'blur'
                }],
                startTime: [{
                    type: 'date',
                    required: true,
                    trigger: 'blur'
                }],
                endTime: [{
                    type: 'date',
                    required: true,
                    trigger: 'blur'
                }],
                // cardType: [{
                //     required: true,
                //     trigger: 'blur'
                // }],
            },
            channels: [{
                value: 'oppo',
                label: 'oppo'
            }, {
                value: 'vivo',
                label: 'vivo'
            }],
            cardTypes: [{
                value: 'customized',
                label: 'customized'
            }],
        }
    },
    methods: {
        show() {
            this.dialogFormVisible = true
            window.setTimeout(()=>{
                this.$refs.addActivityForm.clearValidate()
            }, 0)
        },
        addActivity() {
            this.$refs.addActivityForm.validate((isSuccess) => {
                if (isSuccess) {
                    let {
                        name,
                        channels,
                        startTime,
                        endTime,
                        remark,
                        // cardType
                    } = this.form
                    if (name && channels && startTime && endTime) {
                        channels = channels.join(',')
                        startTime = this.$z.timeFormat('yyyy-MM-dd hh:mm:ss', 0, startTime.getTime())
                        endTime = this.$z.timeFormat('yyyy-MM-dd hh:mm:ss', 0, endTime.getTime())

                        let q = {
                            name,
                            channels,
                            startTime,
                            endTime,
                            remark,
                            // cardType
                        }
                        this.$axios.post(this.$config.dataServer + '/web/activity/addActivity', this.$z.axios.urlEncode(q))
                        .then(res => {
                            let data = res.data
                            // console.log(data);
                            if (data.code) {
                                this.$utils.Patch.success(this, '添加成功')
                                this.dialogFormVisible = false
                                this.$emit('add-success')
                            } else {
                                this.$utils.Patch.error(this, data.errorMsg)
                            }
                        })
                        .catch(err => {
                            throw err
                        })
                    }
                }
            })
        },
    }
}
</script>

<style lang="css">
</style>
