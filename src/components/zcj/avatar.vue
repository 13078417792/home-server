<template>
    <div class="zcj-avatar">
        <img class="avatar-image" v-if="image" :src="image">
    </div>
</template>

<script>
    export default {
        name: "avatar",
        props:{
            image:{
                type:String,
                default:null
            }
        }
    }
</script>

<style scoped lang="less">

    .zcj-avatar{
        background-color:#e2e2e2;
        width:100%;
        height:100%;
        border-radius:50%;
        display:inline-block;
        overflow:hidden;

        .avatar-image{
            display:block;
            width:100%;
            height:100%;
        }
    }
</style>