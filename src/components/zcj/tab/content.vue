<template>
    <div class="tab---content" :name="name" v-show="$parent.$props.current===name">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        name: "tab-content",
        props:{
            label:{
                type:String,
                required:true
            },
            name:{
                type:String,
                required:true
            },
            'header-class':{
                type:String,
                required:false,
                default:''
            }
        }
    }
</script>

<style scoped lang="less">
    .tab---content{
        width:100%;
        height:100%;
        overflow-x:hidden;
        overflow-y:auto;
    }
</style>