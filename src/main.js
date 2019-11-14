import Vue from 'vue'
import App from './App.vue'
import VueRouter from 'vue-router'
import Axios from 'axios'
import store from './store'
import Z from '@/assets/js/z.js'
import utils from '@/assets/js/utils.js'
import router from './router'
import config from '@/assets/js/config'

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'

Vue.config.productionTip = false
Vue.use(VueRouter)
Vue.use(ElementUI, {
  size: 'small'
})

Vue.prototype.$axios = Axios
Vue.prototype.$z = Z
Vue.prototype.$utils = utils
Vue.prototype.$config = config

new Vue({
  router,
  store,
  render: h => h(App),
}).$mount('#app')
