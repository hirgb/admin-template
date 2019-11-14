<!--
 * @Description: 显示卡片资源
 * @Author: 张克飞
 * @Date: 2019-09-16 17:24:33
 * @LastEditTime: 2019-09-16 18:10:11
 -->
<template lang="html">
<div class="">
    <div class="card" v-if="!cards.length">
        无卡片资源
    </div>
    <div class="card" v-for="i in cards" :key="i.token">
        <span>{{ i.token }}</span>
        <span>{{ i.ner }}</span>
    </div>
</div>
</template>

<script>
export default {
    data(){
        return {
            cards: []
        }
    },
    methods: {
        getResource(word, ner) {
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/term/getCard', {
                    word,
                    ner
                },
                (data) => {
                    if (data &&
                        data.result_list &&
                        data.result_list[0] &&
                        data.result_list[0].term_list) {
                        this.cards = data.result_list[0].term_list
                    }
                }
            )
        },
    }
}
</script>

<style lang="css" scoped>
</style>
