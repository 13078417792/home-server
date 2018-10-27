<template>
    <div class="index">

    </div>
</template>

<script>
    export default {
        name: "Index",
        data(){
            return {
                msg:''
            }
        },
        created(){
            window.ws = this.$ws
        },
        methods:{

        }
    }
</script>

<style scoped lang="less">
    .index{
        height:100%;
        overflow-y:auto;
        overflow-x:hidden;
    }
</style>