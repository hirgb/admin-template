<!--
 * @Description: In User Settings Edit
 * @Author:zhangkefei
 * @Date: 2019-08-09 17:14:40
 * @LastEditTime: 2019-08-09 18:11:39
 * @LastEditors: Please set LastEditors
 -->
<template lang="xtmin">
div .ner-editor v-show="visible" :style="nerPosition" @click.stop=""
    el-row
        div .title "编辑NER"
    el-row
        el-button "删除NER" v-show="delBtnVisible" @click="$emit('delete-ner')"
    el-row style="margin-top: 10px"
        el-select v-model="value" :filterable=true placeholder="请选择"
        :allow-create=true
        :default-first-option=true
        @change="nerChange"
            el-option
            v-for="item in ners"
            :key="item.value"
            :label="item.label"
            :value="item.value"
</template>

<!-- el-button style="float: right" type=text @click="hidden" "关闭" -->
<script>
export default {
    data() {
        return {
            visible: false,
            delBtnVisible: false,
            nerPosition: {
                left: 0,
                top: 0,
            },
            value: '',
            ners: [
                {
                    value: 'FILM',
                    label: 'FILM'
                },
                {
                    value: 'BOOK',
                    label: 'BOOK'
                },
            ],
        }
    },
    mounted(){
            this.getNers();
    },
    methods: {
        show(x, y, delBtnVisible = false){
            this.nerPosition.left = x + 'px'
            this.nerPosition.top = y + 'px'
            this.value = ''
            this.delBtnVisible = delBtnVisible
            this.visible = true
        },
        hidden(){
            this.nerPosition.left = 0
            this.nerPosition.top = 0
            this.visible = false
        },
        nerChange(v){
            this.$emit('change-or-add-ner', v)
        },
        getNers() {
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/term/getAllNers',
                null,
                (data) => {
                    this.ners = data.map(i => {
                        return {
                            label: i.ner,
                            value: i.ner,
                        }
                    })
                }
            )}
    }
}
</script>

<style scoped lang="stylus">
.ner-editor
    position: fixed
    z-index: 1000
    background-color: white
    min-width: 180px
    min-height: 50px
    border: 1px solid #f0f0f0
    border-radius: 3px
    padding: 5px
    box-shadow: 2px 2px 5px #66666630
.title
    height: 30px
    font-size: 13px
    color: #666
    line-height: 20px
</style>
