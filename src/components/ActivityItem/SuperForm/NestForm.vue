<template>
<div>
    <dynamic-form-item v-for="i in formConfig" :key="i" :item="i"></dynamic-form-item>
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
    },
    components: {
        DynamicFormItem,
    },
    data() {
        return {
            esData: {}
        };
    },
    methods: {
    }
}
</script>

<style lang="stylus">
</style>
