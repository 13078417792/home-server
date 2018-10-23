<template>
    <li class="tab-header" :class="{on:$parent.$props.current===name}" @click.stop="toggle">
        <slot></slot>
    </li>
</template>

<script>
    export default {
        name: "tab-header",
        props:{
            name:{
                type:String,
                required:true
            }
        },
        data(){
            return {

            }
        },
        methods:{
            toggle(){
                this.$emit('toggle',this.$props.name)
            }
        }
    }
</script>

<style scoped lang="less">

    .tab-header{
        height:100%;
        width:100%;
        list-style:none;
        font-size:1.3em;
        text-align:center;
        cursor:pointer;
        color:#393D49;
        transition:color .1s;
        position:relative;
        overflow:hidden;

        &:hover{
            color:#5FB878;
        }


    }

    .tab-header.on{
        color:#5FB878;

        @border:3px;
        &:after{
            content:'';
            width:200%;
            height:@border;
            background-color:#5FB878;
            position:absolute;
            bottom:0;
            left:0;
        }
    }
</style>