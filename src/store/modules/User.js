// initial state
const state = {
    loginName: '',
    nickName: '',
    role: '',
}

// getters
const getters = {
    getAll(state) {
        return state
    },
}

// mutations
const mutations = {
    set(state, o) {
        state[o.key] = o.value
    },
    clear(state) {
        for(let key in state) {
            delete state[key]
        }
    },
}

// actions
const actions = {}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
