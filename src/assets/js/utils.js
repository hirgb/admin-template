/*
 * @Description: 复用函数，可用在多个组件中
 * @Author: zhangkefei
 * @Date: 2019-09-11 17:07:08
 * @LastEditTime: 2019-09-16 15:27:47
 * @LastEditors: Please set LastEditors
 */
import termStat from '@/assets/js/term-stat.js'

function confirm(_this, alertText, callback) {
    if (!_this || typeof(_this) !== 'object') {
        throw new Error('confirm:参数1应该是一个VueComponent实例')
    }
    if (!alertText || typeof(alertText) !== 'string') {
        throw new Error('confirm:参数2应该是一个字符串')
    }
    if (!callback || typeof(callback) !== 'function') {
        throw new Error('confirm:参数3应该是一个函数')
    }
    _this.$confirm(alertText, '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
    }).then(() => {
        callback()
    })
}

function displayError(_this, msg) {
    if ('$message' in _this) {
        _this.$message({
            message: msg,
            type: 'error'
        })
    } else {
        alert(msg)
    }
}

function displayHTTPError(_this, code) {
    let errors = {
        500: '服务器内部错误或内存超出上限',
        400: '输入参数不完整或禁止访问',
    }
    if (code in errors) {
        displayError(_this, errors[code])
    } else {
        displayError(_this, '未知错误，联系系统管理员')
    }
}

function displaySuccess(_this, msg) {
    if ('$message' in _this) {
        _this.$message({
            message: msg,
            type: 'success'
        })
    } else {
        alert(msg)
    }
}

function post(_this, targetUrl, data = '', callbackSuccess = null, callbackError = null, callbackAll = null) {
    if (!('$axios' in _this)) {
        throw new Error('The $axios is not a property of vue instance.')
    }
    if (!('$z' in _this)) {
        throw new Error('The $z is not a property of vue instance.')
    }
    let dataStr = data && Object.keys(data).length > 0 && _this.$z.axios.urlEncode(data)
    _this.$axios.post(targetUrl, dataStr, {
            timeout: 1000 * 60 * 5,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            }
        })
        .then(res => {
            callbackAll && callbackAll()
            let data = res.data
            if (data.code) {
                callbackSuccess && callbackSuccess(data.data)
            } else {
                let errorMsg = data.errorMsg + data.errorArray.join(',')
                displayError(_this, errorMsg)
                callbackError && callbackError(errorMsg)
            }
        })
        .catch(err => {
            callbackAll && callbackAll()
            if (err.response) {
                displayHTTPError(_this, err.response.status)
            } else {
                displayError(_this, err.message)
            }
        })
}

function handleTerms(data) {
    if (Array.isArray(data)) {
        let terms = []
        data.forEach(i => {
            let t = terms.find(j => j.word === i.word)
            if (t) {
                if (i.deleted) {
                    t.ners.push({
                        ner: i.ner + (i.is_amb ? '(amb)' : ''),
                        deleted: i.deleted,
                    })
                } else {
                    if (i.main_entry === 1) {
                        t.ners.unshift({
                            ner: i.ner + (i.is_amb ? '(amb)' : ''),
                            deleted: i.deleted,
                        })
                    } else {
                        t.ners.splice(1, 0, {
                            ner: i.ner + (i.is_amb ? '(amb)' : ''),
                            deleted: i.deleted,
                        })
                    }
                }
            } else {
                i.stat = termStat[i.status]
                i.ners = [{
                    ner: i.ner + (i.is_amb ? '(amb)' : ''),
                    deleted: i.deleted,
                }]
                terms.push(i)
            }
        })
        return terms
    } else {
        return []
    }
}

function handleHistory(data) {
    let terms = []
    let history = []
    data.forEach(i => {
        if (i.history) {
            history = JSON.parse(i.history)
            i.lastHistory = history.pop()
            terms.push(i)
        } else {
            displayError(this, '目前没有历史记录')
        }
    })
    return terms
}

function checkSubmitStatus(mode = 'complate', callback = null) {
    let checkTimer = window.setInterval(() => {
        this.$utils.post(
            this,
            this.$config.dataServer + 'web/mission/checkSubmitStatus', {
                mode
            },
            (data) => {
                callback && callback(data)
            }
        )
    }, 3000)
    return checkTimer
}

async function getAllNers() {
    let result = []
    let res = await this.$axios.post(
        'http://10.0.4.117:1314/get_ner_types', {}, {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            }
        }
    )
    if (res.status === 200) {
        let ners = res.data.ner_list
        ners = [...new Set(ners)]
        result.push('O')
        ners.forEach(i => {
            result.push(i, i + '(amb)')
        })
    } else {
        displayError(this, '未获取到ner列表')
    }
    return result
}

function submit() {
    this.$confirm('确认将所有已标注条目提交测试吗?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
    }).then(() => {
        this.dataLoading = true
        this.$utils.post(
            this,
            this.$config.dataServer + 'web/mission/submitToTest', {
                missionId: this.missionId,
                userId: this.user.id,
                userName: this.user.loginName,
            },
            () => {
                displaySuccess(this, '提交成功')
                this.refreshTerms(this.page.currentPage, this.page.size)
                this.$emit('submit-success')
            },
            null,
            () => {
                this.dataLoading = false
            }
        )
    })
}

class RealViewHeight {
    constructor() {
        this.height = window.innerHeight
        this.handler = () => {
            this.height = window.innerHeight
        }
        this.regist()
    }

    regist() {
        let p = new Processor(300, this.handler)
        window.addEventListener('resize', p.process.bind(p))
    }

    unregist() {
        window.removeEventListener('resize', this.handler)
    }
}

class Processor {
    constructor(time, callback) {
        this.time = time
        this.callback = callback
        this.timeoutId = null
    }

    process() {
        this.timeoutId && clearTimeout(this.timeoutId)
        this.timeoutId = setTimeout(this.callback, this.time)
    }
}

class Page {
    constructor(sizes) {
        if (sizes && Array.isArray(sizes) && sizes.length && typeof(sizes[0]) === 'number') {
            this.currentPage = 1
            this.total = 0
            this.size = sizes[0]
            this.sizes = sizes
        } else {
            throw new Error('Page constructor:初始化参数需要是一个数字数组')
        }
    }

    registRefresh(fn) {
        if (fn && typeof(fn) === 'function') {
            this.refresh = fn
        } else {
            throw new Error('registRefresh:获取数据方法不存在或不是一个函数')
        }
    }

    sizeChange(v) {
        this.currentPage = 1
        this.size = v
        if (this.refresh && typeof(this.refresh) === 'function') {
            this.refresh()
        } else {
            throw new Error('sizeChange:获取数据方法不存在或不是一个函数')
        }
    }

    currentPageChange(v) {
        this.currentPage = v
        if (this.refresh && typeof(this.refresh) === 'function') {
            this.refresh()
        } else {
            throw new Error('currentPageChange:获取数据方法不存在或不是一个函数')
        }
    }
}

class Tools {
    static guid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    static whatType(obj) {
        let type = Object.prototype.toString.call(obj).slice(8, -1).toLowerCase()
        return type
    }

    static jsonEditorInit() {
        require('brace/ext/language_tools')
        require('brace/mode/json')
        require('brace/theme/chrome')
    }
}

class Patch {
    static tabAddBtnFollow(el) {
        let addBtn = el.querySelector('span.el-tabs__new-tab')
        let navListWrap = el.querySelector('div.el-tabs__nav-wrap')
        let tabHeader = el.querySelector('div.el-tabs__header')

        addBtn.style.float = 'none'
        addBtn.style.display = 'inline-block'
        addBtn.style.outline = 'none'
        navListWrap.style.float = 'left'

        setTimeout(() => {
            let navListHeight = navListWrap.getBoundingClientRect().height
            tabHeader.style.height = navListHeight + 'px'
        })
    }

    static error(_this, msg) {
        displayError(_this, msg)
    }

    static httpError(_this, code) {
        displayHTTPError(_this, code)
    }

    static success(_this, msg) {
        displaySuccess(_this, msg)
    }

    static confirm(_this, alertText, callback) {
        confirm(_this, alertText, callback)
    }

    static testPass(e) {
        if (this.currentRow && e && this.currentRow.word === e) {
            let q = {
                userId: this.user.id,
                userName: this.user.loginName,
                word: this.currentRow.word,
                missionId: this.missionId,
                history: JSON.stringify({
                    userId: this.user.id,
                    userName: this.user.loginName,
                    optType: 'pass',
                }),
            }
            this.$utils.post(
                this,
                this.$config.dataServer + 'web/mission/testPass',
                q,
                () => {
                    displaySuccess(this, '操作成功')
                    this.refreshTerms()
                    this.setTestingItemDefault()
                }
            )
        } else {
            displaySuccess(this, '当前行与测试term不一致')
        }
    }

    static testReject() {
        this.refreshTerms()
        this.setTestingItemDefault()
    }

    static setHeadActiveMenu(menu) {
        this.$store.commit('app/set', {
            key: 'activeHeadMenu',
            value: menu
        })
    }

    static forceUpdate() {
        if (!('forceUpdate' in this)) {
            throw new Error('Cann\'t find "forceUpdate" in this')
        }
        this.forceUpdate = false
        this.$nextTick(() => {
            this.forceUpdate = true
        })
    }
}

export default {
    post,
    handleTerms,
    checkSubmitStatus,
    getAllNers,
    handleHistory,
    submit,
    RealViewHeight,
    Page,
    Processor,
    Tools,
    Patch,
}
