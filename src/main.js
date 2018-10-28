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
import iView from 'iview';
import 'iview/dist/styles/iview.css';

Vue.use(iView);

import components from './components/zcj/components'
Vue.use(components)
Vue.use(ElementUI)
Vue.use(VueCookie)
Vue.use(methods)
Vue.config.productionTip = false
Vue.prototype._zcjComponent.zIndex = 4000
new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')