<template lang="xtmin">
el-dialog title=选择卡片资源
:visible.sync=dialogVisible
:append-to-body=true
width=30%
    el-table :data=cards
        el-table-column prop=name label=名称
        el-table-column label=操作 width=100 fixed=right
            template slot-scope=scope
                el-button type=text @click=select(scope.row) "选择"
                el-button type=text @click=deleteItem(scope.row) "删除"

</template>

<script>
export default {
    props: ['cards'],
    data(){
        return {
            dialogVisible: false,
        }
    },
    methods: {
        show(){
            this.dialogVisible = true
        },
        select(row){
            this.$emit('select', row)
            this.dialogVisible = false
        },
        deleteItem(row){
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/ActivityItem/notify', {
                    data: JSON.stringify(row),
                    category: 'VIDEO',
                    action: 'DEL'
                },
                () => {
                    this.$utils.Patch.success(this, '操作成功')
                }
            )
        }
    }
}
</script>

<style lang="stylus">
</style>
