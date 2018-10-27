<template>
    <div class="chat-frame">
        <header>
            <span class="back el-icon-back" @click="toggle"></span>
            <div class="user-name" v-if="chatUserID">{{onlineUser[chatUserID].nickname}}</div>
        </header>

        <div class="chat-wrapper">
            <div class="chatting">
                <template v-if="chatUserID" >
                    <div class="item" :class="{myself:item.type==='send'}" v-for="(item,key) in privateMessage">
                        <div class="avatar"></div>

                        <div class="chat-content">
                            <p>{{item.content}}</p>
                        </div>
                    </div>
                </template>


            </div>

            <!-- 工具栏 -->
            <div class="fn-bar">
                <ul>
                    <li class="layui-icon upload ">
                        &#xe60d;
                        <input type="file" title="上传图片" accept="image/jpeg,image/png">
                    </li>

                    <li class="layui-icon upload ">
                        &#xe621;
                        <input type="file" title="上传文件">
                    </li>


                </ul>
            </div>

            <textarea class="chat-talk-content" ref="talk" v-model="content">

            </textarea>

            <!-- 底部工具栏 -->
            <div class="bottom-fn-bar">
                <el-button type="primary" @click="send" class="send" size="mini">发送</el-button>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters,mapState} from 'vuex'
    export default {
        name: "chat-frame",
        data(){
            return {
                content:''
            }
        },
        computed:{
            ...mapGetters('chat',['onlineUser','privateMessage']),
            ...mapState('chat',['chatUserID']),
            ...mapGetters('system',['currentUID'])
        },
        methods:{
            toggle(){
                this.content = null
                this.$refs.talk.innerHTML = null
                this.$emit('toggle')
            },
            send(){
                if(this.$isEmpty(this.content)){
                    return
                }
                this.$ws.send('chat/private_message',{content:this.content,type:'text',uid:this.chatUserID})
                this.$store.commit('chat/addMessage',{
                    charUserID:this.chatUserID,
                    message:this.content,
                    messageType:'text',
                    type:'send'
                })
                this.content = null
                this.$refs.talk.innerHTML = null
            }
        }
    }
</script>

<style scoped lang="less">

    .chat-frame{
        width:100%;
        height:100%;
        overflow:hidden;
        background-color:#EBEEF5;
        position:relative;

        @header-height:2em;
        header{
            width:100%;
            height:@header-height;
            overflow:auto;
            background-color:#fff;
            position:absolute;
            top:0;
            left:0;

            .back{
                display:inline-block;
                width:@header-height;
                height:@header-height;
                line-height:@header-height;
                text-align:center;
                cursor:pointer;
                float:left;
            }

            .user-name{
                height:@header-height;
                line-height:@header-height;
                overflow:hidden;
                margin:auto;
                text-align:center;
            }
        }

        .chat-wrapper{
            padding-top:@header-height;
            box-sizing:border-box;
            width:100%;
            height:100%;

            >div{
                background-color:#fff;
            }

            .chatting{
                margin-top:2%;
                height:62%;
                padding:0 10px;
                box-sizing:border-box;
                overflow-x:hidden;
                overflow-y:auto;

                @avatar-size:2em;
                @arrow-size:6px;
                .item{
                    margin-top:10px;
                    padding-left:@avatar-size+1em;
                    box-sizing:border-box;
                    position:relative;

                    .avatar{
                        width:@avatar-size;
                        height:@avatar-size;
                        border-radius:@avatar-size/2;
                        background-color:#e2e2e2;
                        float:left;
                        margin-left:-(@avatar-size+1em);
                    }

                    @content-color:#eee;
                    .chat-content{
                        min-width:3em;
                        max-width:70%;
                        /*padding:5px 15px;
                        min-height:@avatar-size;*/
                        padding:10px;
                        line-height:1em;
                        font-size:.9em;
                        background-color:@content-color;
                        border-radius:4px;
                        color:#000;
                        position:relative;
                        float:left;
                        white-space: pre-line;
                        /*overflow:hidden;*/

                        p{
                            margin:0;
                            overflow:hidden;
                        }

                        &:after{
                            content:'';
                            position:absolute;
                            top:@arrow-size*1.5;
                            left:-@arrow-size*2;
                            width:0;
                            height:0;
                            border:@arrow-size solid transparent;
                            border-right-color:@content-color;
                        }
                    }

                    &:after{
                        content:'';
                        clear:both;
                        display:block;
                    }

                    &:last-child{
                        margin-bottom:10px;
                    }
                }


                .item.myself{
                    padding-right:@avatar-size+1em;
                    padding-left:0;


                    .avatar{
                        float:right;
                        margin-right:-(@avatar-size+1em);
                        margin-left:0;
                    }


                    @content-color:#1E9FFF;
                    .chat-content{
                        background-color:@content-color;
                        color:#fff;
                        float:right;

                        &:after{
                            content:'';
                            left:unset;
                            right:-@arrow-size*2;
                            border-left-color:@content-color;
                            border-right-color:transparent;
                        }

                    }

                    &:after{
                        clear:both;
                        display:block;
                    }
                }



            }


            .fn-bar{
                height:6%;
                background-color: transparent;

                ul{
                    margin:0;
                    padding:0;
                    height:100%;
                    display:flex;
                    justify-content:flex-start;
                    align-items:center;

                    @font-size:1.5em;
                    li{
                        font-size:@font-size;
                        height:1em;
                        display:inline-block;
                        cursor:default;
                        margin-left:.5em;
                        color:#393D49;

                        &:hover{
                            color:#009688;
                        }
                    }

                    .upload{
                        position:relative;
                        overflow:hidden;

                        input[type="file"]{
                            padding:0;
                            margin:0;
                            position:absolute;
                            top:0;
                            left:0;
                            width:100%;
                            height:100%;
                            opacity:0;
                        }
                    }
                }
            }

            @bottom-fn-bar-height:2.5em;
            .chat-talk-content{
                height:calc(31% - @bottom-fn-bar-height);
                line-height:1.5em;
                outline:none;
                white-space:pre-wrap;
                overflow-x:hidden;
                overflow-y:auto;
                font-size:.9em;
                padding:0 5px;
                box-sizing:border-box;
                border:0;
                resize:none;
                display:block;
                width:100%;
            }

            .bottom-fn-bar{
                width:100%;
                height:@bottom-fn-bar-height;
                line-height:@bottom-fn-bar-height;
                position:relative;


                .send{
                    float:right;
                    margin-right:.5em;
                    margin-top:6px;
                }
            }


        }
    }
</style>