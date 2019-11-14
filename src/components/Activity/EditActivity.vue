<template lang="html">
    <el-dialog title="编辑活动" :visible.sync="dialogFormVisible" width="30%">
          <el-form label-width="80px" :model="form" :rules="rules">
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
                placeholder="请选择"
                style="width: 100%">
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
            <el-form-item prop="cardType" label="卡片类型">
                <el-select v-model="form.cardType" placeholder="请选择" style="width: 100%">
                  <el-option v-for="item in cardTypes" :key="item.value" :label="item.label" :value="item.value"> </el-option>
                </el-select>
            </el-form-item>
          </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button type="primary" @click="update">确 定</el-button>
      </div>
    </el-dialog>
</template>

<script>

export default {
    props: ['detail'],
    data() {
        return {
            dialogFormVisible: false,
            formLabelWidth: '80',
            form: {
                id: '',
                name: '',
                channels: [],
                startTime: null,
                endTime: null,
                cardType: null,
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
                cardType: [{
                    required: true,
                    trigger: 'blur'
                }],
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
    mounted(){
        // console.log(this.detail);
    },
    methods: {
        show() {
            this.form.id = this.detail.id
            this.form.name = this.detail.name
            this.form.channels = this.detail.channels.split(',')
            this.form.startTime = this.detail.startTime
            this.form.endTime = this.detail.endTime
            this.form.cardType = this.detail.cardType
            this.dialogFormVisible = true
        },
        update() {
            let {id, name, channels, startTime, endTime, cardType} = this.form
            if (id && name && channels && startTime && endTime && cardType) {
                channels = channels.join(',')
                startTime = (typeof startTime === 'string') ? startTime : this.$z.timeFormat('yyyy-MM-dd hh:mm:ss', 0, startTime.getTime())
                endTime = (typeof endTime === 'string') ? endTime : this.$z.timeFormat('yyyy-MM-dd hh:mm:ss', 0, endTime.getTime())

                let q = {id, name, channels, startTime, endTime, cardType}
                this.$axios.post(this.$config.dataServer+'/web/activity/editActivity', this.$z.axios.urlEncode(q))
                .then(res => {
                    let data = res.data
                    if (data.code) {
                        this.$utils.Patch.success(this, '修改成功')
                        this.dialogFormVisible = false
                        this.$emit('edit-success')
                    } else {
                        this.$utils.Patch.error(this, data.errorMsg)
                    }
                })
                .catch(err => {
                    throw err
                })
            } else {
                this.$utils.Patch.error(this, '数据不完整')
            }
        }
    }
}
</script>

<style lang="css">
</style>
