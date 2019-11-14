<template lang="html">
  <div class="chat-box">
      <div class="msg-list" :style="msgListHeight">
          <div :class="i.role === 'me' ? 'msg-me' : 'msg-other'" v-for="i in msgList" :key="i.key">
              <p v-html="i.msg"></p>
          </div>
      </div>
      <div class="msg-box" :style="msgBoxHeight">
          <el-input type="textarea" v-model="msg" :rows="3" resize="none" @keyup.enter.native="send"></el-input>
      </div>
  </div>
</template>

<script>
export default {
    props: {
        option: {
            type: Object,
            required: true,
        }
    },
    data() {
        return {
            msg: '',
            msgList: [],
        }
    },
    computed: {
        msgBoxHeight: function() {
            return {
                height: this.option.msgBoxHeight ? this.option.msgBoxHeight + 'px' : '69px'
            }
        },
        msgListHeight: function() {
            return {
                height: this.option.msgBoxHeight ? `calc(100% - ${this.option.msgBoxHeight}px)` : 'calc(100% - 69px)'
            }
        },
    },
    methods: {
        send() {
            this.addMsgFromMe(this.msg.trim())
            this.msg = ''
        },
        addMsgFromMe(msg) {
            if (!msg) {
                return
            }
            this.msgList.push({
                msg,
                role: 'me',
                key: Date.now()
            })
            this.scrollToBottom()
            this.$emit('send-msg', msg)
        },
        addMsgFromOther(msg) {
            if (!msg) {
                return
            }
            this.msgList.push({
                msg,
                role: 'other',
                key: Date.now()
            })
            this.scrollToBottom()
        },
        clearMsg() {
            this.msgList = []
        },
        scrollToBottom() {
            setTimeout(() => {
                let ele = this.$el.querySelector('.msg-list')
                ele.scrollTop = ele.scrollHeight
            })
        },
        reset() {
            this.clearMsg()
            this.msg = ''
        },
    },
}
</script>

<style lang="stylus" scoped>
@import "../../../assets/css/veriable.styl";
.chat-box
    box-sizing: border-box
    background-color: #fff
    height: 100%
    .msg-list
        min-height: 300px
        width: 100%
        box-sizing: border-box
        padding: 10px
        overflow-x: hidden
        overflow-y: auto
        border: 1px solid $mainBgColor
        border-radius: 5px
    .msg-box
        width: 100%
</style>
<style lang="stylus">
.msg-me
    padding: 5px 0
    display: flex
    justify-content: flex-end
    p
        font-size: 12px
        line-height: 20px
        min-height: 20px
        padding: 10px
        border-radius: 3px
        background-color: #f0f0f0
        display: inline-block
        max-width: 70%
.msg-other
    padding: 5px 0
    display: flex
    justify-content: flex-start
    p
        font-size: 12px
        line-height: 20px
        min-height: 20px
        padding: 10px
        border-radius: 3px
        background-color: #f0f0f0
        display: inline-block
        max-width: 70%
        .ner
            color: #8282ff
            font-size: 10px
        .active
            color: #ff7373 !important
        .term
            color: #d0b46d
            font-weight: 700
</style>
