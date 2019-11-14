<template>
<el-form-item :rules="Rules" :label="item.label" :prop="item.key">
    <el-input v-if="item.type==='string'" v-bind="$attrs" :type="item.subtype" v-on="$listeners"></el-input>
    <el-input v-else-if="item.type==='number'" v-bind="$attrs" :type="item.subtype" v-on="$listeners"></el-input>
    <el-input v-else-if="item.type==='simpleArray'" v-bind="$attrs" :type="item.subtype" v-on="$listeners"></el-input>
    <span v-else>未知控件类型</span>
</el-form-item>
</template>

<script>
export default {
    props: {
        item: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            ajaxOptions: [],
        };
    },
    computed: {
        Rules() {
            const {
                item: {
                    rules
                }
            } = this;
            if (rules === undefined) return undefined;

            const R = [];

            rules.forEach(rule => {
                R.push(rule);
            })
            return R;
        },
    },
};
</script>
