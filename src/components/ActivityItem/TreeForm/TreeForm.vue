<template>
<div class="super-form">
    <el-form inline-message :model="value" v-bind="formConfig" label-width="80px">
        <el-tree :data="formConfig" :props="defaultProps">
            <div slot-scope="{node, data}">
                <span v-if="!data.item">{{ node.label }}</span>
                <dynamic-form-item
                v-if="!!data.item"
                :item="data.item"
                v-bind="data.item"
                :value="value[data.item.key]"
                @input="handleInput($event, data.item.key)"></dynamic-form-item>
            </div>
        </el-tree>
    </el-form>
</div>
</template>
<script>
import DynamicFormItem from './Item'

export default {
    props: {
        formConfig: {
            type: Array,
            required: true,
        },
        value: {
            type: Object,
            required: true,
        },
        labelWidth: {
            type: String,
        },
    },
    components: {
        DynamicFormItem,
    },
    data() {
        return {
            defaultProps: {
                children: 'children',
                label: 'label'
            }
        };
    },
    mounted() {
        this.setDefaultValue();
    },
    methods: {
        handleInput(val, key) {
            // 这里element-ui没有上报event，直接就是value了
            // console.log(val, key);
            this.$emit('input', { ...this.value,
                [key]: val
            });
        },
        setDefaultValue() {
            const formData = { ...this.value}
            // console.log(this.formConfig);
            // 设置默认值
            this.formConfig.forEach(i=> {
                if (i.item) {
                    this.assignItem(i, formData)
                } else if (i.children) {
                    this.assignArray(i, formData)
                }
            });
            this.$emit('input', { ...formData })
        },
        assignItem(i, formData){
            formData[i.item.key] = i.item.value
        },
        assignArray(i, formData){
            i.children.forEach(i => {
                if (i.item) {
                    this.assignItem(i, formData)
                } else if (i.children) {
                    this.assignArray(i, formData)
                }
            })
        },
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
