<template>
    <ul class="bottom-bar">

        <li title="消息">
            <span class="layui-icon">
                &#xe645;
            </span>
            <span class="count" v-if="notReadPrivateMessageCountTotal"></span>

        </li>
    </ul>
</template>

<script>
    import {mapGetters,mapState} from 'vuex'
    export default {
        name: "bottom-bar",
        computed:{
            ...mapGetters('chat',['onlineUser','notReadPrivateMessageCountTotal']),
            ...mapGetters('system',['currentUID']),
            ...mapState('chat',['notReadPrivateMessageCount'])
        }
    }
</script>

<style scoped lang="less">

    .bottom-bar{
        margin:0;
        padding:0;
        width:100%;
        height:100%;
        display:flex;
        justify-content:flex-start;
        align-items:center;
        user-select:none;

        li{
            list-style:none;
            margin-left:.5em;
            cursor:pointer;
            position:relative;

            .layui-icon{
                font-size:2em;
            }

            @count-size:.5em;
            .count{
                position:absolute;
                right:-@count-size*0.5;
                top:-@count-size*0.5;
                width:@count-size;
                height:@count-size;
                border-radius:@count-size/2;
                background-color:#FF5722;
            }


        }
    }
</style>