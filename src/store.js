import Vue from 'vue'
import Vuex from 'vuex'
import pageTabs from './vuex/page-tabs'
import system from './vuex/system'
import video from './vuex/video'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {

    },
    mutations: {

    },
    actions: {

    },
    modules:{
        pageTabs,
        system,
        video
    }
})
