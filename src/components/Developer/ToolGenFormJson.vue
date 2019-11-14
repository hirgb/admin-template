<!--
 * @Description: In User Settings Edit
 * @Author: your name
 * @Date: 2019-09-16 17:24:33
 * @LastEditTime: 2019-09-16 17:47:23
 -->
<template lang="xtmin">
el-row :gutter=20
    el-col :span=12
        ace-editor ref="input" v-model="input" @init="editorInit" lang="json" theme="chrome" width="100%" height="700"
    el-col :span=12
        ace-editor ref="output" v-model="output" @init="editorInit" lang="json" theme="chrome" width="100%" height="700"
    el-button type=primary @click=gen "生成表单json"
</template>

<script>
import AceEditor from 'vue2-ace-editor'

export default {
    components: {
        AceEditor,
    },
    data() {
        return {
            input: '',
            output: ''
        }
    },
    mounted() {
        this.initInput()
    },
    methods: {
        editorInit: function() {
            require('brace/ext/language_tools')
            require('brace/mode/json')
            require('brace/theme/chrome')
        },
        initInput() {
            let obj = {
                name: "巴黎圣母院大火",
                desc: "巴黎当地时间2019年4月15日下午6:30(北京时间16日00:30),法国巴黎圣母院大教堂(Notre-Dame Cathedral)发生重大火灾,浓烟弥漫整个巴黎上空。400名消防员赶到现场抢",
                url: "http://content.sanjiaoshou.net/mobile/news/index.html?uid=12345",
                id: "12345",
                pic: "https://5b0988e595225.cdn.sohucs.com/q_70,c_lfill,w_228,h_148,g_faces/images/20190416/9bb6463d63f547bd8ba626b8bb771175.jpeg",
                sub_content: [{
                        name: "最新情况",
                        type: "news_list",
                        keywrod: "",
                        news_list: [{
                                datetime: "2019-04-16 14:12:20",
                                id: "01da8cb7db709891bf21f6252d967507",
                                img_url: "https://5b0988e595225.cdn.sohucs.com/q_70,c_lfill,w_228,h_148,g_faces/images/20190416/c2e7482c86d246beb9e1f6361b91ffd2.jpeg",
                                source: "世界旅行清单",
                                timetamp: 1555395140,
                                title: "巴黎圣母院：拿破仑加冕的地方，全世界最著名的教堂",
                                url: "https://www.sohu.com/a/308252788_475369",
                                wap_url: "https://m.sohu.com/a/308252788_475369"
                            },
                            {
                                datetime: "2019-04-16 06:07:00",
                                id: "4bd177eb70c90a7480c812597cf804da",
                                img_url: "http://cms-bucket.ws.126.net/2019/04/16/d67245c48f1e4ad09e411e2187b44e46.png",
                                source: "中国新闻网",
                                timetamp: 1555366020,
                                title: "巴黎圣母院遭遇大火受损严重 马克龙：将重建",
                                url: "http://money.163.com/19/0416/06/ECS5L8I6002580S6.html",
                                wap_url: "http://3g.163.com/money/19/0416/06/ECS5L8I6002580S6.html"
                            },
                            {
                                datetime: "2019-04-16 11:36:22",
                                id: "5ab14fda447358c8a871609523310a92",
                                img_url: "https://5b0988e595225.cdn.sohucs.com/q_70,c_lfill,w_228,h_148,g_faces/images/20190416/8d6dfe67c5b5474aa2b7db5624859e01.jpeg",
                                source: "澎湃新闻",
                                timetamp: 1555385782,
                                title: "一图看懂巴黎圣母院大火，标志性尖塔一小时内坍塌",
                                url: "https://www.sohu.com/a/308225582_260616",
                                wap_url: "https://m.sohu.com/a/308225582_260616"
                            },
                            {
                                datetime: "2019-04-16 08:14:49",
                                id: "847662cd3b78ba8f364a818f8a56e6a6",
                                img_url: "https://5b0988e595225.cdn.sohucs.com/q_70,c_lfill,w_228,h_148,g_faces/images/20190416/108f64e33e204ad09b34ee1ab1d09300.jpeg",
                                source: "新京报",
                                timetamp: 1555373689,
                                title: "850年巴黎圣母院突遭大火，塔顶坍塌但主体保留",
                                url: "https://www.sohu.com/a/308180923_114988",
                                wap_url: "https://m.sohu.com/a/308180923_114988"
                            }
                        ]
                    }
                ]
            }
            this.input = JSON.stringify(obj, null, '\t')
        },
        gen() {
            try {
                let obj = JSON.parse(this.input)
                let whatType = this.$utils.Tools.whatType
                if (whatType(obj) === 'object') {
                    let output = this.inToOut(obj)
                    this.output = JSON.stringify(output, null, '\t')
                } else {
                    this.$utils.Patch.error(this, '源数据应该是一个对象类型')
                }
            } catch (e) {
                this.$utils.Patch.error(this, e)
            }
        },
        inToOut(obj) {
            let outputArr = []
            let whatType = this.$utils.Tools.whatType
            let guid = this.$utils.Tools.guid
            for (let [k, v] of Object.entries(obj)) {
                let type = whatType(v),
                    t
                if (type === 'string') {
                    t = {
                        label: k,
                        item: {
                            type,
                            subtype: 'text',
                            label: k,
                            field: k,
                            value: '',
                            key: guid(),
                        }
                    }
                } else if (type === 'number') {
                    t = {
                        label: k,
                        item: {
                            type,
                            label: k,
                            field: k,
                            value: '',
                            key: guid(),
                        }
                    }
                } else if (type === 'array') {
                    let a1 = v[0]
                    let type = whatType(a1),
                        tt
                    if (type === 'string') {
                        let children = {
                            label: '0',
                            item: {
                                type,
                                subtype: 'text',
                                label: '0',
                                field: '0',
                                value: '',
                                key: guid(),
                            }
                        }
                        tt = {
                            label: k,
                            children: [children]
                        }
                    } else if (type === 'number') {
                        let children = {
                            label: '0',
                            item: {
                                type,
                                label: '0',
                                field: '0',
                                value: '',
                                key: guid(),
                            }
                        }
                        tt = {
                            label: k,
                            children: [children]
                        }
                    } else if (type === 'object') {
                        let children = this.inToOut(a1)
                        tt = {
                            label: k,
                            children: [{
                                label: '0',
                                children
                            }]
                        }
                    }
                    t = tt
                }
                outputArr.push(t)
            }
            return outputArr
        }
    }
}
</script>

<style lang="css">
</style>
