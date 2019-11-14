<template lang="html">
<div class="">
    <div v-if="!requestData.length">
        无用户请求
    </div>
    <div class="user-request" v-for="i in requestData" :key="i.content">
        <div class="content" v-html="toLong(i.content, i.extend)"></div>
        <div class="opt">
            <span class="tip"><i class="el-icon-chat-round"></i> {{i.pv}} 次请求</span>
            <span class="btn" v-if="test" icon="el-icon-s-promotion" @click="$emit('test', i.content)">测试</span>
            <span class="btn" v-show="i.content.length > 100" @click="i.extend = !i.extend">{{!i.extend ? '展开' : '收起'}}</span>
        </div>
    </div>
</div>
</template>

<script>
export default {
    props: ['test'],
    data() {
        return {
            requestData: [],
            word: ''
        }
    },
    filters: {
        toLong: function(v, extend, word) {
            if (extend) {
                return v.replace(word, `<i>${word}</i>`)
            } else {
                return v.length > 100 ? v.slice(0, 100).replace(word, `<i>${word}</i>`) + '...' : v.replace(word, `<i>${word}</i>`)
            }
        }
    },
    methods: {
        getUserRequest(word) {
            this.word = word
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/term/getUserRequest', {
                    word
                },
                (data) => {
                    let requests = data.map(i => {
                        return {
                            content: i.query_raw,
                            pv: i.pv,
                            extend: false
                        }
                    })
                    this.requestData = requests
                }
            )
        },
        reset() {
            this.requestData = []
        },
        toLong(v, extend) {
            let word = this.word
            if (extend) {
                return v.replace(word, `<i>${word}</i>`)
            } else {
                return v.length > 100 ? v.slice(0, 100).replace(word, `<i>${word}</i>`) + '...' : v.replace(word, `<i>${word}</i>`)
            }
        }
    }
}
</script>

<style lang="stylus">
.user-request
    padding-top: 10px
    padding-bottom: 10px
    font-size: 12px
    border-bottom: 1px solid #ddd
    .content
        i
            color: red
            font-weight: bold
            font-style: normal
    .opt
        .btn
            color: #409EFF
            cursor: pointer
            margin-right: 10px
        .tip
            color: #666
            padding-right: 10px
</style>
