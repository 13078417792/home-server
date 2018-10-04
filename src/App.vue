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
    export default{
        name:'App',
        data(){
            return {
                layout:null
            };
        },
        watch:{
            '$route':function(newVal){
                if(newVal.meta && newVal.meta.layout){
                    this.layout = Vue.component(newVal.meta.layout,()=>import(`./layouts/${newVal.meta.layout}`)); // 异步注册组件
                }else{
                    this.layout = null;
                }
            }
        },
        created(){
            // document.addEventListener('drop',function(e){
            //     e.preventDefault()
            // })
            // document.addEventListener('dropend',function(e){
            //     e.preventDefault()
            // })
            // document.addEventListener('dropenter',function(e){
            //     e.preventDefault()
            // })
            // document.addEventListener('dropexit',function(e){
            //     e.preventDefault()
            // })
            // document.addEventListener('dropleave',function(e){
            //     e.preventDefault()
            // })
            // document.addEventListener('dropover',function(e){
            //     e.preventDefault()
            // })
            // document.addEventListener('dropstart',function(e){
            //     e.preventDefault()
            // })

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
