<template>
<div class="super-form">
    <el-form inline-message :model="value" v-bind="formConfig" :label-width="labelWidth">
        <dynamic-form-item v-for="i in formConfig" :key="i.key" :item="i" :value="i.value" @input="handleInput($event, data.key)"></dynamic-form-item>
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
            // 设置默认值
            this.formConfig.jsonData.forEach(item => {
                const {
                    key,
                    value
                } = item
                if (!formData[key]) {
                    formData[key] = value;
                }
            });
            this.$emit('input', { ...formData })
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
