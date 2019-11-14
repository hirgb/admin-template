import Vue from 'vue'
import Vuex from 'vuex'
import createPersistedState from "vuex-persistedstate"
import app from './modules/App'
import user from './modules/User'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    app,
    user,
  },
  plugins: [createPersistedState()]
})
