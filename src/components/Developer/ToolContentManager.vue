<!--
 * @Description: In User Settings Edit
 * @Author: 张克飞
 * @Date: 2019-09-16 17:24:33
 * @LastEditTime: 2019-09-16 17:45:06
 -->
<template lang="html">
  <div class="">
          <el-form :inline="true" :model="form" class="demo-form-inline">
              <el-row>
                  <el-form-item>
                      <el-button @click="goback">返回</el-button>
                  </el-form-item>
                  <el-form-item label="IP端口">
                      <el-input v-model="form.ip"></el-input>
                  </el-form-item>
                  <el-form-item label="ESIndex">
                      <el-input v-model="form.esIndex"></el-input>
                  </el-form-item>
                  <el-form-item label="Type">
                      <el-select
                          v-model="form.type"
                          filterable
                          allow-create
                          default-first-option
                          placeholder="请选择type">
                          <el-option v-for="item in cardTypes" :key="item.value" :label="item.label" :value="item.value"> </el-option>
                      </el-select>
                  </el-form-item>
                  <el-form-item label="数据ID">
                      <el-input v-model="form.dataId"></el-input>
                  </el-form-item>
                  <el-form-item>
                      <el-button type="primary" :loading="searching" @click="submit">搜索</el-button>
                      <el-button @click="update">保存编辑</el-button>
                  </el-form-item>
            </el-row>
            <el-row>
                <el-col :span="11">
                    <el-row>
                        <el-form-item label="Query">
                        </el-form-item>
                        <ace-editor ref="query" v-model="form.query" @init="editorInit" lang="json" theme="chrome" width="100%" height="700"></ace-editor>
                        <!-- <el-input type="textarea" :rows="35" v-model="form.query"></el-input> -->
                    </el-row>
                </el-col>
                <el-col :span="11" :offset="1">
                    <el-form-item label="Result">
                    </el-form-item>
                    <ace-editor ref="result" v-model="form.result" @init="editorInit" lang="json" theme="chrome" width="100%" height="700"></ace-editor>
                    <!-- <el-input type="textarea" :rows="35" v-model="form.result"></el-input> -->
                </el-col>
            </el-row>
          </el-form>
          <el-dialog
              title="更新进度"
              :visible.sync="dialogVisible"
              width="30%">
              <el-progress :text-inside="true" :stroke-width="18" :percentage="process"></el-progress>
              <span slot="footer" class="dialog-footer">
                  <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
              </span>
              <span>失败信息：</span>
              <span v-for="i in resultArr" :key="i" v-text="i"></span>
          </el-dialog>
  </div>
</template>

<script>
import AceEditor from 'vue2-ace-editor'

export default {
    props: ['uid'],
    components: {
        AceEditor,
    },
    data() {
        return {
            dialogVisible: false,
            form: {
                ip: 'http://10.0.4.21:8407',
                esIndex: 'video_douban_20181220',
                type: 'customized',
                dataId: '',
                query: '',
                result: '',
            },
            query: '{"query":{"bool":{"must":{"term":{"details.name":"变形金刚"}}}}}',
            resultArr: [],
            process: 0,
            searching: false,
            cardTypes: [{
                value: 'customized',
                label: 'customized'
            },{
                value: 'movie',
                label: 'movie'
            }],
        }
    },
    mounted(){
        this.generateQuery()
        this.autoSearch()
    },
    methods: {
        autoSearch(){
            if (this.uid) {
                this.form.dataId = this.uid
                this.submit()
            }
        },
        goback(){
            this.$router.go(-1)
        },
        submit() {
            let ip = this.form.ip,
                esIndex = this.form.esIndex,
                type = this.form.type,
                dataId = this.form.dataId,
                query = this.form.query

            if (ip && esIndex && type && (dataId || query)) {
                let q = {
                    ip,
                    esIndex,
                    type,
                    dataId,
                    query
                }
                this.searching = true
                this.$axios.post(this.$config.dataServer + 'web/dev/search', this.$z.axios.urlEncode(q))
                .then(res => {
                    this.searching = false
                    let data = res.data
                    if (data.code) {
                        if (data.data.hits) {
                            this.form.result = JSON.stringify(data.data.hits, null, 4)
                        } else {
                            this.form.result = JSON.stringify(data.data, null, 4)
                        }
                    } else {
                        this.$utils.Patch.error(this, data.errorMsg)
                    }
                })
            } else {
                this.$utils.Patch.error(this, '搜索条件不完整')
            }
        },
        update(){
            let ip = this.form.ip,
                esIndex = this.form.esIndex,
                type = this.form.type,
                result = this.form.result

            if (ip && esIndex && type) {
                let sources = null
                try {
                    sources = JSON.parse(result)
                } catch (e) {
                    this.$utils.Patch.error(this, 'json格式错误')
                    throw new Error('json格式错误')
                }

                if (Array.isArray(sources)) {
                    this.process = 0
                    this.dialogVisible = true
                    // return
                    let n = sources.length
                    let p = 0
                    sources.forEach(i => {
                        let source = i._source
                        let id = i._id
                        // let id = source.details.uid
                        this.updateSingleItem(id, JSON.stringify(source))
                        .then(res => {
                            let data = res.data
                            if (data.code) {
                                p++
                                this.process = Math.ceil( p / n * 100)
                                // console.log(p / n * 100);
                            } else {
                                this.resultArr.push(id)
                            }
                        })
                    })
                } else if (Object.prototype.toString.call(sources) === '[object Object]') {
                    let id = this.form.dataId
                    // let id = sources.details.uid
                    this.updateSingleItem(id, JSON.stringify(sources))
                    .then(res => {
                        let data = res.data
                        if (data.code) {
                            this.$utils.Patch.success(this, '修改成功')
                        } else {
                            this.$utils.Patch.error(this, '修改失败')
                        }
                    })
                } else {
                    this.$utils.Patch.error(this, 'sources is not a object or array')
                }

            } else {
                this.$utils.Patch.error(this, '条件不完整')
            }
        },
        async updateSingleItem(id, source){
            let {ip, esIndex, type} = this.form

            let q = {
                ip,
                esIndex,
                type,
                dataId: id,
                result: source
            }

            return await this.$axios.post(this.$config.dataServer + 'web/dev/updateById', this.$z.axios.urlEncode(q))
        },
        editorInit: function () {
            require('brace/ext/language_tools')
            require('brace/mode/json')
            require('brace/theme/chrome')
        },
        generateQuery(){
            this.form.query = JSON.stringify(JSON.parse(this.query), null, 4)
        },
    }
}
</script>

<style lang="css">
</style>
