<template>
    <div class="chat">
        <tab-container class="chat-tab" :current="current" @toggle="toggleTab">
            <tab-content header-class="layui-icon" label="&#xe612;" name="user" class="sub user-chat" :count="notReadPrivateMessageCountTotal" >

                <div class="user-chat--scroll-wrapper" :style="userChatStyle">
                    <user @toggle="toggleChat('chat')"></user>
                    <chat-frame @toggle="toggleChat('list')"></chat-frame>
                </div>

            </tab-content>
            <tab-content header-class="layui-icon" label="&#xe613;" name="group" class="sub group">
                group
            </tab-content>
        </tab-container>

        <bottom-bar class="message-bar"></bottom-bar>
    </div>
</template>

<script>
    import {mapGetters,mapState} from 'vuex'
    import user from '@/views/chat/user'
    import chatFrame from '@/views/chat/chat-frame'
    import bottomBar from '@/views/chat/bottom-bar'
    export default {
        name: "chat",
        components:{user,chatFrame,bottomBar},
        created(){
            this.acceptPrivateMessage()
        },
        data(){
            return {
                tabIndex:0,
                tabs:{
                    user:'&#xe612;',
                    group:'&#xe613;',
                },
                current:'user',
                userChatStyle:{}
            }
        },
        computed:{
            ...mapGetters('chat',['onlineUser','notReadPrivateMessageCountTotal']),
            ...mapGetters('system',['currentUID']),
            ...mapState('chat',['chatUserID','message'])
        },
        methods:{
            toggleTab(name){
                this.current = name
            },
            // 接收私聊信息
            acceptPrivateMessage(){
                this.$ws.addBroadCase('private_message',result=>{

                    console.log(result)
                    if(result.data.senderUID===this.currentUID){
                        // 同步其他登录相同账号的聊天记录(发送者ID和当前用户ID相同)
                        this.$store.commit('chat/addMessage',{
                            charUserID:result.data.acceptUID, // 接收消息的目标用户ID
                            message:result.data.message,
                            messageType:'text',
                            type:'send'
                        })
                    }else{
                        if(result.data.senderUID!==this.chatUserID){
                            // 增加未读消息数量(发送者ID和当前聊天目标用户ID不相同)
                            this.$store.commit('chat/addNotReadPrivateMessage',result.data.senderUID)
                        }
                        this.$store.commit('chat/addMessage',{
                            charUserID:result.data.senderUID, // 发送者ID
                            message:result.data.message,
                            messageType:'text',
                            type:'accept'
                        })
                    }

                    console.log(result.data.senderUID,this.currentUID)
                })

            },
            toggleChat(type){
                if( !(type && typeof type==='string' && ['chat','list'].includes(type)) ){
                    return
                }

                const move = {
                    chat:'-50%',
                    list:0
                }

                if(type==='chat' && this.currentUID===this.chatUserID){
                    this.$alert('不能和自己对话')
                    this.$store.commit('chat/toggleChat')
                    return
                }
                if(type==='list'){
                    this.$store.commit('chat/toggleChat')
                }

                this.$set(this.userChatStyle,'transform',`translateX(${move[type]})`)
                this.$store.commit('chat/reduceNotReadPrivateMessage',this.chatUserID)
            }
        }
    }
</script>

<style scoped lang="less">

    @pad:1.5em;
    @message-bar-height:3em;
    .chat{
        width:100%;
        height:100%;
        overflow:hidden;
        position:relative;

        .chat-tab{
            padding-bottom:@message-bar-height;
            box-sizing:border-box;


            .sub{
                width:100%;
            }

            .user-chat{
                overflow:hidden;
                width:100%;
                height:100%;



                .user-chat--scroll-wrapper{
                    min-width:100%;
                    width:200%;
                    height:100%;
                    white-space:nowrap;
                    overflow:hidden;
                    transform:translateX(0);
                    transition:transform .2s;

                    >div{
                        width:50%;
                        height:100%;
                        overflow:hidden;
                        display:inline-block;
                    }
                }

            }
        }


        .message-bar{
            width:100%;
            height:@message-bar-height;
            position:absolute;
            bottom:0;
            left:0;
        }



    }
</style>