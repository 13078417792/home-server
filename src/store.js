import Vue from 'vue'
import Vuex from 'vuex'
import system from './vuex/system'
import video from './vuex/video'
import chat from './vuex/websocket/chat'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {

    },
    mutations: {

    },
    actions: {

    },
    modules:{
        system,
        video,
        chat
    }
})
