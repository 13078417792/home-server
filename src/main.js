require("babel-polyfill")
import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
require('./assets/css/style.less')
require('../public/css/layui-icon/layui-icon.css')
import './registerServiceWorker'
import VueCookie from 'vue-cookie'
import methods from '@/util/methods'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
Vue.use(ElementUI)
Vue.use(VueCookie)
Vue.use(methods)
Vue.config.productionTip = false

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')