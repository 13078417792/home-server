<template>
    <div class="user">
        <div class="user-list">
            <ul>
                <li class="overflow" v-for="(item,key,index) in onlineUser" :key="index" :data-uid="key" @click="startChat(key)" :class="{disabled:parseInt(key)===parseInt(currentUID)}">

                    <span class="username">
                        {{item.nickname?`${item.nickname}[${item.username}]`:item.username}}
                        <span class="message-count" v-if="notReadPrivateMessageCount[key]">
                            {{notReadPrivateMessageCount[key]}}
                        </span>
                    </span>

                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    import {mapGetters,mapState} from 'vuex'
    export default {
        name: "user",
        computed:{
            ...mapGetters('chat',['onlineUser','notReadPrivateMessageCountTotal']),
            ...mapGetters('system',['currentUID']),
            ...mapState('chat',['notReadPrivateMessageCount'])
        },
        methods:{
            startChat(uid){
                if(!uid || /[^\d]/.test(uid)){
                    return
                }
                if(parseInt(uid)===parseInt(this.currentUID)){
                    return
                }
                // console.log(uid)
                this.$store.commit('chat/toggleChat',uid)
                this.$emit('toggle')
            }
        }
    }
</script>

<style scoped lang="less">

    .user{
        overflow-y:auto !important;

        .user-list{
            padding:10px;
            box-sizing:border-box;

            >ul{
                padding:0;
                margin:0;
                font-size:.9em;

                @itemUserHeight:3em;
                >li{
                    height:@itemUserHeight;
                    line-height:@itemUserHeight;
                    cursor:pointer;
                    background-color:#fff;
                    text-indent:1em;


                    &:hover{
                        background-color:#F2F4F8;
                    }

                    .username{
                        position:relative;

                        @message-count-size:1.2em;
                        @message-count-fontsize:.8em;
                        .message-count{
                            font-size:@message-count-fontsize;
                            display:inline-block;
                            background-color:#FF5722;
                            color:#fff;
                            width:@message-count-size/@message-count-fontsize;
                            height:@message-count-size/@message-count-fontsize;
                            line-height:@message-count-size/@message-count-fontsize;
                            text-align:center;
                            border-radius:@message-count-size/2/@message-count-fontsize;
                            position:absolute;
                            right:-@message-count-size*1.3;
                            top:-@message-count-size/1.5/@message-count-fontsize;
                            overflow:hidden;
                            text-indent:0;
                        }
                    }

                }

                li.disabled{
                    cursor:not-allowed;
                    color:#ccc;
                    user-select:none;

                    &:hover{
                        background-color:transparent;
                    }

                    .message-count{
                        display:none;
                    }
                }
            }
        }

    }
</style>