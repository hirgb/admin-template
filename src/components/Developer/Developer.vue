<template lang="xtmin">
div
    el-button type=primary @click=contentManager "ES内容管理"
    el-button type=primary @click=genFormJSON "生成表单json"
</template>

<script>
export default {
    methods: {
        contentManager(){
            this.$router.push({
                path: '/tools-contentmanager'
            })
        },
        genFormJSON(){
            this.$router.push({
                path: '/tools-genformjson'
            })
        }
    }
}
</script>

<style lang="css">
</style>
