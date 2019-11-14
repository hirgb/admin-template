export default {
    /**
     * 从输入参数中取出termList，只包含token, ner, rank_score三个字段
     * @param  {[type]} termList [description]
     * @return {[type]}          [description]
     */
    getTermList(termList){
        if (Array.isArray(termList)) {
            let t = []
            termList.forEach(i => {
                if (i.token && i.ner) {
                    t.push({
                        token: i.token,
                        ner: i.ner,
                        rank_score: i.rank_score
                    })
                }
            })
            this.termList = t
        } else {
            throw new Error('The termList is not an array')
        }
    },
    /**
     * 将termList转换为contentList，方便处理
     * contentList中，如果ner为O，那么只保留token，如果相邻元素ner都为O，则将连接一个字符串
     * @return {[type]} [description]
     */
    termList2contentList(){
        let tl = this.termList
        if (Array.isArray(tl) && tl.length) {
            let cl = []
            let t = ''
            tl.forEach(i => {
                if (i.ner === 'O') {
                    t += i.token
                } else {
                    if (t) {
                        cl.push(t)
                        t = ''
                    }
                    cl.push({
                        token: i.token,
                        ner: i.ner,
                        type: 'system',
                        rank_score: i.rank_score
                    })
                }
            })
            if (t) {
                cl.push(t)
                t = ''
            }
            this.contentList = cl
        } else {
            throw new Error('The termList is empty')
        }
    },

    /**
     * 将contentList转换为termList格式，即恢复原来termList中ner为O的元素
     * 以便传回服务器
     * @return {[type]} [description]
     */
    contentList2TermList(){
        try {
            let tl = this.termList
            let cl = JSON.parse(JSON.stringify(this.contentList))
            let tlO = tl.filter(i => i.ner === 'O')
            tlO.sort((a, b) => a.token.length < b.token.length)
            tlO.forEach(i => {
                let complate = false
                while (!complate) {
                    let l = cl.length
                    let has = false // 表示cl中有没有找到token,如果没有找到就结束while循环
                    for (let j = 0; j < l; j++) {
                        if (typeof(cl[j]) === 'string' && cl[j].includes(i.token)) {
                            has = true
                            let str = cl[j]
                            let arr = str.split(i.token)
                            let result = []
                            arr.forEach((k, idx, a) => {
                                if (k) {
                                    result.push(k)
                                    if (a[idx + 1]) {
                                        result.push({
                                            token: i.token,
                                            ner: i.ner,
                                            rank_score: i.rank_score
                                        })
                                    }
                                } else {
                                    if (a[idx + 1] || a[idx + 1] === undefined) {
                                        result.push({
                                            token: i.token,
                                            ner: i.ner,
                                            rank_score: i.rank_score
                                        })
                                    }
                                }
                            })
                            cl.splice(j, 1, ...result)
                            break
                        }
                    }
                    complate = !has
                }
            })
            cl.forEach((i, idx, a) => {
                if (typeof(i) === 'string') {
                    a[idx] = {
                        token: i,
                        ner: 'O',
                        rank_score: 0,
                    }
                }
                if (typeof(i) === 'object') {
                    delete a[idx].type
                }
            })
            this.returnList = cl

        } catch (e) {
            throw e
        }
    },
    deleteNer(index){
        let cl = this.contentList
        if (Array.isArray(cl) && cl.length) {
            let token = cl[index].token
            //处理前后元素
            //如果前后元素为字符串，需要连接起来
            let first = index, count = 1
            if (cl[index - 1] && typeof(cl[index - 1]) === 'string') {
                first = index - 1
                count ++
                token = cl[index - 1] + token
            }
            if (cl[index + 1] && typeof(cl[index + 1]) === 'string') {
                count ++
                token += cl[index + 1]
            }
            cl.splice(first, count, token)
        } else {
            throw new Error('The contentList is empty')
        }
    },
    changeNerFromToken(token, ner){
        let cl = this.contentList
        if (Array.isArray(cl) && cl.length) {
            let term = cl.find(i => {
                return i.token && i.token === token
            })
            // cl.splice(index, 1, token)
            if (term) {
                term.ner = ner
                term.type = 'user'
            }
        } else {
            throw new Error('The contentList is empty')
        }
    },

    changeNer(index, ner){
        let cl = this.contentList
        if (Array.isArray(cl) && cl.length) {
            let target = cl[index]
            if (typeof(target) === 'object') {
                target.ner = ner
                target.type = 'user'
            } else {
                throw new Error('选中元素不是预期类型')
            }
        } else {
            throw new Error('The contentList is empty')
        }
    },
    /**
     * 在编辑过程中为一段文字添加一个ner
     * @param {[type]} token [description]
     * @param {[type]} ner   [description]
     */
    addNer(index, start, end, ner){
        if (!ner) {
            throw new Error('ner can not be empty')
        }
        let cl = this.contentList
        if (Array.isArray(cl) && cl.length) {
            let target = cl[index]
            if (typeof(target) === 'string') {
                let result = [
                    target.slice(0, start),
                    {
                        token: target.slice(start,end),
                        ner,
                        type: 'user',
                        rank_score: 0
                    },
                    target.slice(end, target.length)
                ]
                cl.splice(index, 1, ...result.filter(i => !!i))
            } else {
                throw new Error('添加ner过程中，取到的contentList元素为空或类型不正确')
            }
        } else {
            throw new Error('The contentList is empty')
        }
    },
    /**
     * 将contentList渲染为html以供显示
     * @return {[type]} [description]
     */
    render(){
        let cl = this.contentList
        if (Array.isArray(cl) && cl.length) {
            let h = ''
            cl.forEach(i => {
                let t = typeof(i)
                if (t === 'string') {
                    h += `${i}`
                } else if (t === 'object') {
                    h += `<span class="${i.type}"><i>[NOR]</i> ${i.token} <i>[${i.ner}]</i></span>`
                }
            })
            this.html = h
        } else {
            throw new Error('The contentList is empty')
        }
    },
    /**
     * 将termList和returnList转换为便于记录的格式
     * @return {[type]} [description]
     */
    renderToSave(){
        let tl = this.termList
        let rl = this.returnList
        if (Array.isArray(tl) && Array.isArray(rl)) {
            return {
                tl: this.list2String(tl),
                rl: this.list2String(rl)
            }
        } else {
            throw new Error('The termList or contentList is empty')
        }
    },
    /**
     * 将list转换为string，ner为O的转换为纯字符串
     * @param  {[type]} list [description]
     * @return {[type]}      [description]
     */
    list2String(list){
        let s = ''
        list.forEach(i => {
            let t = typeof(i)
            if (t === 'string') {
                s += i
            } else if (t === 'object') {
                if (i.ner === 'O') {
                    s += i.token
                } else {
                    s += `[NOR]${i.token}[${i.ner}]`
                }
            }
        })
        return s
    },
}
