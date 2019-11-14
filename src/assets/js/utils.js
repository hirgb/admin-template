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

export default {
    post,
    RealViewHeight,
    Processor,
    Tools,
}
