<template lang="xtmin">
div style="height: 100%; width: 100%;"
    el-row style="height: 40px;"
        el-col .data :span=8 "测试项：{{term.word}}"
        el-col .data :span=16 "NER类别：{{joinNer(term.ners)}}"
    el-row style="height: 40px;"
        el-button type=success :disabled="[1,2,5].includes(term.status)" @click=pass "通过"
        el-button type=danger :disabled="[1,2,5].includes(term.status)" @click=reject "驳回"
    el-row :gutter=10 style="height: calc(100% - 80px)"
        el-col :span=14 style="height: 100%"
            my-chat :option="{}" ref="chatBox" @send-msg=onNewOwnMessage
        el-col :span=10 style="height: 100%" :style="{overflowY: 'auto'}"
            request ref="request" v-show="testHelper === 'request'"
            :test=true
            @test=onTest
            :style="{borderTop: '1px solid #f0f0f0', borderBottom: '1px solid #f0f0f0', height: 'calc(100% - 5px)'}"
            reject ref="reject" :missionId=missionId :word=term.word
            v-show="testHelper === 'reject'"
            @back="testHelper = 'request'"
            @success=onRejectSuccess
</template>

<script>
import Request from './Request.vue'
import Reject from './Reject.vue'
import MyChat from '@/components/Common/Chat/Chat.vue'

export default {
    props: ['term', 'missionId'],
    components: {
        Request,
        Reject,
        MyChat
    },
    data() {
        return {
            testHelper: 'request'
        }
    },
    watch: {
        term: function(newVal) {
            if (newVal.word) {
                this.$refs.chatBox.clearMsg()
                this.$refs.chatBox.addMsgFromMe(newVal.word)
                this.$refs.request.getUserRequest(newVal.word)
            }
        }
    },
    methods: {
        pass() {
            this.$emit('pass')
            this.$refs.reject.reset()
            this.$refs.request.reset()
            this.$refs.chatBox.reset()
        },
        reject() {
            this.testHelper = 'reject'
            this.$refs.reject.reset()
        },
        editNer() {
            this.$emit('edit-ner', this.term.word)
        },
        onTest(query) {
            this.$refs.chatBox.addMsgFromMe(query)
        },
        onRejectSuccess() {
            this.testHelper = 'request'
            this.$refs.reject.reset()
            this.$refs.request.reset()
            this.$refs.chatBox.reset()
            this.$emit('reject-success')
        },
        onNewOwnMessage(query) {
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/term/segment', {
                    query
                },
                (data) => {
                    let reply = ''
                    if (data.length) {
                        data.forEach(i => {
                            if (i.ner === 'O') {
                                reply += i.token
                            } else {
                                if (i.token === this.term.word) {
                                    reply += ` <span class="term active">${i.token}</span><span class="ner">#${i.ner}</span> `
                                } else {
                                    reply += ` <span class="term">${i.token}</span><span class="ner">#${i.ner}</span> `
                                }
                            }
                        })
                    } else {
                        reply = '没有回答'
                    }
                    this.$refs.chatBox.addMsgFromOther(reply)
                }
            )
        },
        joinNer(ners) {
            if (Array.isArray(ners)) {
                return ners.map(i => i.ner).join(',')
            } else {
                return ''
            }
        }
    }
}
</script>

<style scoped lang="stylus">
.dialogBox
    height: calc(100% - 20px)
    border-radius: 4px
    border-color: #EBEEF5
    border-style: solid
    border-width: 1px
.data
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
</style>
