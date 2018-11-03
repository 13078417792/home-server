<template>
  <div id="app">
    <keep-alive v-if="layout">
      <component :is="layout">
        <router-view/>
      </component>
    </keep-alive>
    <template v-else>
      <router-view />
    </template>
  </div>
</template>

<script>
    import Vue from 'vue'
    import chat from '@/dialog/chat'
    export default{
        name:'App',
        data(){
            return {
                layout:null,
                dialog:[chat]
            };
        },
        watch:{
            '$route':function(newVal){
                if(newVal.meta && newVal.meta.layout){
                    this.layout = Vue.component(newVal.meta.layout,()=>import(`./layouts/${newVal.meta.layout}`)); // 异步注册组件
                    this.chat()
                }else{
                    this.layout = null
                    if(this.dialogInstance.hasOwnProperty('chat')){
                        this.dialogInstance.chat.close()
                    }
                }
            }
        },
        created(){
            // console.log(this.dialogInstance)
            this.getOnlineUser()
            this.getGroupList()
            this.$store.dispatch('system/getUser') // 获取用户列表
        },
        methods:{
            chat(){
                this.dialogInstance.chat.open({
                    title:'站内通信',
                    width:'414px',
                    height:'736px',
                    fixed:3
                })
            },
            getOnlineUser(){
                // 实时更新在线用户
                this.$ws.addBroadCase('onlineUserList',result=>{
                    // console.log(result)
                    this.$store.commit('chat/updateOnlineUser',result.data)
                })
            },
            getGroupList(){
                // 实时更新群组列表
                this.$ws.addBroadCase('groupList',result=>{
                    // console.log(result)
                    console.log(result)
                    this.$store.commit('chat/addSingleGroup',result.data)
                    // this.$store.commit('chat/updateOnlineUser',result.data)
                })
            }
        }
    }
</script>

<style lang="less">
  #app {
    width:100%;
    height:100%;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #2c3e50;
  }
</style>
