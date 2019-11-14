<template lang="xtmin">
el-dialog title="新建活动项"
:close-on-click-modal=false
:close-on-press-escape=false
:visible.sync=dialogVisible
:fullscreen=true
    select-card-dialog ref=selectCardDialog :cards=resources @select=onSelectCard($event)
    el-row :gutter=20 :style="'height:'+contentHeight+'px'"
        el-col :span=8 style="height: 100%; border-right: 1px solid #00000020"
            el-tabs :value="'card'"
                el-tab-pane label=卡片展现 name=card
                    div .card-warper :style="'height:'+contentHeight+'px'"
                        el-card .card
                            div slot=header
                                span "卡片"
                            div "content"
                el-tab-pane label=识别测试 name=test
        el-col :span=16 style="height: 100%; overflow-y: auto"
            el-row
                el-form :model=form label-width=120px
                    el-form-item label=内容名称
                        el-input .width-300 v-model=form.name
                    el-form-item label=内容类型
                        el-select .width-300 v-model=form.category @change=categoryChange placeholder=请选择
                            el-option v-for="i in categorys" :key=i.value :label=i.label :value=i.value
                        el-button type=text @click=loadResource "加载资源"
                    el-form-item label=内容策略
                        el-radio-group v-model=form.strategy
                            el-radio label=recognise "识别"
                            el-radio label=monopolyRecognise "独享识别"
                    el-form-item label=识别情况
                        el-input .width-300 :disabled=true :value=form.recogniseResult
                    el-form-item label=干预项
                        el-input .width-300 placeholder=多个干预项用英文逗号分隔 v-model=form.intervene
                    el-form-item label=资源数量
                        span "匹配到 {{ resources.length }} 个资源 "
                        el-button type=text @click=choiceResource "选择资源"
            el-row
                tree-form ref=treeForm :form-config="formConfig" v-model=esData
    span slot="footer"
        el-button @click="dialogVisible = false" "取 消"
        el-button type="success" :loading="loading" :disabled="loading" @click="autoComplate" "尝试自动补全"
        el-button type="primary" :loading="loading" :disabled="loading" @click="submit('MODIFY')" "新建并绑定"
        el-button type="primary" :loading="loading" :disabled="loading" @click="submit('ADD')" "更新并绑定"
</template>

<script>
import SelectCardDialog from './SelectCardDialog'
import TreeForm from './TreeForm/TreeForm'
import {
    video
} from '@/assets/js/form-json.js'

export default {
    props: ['type', 'actName'],
    components: {
        TreeForm,
        SelectCardDialog,
    },
    data() {
        return {
            loading: false,
            contentHeight: window.innerHeight - 196,
            dialogVisible: false,
            form: {
                name: '纽约巨人',
                category: '',
                strategy: 'recognise',
                recogniseResult: '无',
                intervene: '',
            },
            esData: {},
            formConfig: [],
            categorys: [{
                label: '影视',
                value: 'VIDEO'
            }],
            resources: [],
            originData: null,
        }
    },
    methods: {
        show() {
            this.dialogVisible = true
            this.originData = null
        },
        submit(action) {
            let formConfig = this.formConfig,
                finalData = {}
            formConfig.forEach(i => {
                if (i.item) {
                    this.getValueFromItem(i, finalData)
                } else if (i.children) {
                    this.getValueFromArray(i, finalData)
                }
            })
            if (this.originData) {
                finalData = { ...this.originData,
                    ...finalData
                }
            }
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/ActivityItem/notify', {
                    data: JSON.stringify(finalData),
                    action,
                    category: this.form.category
                },
                () => {
                    this.$utils.Patch.success(this, '操作成功')
                }
            )
        },
        getValueFromItem(i, data) {
            let v, key, esData = this.esData

            if (esData[i.item.key] != undefined) {
                if (i.item.type === 'string') {
                    v = esData[i.item.key] + ''
                } else if (i.item.type === 'number') {
                    v = +esData[i.item.key]
                }
            } else if (i.item.type === 'string') {
                v = ''
            } else if (i.item.type === 'number') {
                v = 0
            }

            if (/^\d+$/.test(i.item.field)) {
                key = +i.item.field
                // data.push(v)
            } else {
                key = i.item.field
            }
            data[key] = v
        },
        getValueFromArray(i, data) {
            //判断 i 是Array还是Object
            let field = i.field
            let type
            if (i.children[0].children) {
                if (/^\d+$/.test(i.children[0].field)) {
                    type = 'array'
                } else {
                    type = 'object'
                }
            } else if (i.children[0].item) {
                if (/^\d+$/.test(i.children[0].item.field)) {
                    type = 'array'
                } else {
                    type = 'object'
                }
            }
            let result
            if (type === 'array') {
                result = []
                i.children.forEach(i => {
                    if (i.item) {
                        this.getValueFromItem(i, result)
                    } else if (i.children) {
                        this.getValueFromArray(i, result)
                    }
                })
            } else if (type === 'object') {
                result = Object.create(null)
                i.children.forEach(i => {
                    if (i.item) {
                        this.getValueFromItem(i, result)
                    } else if (i.children) {
                        this.getValueFromArray(i, result)
                    }
                })
            }
            data[field] = result
        },
        loadResource() {
            // get data from content
            let c = {
                name: this.form.name,
                category: this.form.category,
            }
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/ActivityItem/getDataFromContent',
                c,
                (data) => {
                    this.resources = data.infos[0].datas
                }
            )
            // get data from recognise
            let r = {
                word: this.form.name,
                ner: this.form.category,
            }
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/term/getTermOfNer',
                r,
                (data) => {
                    this.form.recogniseResult = data.total === 1 ? '有' : '无'
                }
            )
        },
        choiceResource() {
            if (this.resources.length) {
                this.$refs.selectCardDialog.show()
            }
        },
        categoryChange(v) {
            if (v === 'VIDEO') {
                this.formConfig = video
                this.$refs.treeForm.setDefaultValue()
            }

            this.loadResource()
        },
        onSelectCard(e) {
            this.originData = e
            let tpl = this.formConfig
            tpl.forEach(i => {
                if (i.item) {
                    this.assignItem(i, e)
                } else if (i.children) {
                    this.assignArray(i, e)
                }
            })
            this.$refs.treeForm.setDefaultValue()
        },
        assignArray(obj, e, guid = true) {
            let field = obj.field
            let value = e[field]
            let vType = this.$utils.Tools.whatType(value)
            if (!['array', 'object'].includes(vType)) {
                this.$utils.Patch.error(this, `${field}字段类型不匹配`)
                throw new Error(`${field}字段类型不匹配`)
            }
            if (obj.children[0].item) { //如果item存在，说明obj是个简单数组
                if (vType === 'array') {
                    let length = value.length
                    if (length > 0) {
                        obj.children[0].item.value = value[0]
                        for (let i = 1; i < length; i++) {
                            let t = JSON.parse(JSON.stringify(obj.children[0]))
                            t.label = i
                            t.item.label = i + ''
                            t.item.field = i + ''
                            t.item.value = value[i]
                            t.item.key = this.$utils.Tools.guid()
                            obj.children.push(t)
                        }
                    }
                } else if (vType === 'object') {
                    for (let [k, v] of Object.entries(value)) {
                        let t = obj.children.find(i => i.item.field === k)
                        t.item.value = v
                        if (guid) {
                            t.item.key = this.$utils.Tools.guid()
                        }
                    }
                }
            } else if (obj.children[0].children) {
                value.forEach((i, index, arr) => {
                    if (index === 0) {
                        this.assignArray(obj.children[0], arr, false)
                    } else {
                        let children = JSON.parse(JSON.stringify(obj.children[0]))
                        children.label = index + ''
                        children.field = index + ''
                        obj.children[index] = children
                        this.assignArray(obj.children[index], arr)
                    }
                })
            }
        },
        assignItem(obj, e) {
            let field = obj.item.field,
                type = obj.item.type
            if (['string', 'number'].includes(type)) {
                obj.item.value = e[field]
            }
        },
        autoComplate() {
            let doubanIdItem = this.formConfig.find(i => {
                if (i.item && i.item.field === 'DoubanId') {
                    return i
                }
            })
            if (doubanIdItem) {
                let doubanId = this.esData[doubanIdItem.item.key]
                if (doubanId) {
                    let data = {
                        DoubanId: "21965529",
                        category: ["movie"]
                    }
                    this.loading = true
                    this.$utils.post(
                        this,
                        this.$config.dataServer + 'web/ActivityItem/notify', {
                            data: JSON.stringify(data),
                            action: 'COMPLETION',
                            category: this.form.category
                        },
                        () => {
                            this.$utils.Patch.success(this, '操作成功')
                        },
                        null,
                        ()=>{
                            this.loading = false
                        }
                    )
                } else {
                    this.$utils.Patch.error(this, 'doubanId is null')
                }
            } else {
                this.$utils.Patch.error(this, 'can not find the doubanId item.')
            }
        }
    }
}
</script>
<style lang="stylus">
.super-form
    border-radius: 12px
    border: 1px solid #00000020
    overflow: hidden
    .el-tree-node__content
        height: 40px
    .el-form-item
        margin-bottom: 0
</style>
<style scoped lang="stylus">
.card-warper
    background-color: #fff
    position: relative
    .card
        width: 70%
        margin: 50px auto
.width-300
    width: 300px
</style>
