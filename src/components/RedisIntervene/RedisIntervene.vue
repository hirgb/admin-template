<!--
 * @Description: redis干预的页面，这个页面主要是对输入的query进行分词识别，
 然后对分词的类型（ner）进行编辑修改与删除
 * @Author: sunshuixian
 * @Date: 2019-08-09 17:14:40
 * @LastEditTime: 2019-09-16 18:11:18
 * @LastEditors: Please set LastEditors
 -->
<template lang="html">
<div>
    <el-dialog title="修改详情" :visible.sync="modifyDialogVisible" :close-on-click-modal="false" id="dialog">
        <el-row :gutter="20">
            <el-col :span="12">
              <div class="title"><h3>修改前</h3></div>
                <div class="history">
                    <div>意图：{{modifyIntent.before}}</div>
                    <div style="margin-top: 10px;" v-html="modifyQuery.before"> </div>
                </div>
            </el-col>
            <el-col :span="12">
              <div class="title"><h3>修改后</h3></div>
                <div class="history" style="background-color:#ecf5ff;">
                    <div>意图：{{modifyIntent.after}}</div>
                    <div style="margin-top: 10px;" v-html="modifyQuery.after"></div>
                </div>
            </el-col>
        </el-row>
    </el-dialog>
    <el-row :gutter="20" id="card">
        <el-col :span="12">
            <el-row>
                <el-card id="seg-display" class="box-card" body-style="height:168px" shadow="never">
                    <div slot="header" class="clearfix">
                        <span>识别查询</span>
                    </div>
                    <div v-show="!!segResult">
                        <span style="font-size: 15px" v-show="!intent.isNull">意图：{{ intent.before }}</span>
                        <p class="segresult" v-html="segResult"></p>
                    </div>
                </el-card>
            </el-row>
            <el-row>
                <div class="text">
                    <el-input type="textarea" placeholder="请输入内容" v-model="query" :rows="2" resize="none">
                    </el-input>
                </div>
            </el-row>
            <el-row>
                <el-button type="primary" icon="el-icon-search" style="float: right" :disabled="!query" @click="segment" :loading="segLoading">识别</el-button>
            </el-row>
        </el-col>
        <el-col :span="12">
            <el-card id="seg-editor" class="box-card" body-style="height:250px" shadow="never" :style="{backgroundColor: isTested ? '#ecf5ff' : '#fff'}">
                <div slot="header" class="clearfix" >
                    <span style="text-align:center">{{ isTested ? '测试环境结果' : '干预标注'}}</span>
                    <el-button
                    style="float:right; padding: 3px"
                    :type="isTested ? 'success' : 'info'"
                    :disabled="!segMark || !isTested"
                    :loading="uploadLoading"
                    @click="save('formal')">
                        上线(正式)<i class="el-icon-upload el-icon--right"></i>
                    </el-button>
                    <el-button
                    style="float:right; padding: 3px; margin-right: 10px"
                    :type="!segMark || isTested ? 'info' : 'primary'"
                    :disabled="!segMark || isTested"
                    :loading="uploadLoading"
                    @click="save('test')">
                        上线(测试)<i class="el-icon-upload el-icon--right"></i>
                    </el-button>
                </div>
                <div v-show="!!segMark">
                    <span style="font-size: 15px" v-show="!intent.isNull">意图：
                        <el-select
                            v-model="intent.after">
                            <el-option
                                v-for="i in intents"
                                :key="i"
                                :label="i"
                                :value="i">
                            </el-option>
                        </el-select>
                    </span>
                    <p id="seg-mark" class="segmark" v-html="segMark"></p>
                </div>
                <ner-editor ref="nerEditor" @delete-ner="deleteNer" @change-or-add-ner="changeOrAddNer" />
            </el-card>
        </el-col>
    </el-row>
    <el-row  style="margin:20px 0">
        <el-col :span="18">
            <el-button type="primary" :disabled="!multipleSelection.length" @click="exportExcel(multipleSelection)">导出</el-button>
            <el-button type="danger" :disabled="!multipleSelection.length" @click="deleteIntervenes">删除</el-button>
            <a id="downlink" style="display: none"></a>
        </el-col>
        <el-col :span="6">
            <el-input placeholder="请输入关键字" v-model.trim="keyWord" @keyup.enter.native="search" clearable>
                <el-button slot="append" icon="el-icon-search" @click="search" :disabled="historyLoading">搜索</el-button>
            </el-input>
        </el-col>
    </el-row>
    <el-row>
        <el-col :span="24">
            <el-table ref="multipleTable"
            :data="tableData"
            :height="tableHeight"
            :cell-style="{padding: '0'}"
            :header-cell-style="{padding: '0'}"
            style="width: 100%;font-size:13px"
            border
            tooltip-effect="dark"
            @selection-change="handleSelectionChange"
            v-loading="historyLoading" >
                <el-table-column type="selection" width="55" ></el-table-column>
                <el-table-column prop="query" label="query" show-overflow-tooltip>
                  <template slot-scope="scope">
                    <div  v-html="`<p>${scope.row.query}</p>`"></div>
                  </template>
                </el-table-column>
                <el-table-column prop="status" label="干预状态" align="center" width="250" show-overflow-tooltip> </el-table-column>
                <el-table-column prop="userName" label="干预人" width="80" align="center"></el-table-column>
                <el-table-column prop="date" label="干预时间" width="160" sortable align="center"></el-table-column>
                <el-table-column prop="operation" label="操作" width="120" align="center">
                    <template slot-scope="scope">
                        <el-button type="text" @click="getModifyDetail(scope.row)">查看修改</el-button>
                        <el-button type="text" @click="deleteIntervene(scope.row.id)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-col>
    </el-row>
    <el-row style="text-align:center">
        <el-col :span="24">
            <el-pagination
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
            :current-page="page"
            :page-sizes="[20,50,100]"
            :page-size="pageSize"
            layout="total, sizes, prev, pager, next, jumper"
            :total="pageTotal">
            </el-pagination>
        </el-col>
    </el-row>
</div>
</template>
<script>
import XLSX from "xlsx";
import dlgtProto from "@/assets/js/delegator.js";
import NerEditor from "./NerEditor.vue";

let dlgt = Object.create(dlgtProto)

export default {
    components: {
        NerEditor
    },
    data() {
        return {
            modifyDialogVisible: false,
            modifyQuery: {},
            modifyIntent: {},
            multipleSelection: [],
            tableData: [],
            query: '',
            keyWord: "",
            value: "",
            seg: null, //识别返回结果
            segResult: "", //渲染后的html字符串，供显示
            segMark: "", //显示在编辑区的html字符串
            segMarkTemp: '', //存入Mysql的字符串
            currentNer: null, //{ act: '', content: '' }
            browserSreenH: window.innerHeight,
            allDataCount: 0,
            page: 1,
            pageSize: 20,
            pageTotal: 0,
            historyLoading: false,
            segLoading: false,
            uploadLoading: false,
            intent: {
                isNull: false,
                before: '',
                after: '',
            },
            intents: ["非日程", "日程"],
            isTested: false,

            bigBang: {
                start: 0,
                end: 0,
            }
        };
    },
    computed: {
        user: function() {
            return this.$store.getters["user/getAll"];
        },
        role: function() {
            return this.user.role;
        },
        tableHeight: function() {
            return window.innerHeight - 535;
        },
    },
    watch: {
        query: function(){
            this.segMark = ''
            this.segResult = ''
        },
        'intent.after': function(){
            this.isTested = false
        },
    },
    mounted() {
        document.addEventListener("click", this.handleNerClick)
        document.addEventListener("mouseup", this.addNer)
        this.outFile = document.getElementById("downlink");
        this.updateHistory();
    },
    methods: {
        handleSizeChange(pageSize) {
            this.page = 1
            this.pageSize = pageSize
            this.updateHistory()
        },
        /**
         * @description: 页面跳转从数据库中调取相应的数据
         * @param {type}
         * @return: 返回某个页面的数据
         */
        handleCurrentChange(page) {
            this.page = page
            this.updateHistory()
        },

        updateHistory(){
            this.historyLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + "web/Intervene/getHistory", {
                    page: this.page,
                    pageSize: this.pageSize,
                    query: this.keyWord
                },
                data => {
                    this.tableData = data.data
                    this.pageTotal=data.total
                },
                null,
                () => {
                    this.historyLoading = false
                }
            );
        },

        /**
         * @description: 表格多选，可以进行删除或者导出
         * @param {type}
         * @return:
         */
        handleSelectionChange(val) {
            this.multipleSelection = val;
        },
        /**
         * @description: 将输入的query进行识别，并将query分割成多个部分，对每一个小部分匹配类型（ner),
         * 若匹配类型不正确，还可以对匹配的类型修改
         * @param {type}
         * @return:
         */
        segment() {
            let query = this.query;
            if (query) {
                this.segLoading = true
                this.$utils.post(
                    this,
                    this.$config.dataServer + "web/Intervene/segment", {
                        query: query,
                        env: 'formal',
                    },
                    res => {
                        if (res && res.error_msg === "success") {
                            this.seg = res; //保存返回结果以备后用
                            if (
                                res.result_list[0] &&
                                res.result_list[0].domain_list[3] &&
                                res.result_list[0].domain_list[4] &&
                                this.intents.includes(res.result_list[0].domain_list[3].name) &&
                                this.intents.includes(res.result_list[0].domain_list[4].name)
                            ) {
                                this.intent.isNull = false
                                this.intent.before = res.result_list[0].domain_list[3].name
                                this.intent.after = res.result_list[0].domain_list[3].name
                            } else {
                                this.intent.isNull = true
                            }

                            let termList = res.result_list[0].term_list;
                            if (Array.isArray(termList)) {
                                dlgt.getTermList(termList);
                                dlgt.termList2contentList();
                                dlgt.contentList2TermList();
                                dlgt.render();
                                this.segResult = dlgt.html;
                                this.segMark = dlgt.html;
                            }
                        } else {
                            this.$message({
                                message: "服务器返回错误",
                                type: "warning"
                            });
                        }
                    },
                    null,
                    () => {
                        this.segLoading = false;
                        this.isTested = false;
                    }
                );
            } else {
                this.$message({
                    message: "输入框不能为空！",
                    type: "warning"
                });
            }
        },
        handleNerClick(e) {
            let target = e.target;
            let isInEditor = e.path.find(i => i.nodeName === "P" && i.className === 'segmark');
            if (
                isInEditor &&
                target.tagName === "SPAN" && ["system", "user"].includes(target.className)
            ) {
                let x = e.x,
                    y = e.y,
                    parentNode = isInEditor,
                    sibings = parentNode.childNodes,
                    index = [].findIndex.call(sibings, i => i === target)

                this.currentNer = {
                    act: "changeOrDelNer",
                    content: target.innerHTML,
                    index,
                };
                this.$refs.nerEditor && this.$refs.nerEditor.show(x, y + 20, true);
            }
        },
        /**
         * @description: 删除segment分词匹配不正确的类型
         * @param {type}
         * @return:
         */
        deleteNer() {
            let index = this.currentNer.index
            dlgt.deleteNer(index)
            dlgt.render()
            this.segMark = dlgt.html
            this.$refs.nerEditor && this.$refs.nerEditor.hidden();
            this.isTested = false
        },
        /**
         * @description: 对类型ner进行修改和对不正确的类型进行替换操作
         * @param {type}
         * @return:
         */
        changeOrAddNer(ner) {
            let act = this.currentNer.act
            if (act === "changeOrDelNer") {
                let index = this.currentNer.index
                dlgt.changeNer(index, ner)
                dlgt.render()
                this.segMark = dlgt.html
                this.$refs.nerEditor && this.$refs.nerEditor.hidden()
                this.isTested = false
            } else if (act === "addNer") {
                let index = this.currentNer.index,
                    start = this.currentNer.start,
                    end = this.currentNer.end
                dlgt.addNer(index, start, end, ner);
                dlgt.render();
                this.segMark = dlgt.html;
                this.$refs.nerEditor && this.$refs.nerEditor.hidden();
                this.isTested = false
            }
        },
        addNer(e) {
            let w = document.body.clientWidth
            let target = e.target,
                x = w - e.x < 240 ? w - 240 : e.x,
                y = e.y
            if (target.tagName === "P" && target.className === 'segmark') {
                let selecter = window.getSelection()
                if (selecter != null) {
                    let focusNode = selecter.focusNode,
                        parentNode = focusNode.parentNode,
                        sibings = parentNode.childNodes,
                        index = [].findIndex.call(sibings, i => i === focusNode)

                    let data = selecter.baseNode.data,
                        start = Math.min(selecter.baseOffset, selecter.focusOffset),
                        end = Math.max(selecter.baseOffset, selecter.focusOffset)
                    if (start < end) {
                        let select = data.slice(start, end);
                        if (select) {
                            this.currentNer = {
                                act: "addNer",
                                index,
                                data,
                                content: select,
                                start,
                                end
                            };
                            this.$refs.nerEditor && this.$refs.nerEditor.show(x, y + 20)
                        }
                    } else {
                        this.$refs.nerEditor && this.$refs.nerEditor.hidden()
                    }
                }
            } else {
                let path = e.path
                if (!path.includes(this.$refs.nerEditor.$el)) {
                    this.$refs.nerEditor && this.$refs.nerEditor.hidden()
                }
            }
        },
        /**
         * @description: 将修改后的segment上传（提交）
         * @param {type}
         * @return:
         */
        save(env) {
            if (!env) return

            //修改意图
            //如果intent是正常的
            if (!this.intent.isNull) {
                if (this.intent.before !== this.intent.after) {
                    this.seg.result_list[0].domain_list[3] = {
                        name: this.intent.after,
                        value: 1
                    }
                    this.seg.result_list[0].domain_list[4] = {
                        name: this.intent.before,
                        value: 0
                    }
                }
            }
            dlgt.contentList2TermList()
            let redis = dlgt.returnList
            // let mysql = dlgt.renderToSave()
            this.seg.result_list[0].term_list = redis;
            this.uploadLoading = true
            if (env === 'test') this.segMarkTemp = this.segMark
            let req = {
                query: this.query,
                beforeQuery: this.segResult,
                afterQuery: this.segMarkTemp,
                beforeIntent: this.intent.before,
                afterIntent: this.intent.after,
                userId: this.user.id,
                seg: JSON.stringify(this.seg),
                env,
            }
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/Intervene/redisSave',
                req,
                (data) => {
                    if (env === 'formal') {
                        this.$utils.Patch.success(this, "添加成功")
                        this.segMark = ""
                        this.segResult = ""
                        this.query = ""
                        this.isTested = false
                        this.updateHistory()
                    } else if (env === 'test') {
                        this.$utils.Patch.success(this, data.status)
                        let seg = data.segment
                        if (seg && seg.error_msg === "success") {
                            this.seg = seg; //保存返回结果以备后用
                            if (
                                seg.result_list[0] &&
                                seg.result_list[0].domain_list[3] &&
                                seg.result_list[0].domain_list[4] &&
                                this.intents.includes(seg.result_list[0].domain_list[3].name) &&
                                this.intents.includes(seg.result_list[0].domain_list[4].name)
                            ) {
                                this.intent.after = seg.result_list[0].domain_list[3].name
                            } else {
                                this.intent.after = ''
                            }

                            let termList = seg.result_list[0].term_list;
                            if (Array.isArray(termList)) {
                                dlgt.getTermList(termList);
                                dlgt.termList2contentList();
                                dlgt.contentList2TermList();
                                dlgt.render();
                                this.segMark = dlgt.html;
                            } else {
                                this.segMark = ''
                            }
                            this.isTested = true
                        } else {
                            this.$message({
                                message: "服务器返回错误",
                                type: "warning"
                            });
                        }
                    }
                },
                null,
                () => this.uploadLoading = false
            )
        },
        /**
         * @description:显示segment中被修改的详情
         * @param {type}
         * @return: 返回被修改前与修改后的数据
         */
        getModifyDetail(row) {
            this.modifyQuery = {
                before: row.before_query,
                after: row.after_query
            };
            this.modifyIntent = {
                before: row.before_intent,
                after: row.after_intent,
            }
            this.modifyDialogVisible = true;
        },
        /**
         * @description: 通过Id删除干预的记录
         * @param {type}
         * @return:
         */
        deleteIntervene(id) {
            this.$confirm("确认删除这条记录吗?", "提示", {
                confirmButtonText: "确定",
                cancelButtonText: "取消",
                type: "warning"
            }).then(() => {
                this.$utils.post(
                    this,
                    this.$config.dataServer + "web/Intervene/deleteInterveneById", {
                        id
                    },
                    () => {
                        this.$utils.Patch.success(this, "删除成功");
                        this.updateHistory();
                    }
                );
            });
        },
        deleteIntervenes() {
            this.$confirm("确认删除这些记录吗?", "提示", {
                confirmButtonText: "确定",
                cancelButtonText: "取消",
                type: "warning"
            }).then(() => {
                let ids = this.multipleSelection.map(i => i.id);
                this.$utils.post(
                    this,
                    this.$config.dataServer + "web/Intervene/deleteIntervenes", {
                        ids: ids.join(",")
                    },
                    () => {
                        this.updateHistory();
                    }
                );
            });
        },
        /**
         * @description: 通过关键词查找表格的数据，关键词主要匹配query
         * @param {type}
         * @return: 返回包含关键词的数据
         */
        search() {
            var q = this.keyWord;
            this.historyLoading = true
            this.$utils.post(
                this,
                this.$config.dataServer + "web/Intervene/search", {
                    q,
                    page:this.page,
                    size:this.pageSize
                },
                (data) => {
                    this.tableData = data.data
                    this.pageTotal = data.total

                    var str = '/' + this.keyWord + '/g';
                    let len = this.tableData.length;
                    var str1 = '<span id="keyWord">' + this.keyWord + '</span>';
                    for (let i = 0; i < len; i++) {
                        this.tableData[i].query = this.tableData[i].query.replace(eval(str), str1);
                    }
                },
                null,
                () => {
                    this.historyLoading = false
                }
            )
        },
        /**
         * @description: 将表格中的数据导出到Excel
         * @param {type}
         * @return:
         */
        exportExcel(rs) {
            let data = [{}];
            for (let k in rs[0]) {
                data[0][k] = k;
            }
            data = data.concat(rs);
            this.downloadExl(data, "data");
        },
        downloadExl(json, downName, type) {
            // 导出到excel
            let keyMap = []; // 获取键
            for (let k in json[0]) {
                keyMap.push(k);
            }
            let tmpdata = []; // 用来保存转换好的json
            json
                .map((v, i) =>
                    keyMap.map((k, j) =>
                        Object.assign({}, {
                            v: v[k],
                            position:
                                (j > 25 ? this.getCharCol(j) : String.fromCharCode(65 + j)) +
                                (i + 1)
                        })
                    )
                )
                .reduce((prev, next) => prev.concat(next))
                .forEach(function(v) {
                    tmpdata[v.position] = {
                        v: v.v
                    };
                });
            let outputPos = Object.keys(tmpdata); // 设置区域,比如表格从A1到D10
            let tmpWB = {
                SheetNames: ["mySheet"], // 保存的表标题
                Sheets: {
                    mySheet: Object.assign({},
                        tmpdata, // 内容
                        {
                            "!ref": outputPos[0] + ":" + outputPos[outputPos.length - 1] // 设置填充区域
                        }
                    )
                }
            };
            let tmpDown = new Blob(
                [
                    this.s2ab(
                        XLSX.write(
                            tmpWB, {
                                bookType: type === undefined ? "xlsx" : type,
                                bookSST: false,
                                type: "binary"
                            } // 这里的数据是用来定义导出的格式类型
                        )
                    )
                ], {
                    type: ""
                }
            ); // 创建二进制对象写入转换好的字节流
            var href = URL.createObjectURL(tmpDown); // 创建对象超链接
            this.outFile.download = downName + ".xlsx"; // 下载名称
            this.outFile.href = href; // 绑定a标签
            this.outFile.click(); // 模拟点击实现下载
            setTimeout(function() {
                // 延时释放
                URL.revokeObjectURL(tmpDown); // 用URL.revokeObjectURL()来释放这个object URL
            }, 100);
        },
        s2ab(s) {
            // 字符串转字符流
            var buf = new ArrayBuffer(s.length);
            var view = new Uint8Array(buf);
            for (var i = 0; i !== s.length; ++i) {
                view[i] = s.charCodeAt(i) & 0xff;
            }
            return buf;
        },
    }
};
</script>

<style lang="stylus">
i
    font-style: normal
    cursor: default
span.system
    display: inline-block
    color: #66b1ff
    // background-color: #66b1ff
    cursor: pointer
    line-height: 1.2
    padding: 0 3px
    border-radius: 3px
    user-select: none
    i
        font-size: 0.8em
        font-style: normal
span.user
    display: inline-block
    color: #f56c6c
    // background-color: #f56c6c
    cursor: pointer
    line-height: 1.2
    padding: 0 3px
    border-radius: 3px
    user-select: none
    i
        font-size: 0.8em
        font-style: normal
.el-dialog__header
    border-bottom-style: solid
    border-bottom-width:1.5px
    border-bottom-color:#F0F0F0
#card
    .el-card__header
        padding-top: 8px
        padding-bottom: 8px
    .el-card__body
        padding-top: 10px
        overflow-y:auto
    p
        margin-top: 10px
#keyWord
    color:red
    font-weght:bold
</style>

<style scoped lang="stylus">
.text
    font-size: 14px
.title
    padding-bottom :10px
.item
    margin-bottom: 18px
.segresult
    font-size: 15px
.segmark
    font-size: 15px
.history
    min-height: 300px
    border: 1px solid #f0f0f0
    border-radius: 5px
    padding: 10px
</style>
