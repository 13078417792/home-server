import mine from '../../util/mine'
import {MessageBox} from 'element-ui'
import Vue from 'vue'

const state = {
    onlineUserArray:[],
    onlineUser:Object.create(null),
    isChatting:false,
    chatUserID:null,
    notReadPrivateMessageCount:Object.create(null), // 针对各用户的未读消息数量
    message:Object.create(null), // 聊天记录
    /*
    *   {
    *       [userID]:[
    *           {
    *                   type:send（发送的消息）|accept（接收的消息）  [接收还是发送]
    *                   time:timestamp,  [发送或接收消息的时间戳]
    *                   content:string|url, [发送、接收的内容]
    *                   contentType:image（图片）|text（文本）|file（文件） [内容类型]
    *           }
    *       ]
    *
    *   }
    *
    **/
}

const mutations = {
    updateOnlineUser(state,data){
        if(Array.isArray(data) && !mine.empty(data)){
            state.onlineUserArray = data
            let onlineUser = Object.create(null),notReadPrivateMessageCount = {}
            data.forEach(el=>{
                onlineUser[el.id] = el
                notReadPrivateMessageCount[el.id] = 0
                if(!Object.hasOwnProperty.call(state.message,el.id) && mine.empty(state.message[el.id])){
                    Vue.set(state.message,el.id,[])
                }
            })
            state.onlineUser = onlineUser
            state.notReadPrivateMessageCount = notReadPrivateMessageCount
        }
    },
    toggleChat(state,uid){
        if(uid && !/[^\d]+/.test(uid)){
            if(!state.onlineUser[uid]){
                state.isChatting = false
                state.chatUserID = null
                MessageBox.alert('用户不在线')
                return
            }
            state.isChatting = true
            state.chatUserID = parseInt(uid)
        }else{
            state.isChatting = false
            state.chatUserID = null
        }
    },
    addNotReadPrivateMessage(state,uid){
        if(mine.empty(state.notReadPrivateMessageCount[uid])){
            // console.log('empty',state.notReadPrivateMessageCount[uid],uid)
            return
        }
        state.notReadPrivateMessageCount[uid] += 1
    },
    reduceNotReadPrivateMessage(state,uid){
        if(mine.empty(state.notReadPrivateMessageCount[uid])){
            return
        }
        state.notReadPrivateMessageCount[uid] = 0
    },

    // 添加聊天记录
    addMessage(state,options={}){
        console.log(options)
        const field = ['message','messageType','type','charUserID']
        const messageType = ['text','image','file']
        const type = ['send','accept']
        if(toString.call(options)!=='[object Object]' || mine.empty(options)){
            return
        }

        // 验证是否有必要数据
        for(let i of field){
            if(!Object.hasOwnProperty.call(options,i) || mine.empty(options[i])){
                console.error(`保存聊天记录时出错：缺少字段${i}`)
                return
            }
        }
        if(!Object.hasOwnProperty.call(state.message,options.charUserID)){
            console.error(`保存聊天记录时出错：用户不在线`)
            return
        }

        // 验证消息类型是否合法
        if(typeof options.messageType!=='string' || !messageType.includes(options.messageType)){
            console.error(`保存聊天记录时出错：消息类型不合法(${options.messageType})`)
            return
        }

        // 验证消息是否合法
        if(typeof options.message!=='string'){
            console.error(`保存聊天记录时出错：消息不合法(${options.message})`)
            return
        }

        // 消息内容是图像或文件时，验证消息是否是一个链接
        if(options.messageType!=='text' && !/^http|https:\/\/.+/.test(options.message)){
            console.error(`保存聊天记录时出错：消息是图像或文件，但链接不合法(${options.message})`)
            return
        }

        if(!['send','accept'].includes(options.type)){
            console.error(`保存聊天记录时出错：${options.type})`)
            return
        }

        state.message[options.charUserID].push({
            time:parseInt(Date.now() / 1000),
            content:options.message,
            contentType:options.messageType,
            type:options.type
        })
    }
}

const actions = {

}

const getters = {
    onlineUser(state){

        return state.onlineUser
    },

    // 未读消息总数
    notReadPrivateMessageCountTotal(state){
        let total = 0
        for(let uid in state.notReadPrivateMessageCount){
            total += state.notReadPrivateMessageCount[uid]
        }
        return total
    },

    // 获取聊天信息
    privateMessage(state){
            if(!state.chatUserID || !Object.hasOwnProperty.call(state.message,state.chatUserID)){
            console.warn('没有指定用户的聊天信息')
            console.log(state.chatUserID)
            return []
        }

        return state.message[state.chatUserID]
    }
    // privateMessage(state){
    //     const rootGetters = arguments[3] // 根节点Getter
    //     const rootState = arguments[2] // 根节点State
    //     const currentUID = rootGetters['system/currentUID']
    //     console.log(rootGetters,currentUID)
    //     if(!state.chatUserID || mine.empty(state.message[state.chatUserID])){
    //         console.warn('没有指定用户的聊天信息')
    //         return []
    //     }
    //     if(!(currentUID && state.message[currentUID][currentUID])) {
    //         console.warn('没有指定用户的聊天信息')
    //         return []
    //     }
    //
    //     return state.message[state.chatUserID][currentUID]
    // }
}

export default {
    state,
    mutations,
    actions,
    getters,
    namespaced:true
}