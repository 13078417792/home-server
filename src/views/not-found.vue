<template>
    <div class="not-found">

        <div class="main">
            <div class="icon">
                <i class="layui-icon">&#xe69c;</i>
            </div>
            <h1 class="tips-code">404</h1>
        </div>
    </div>
</template>

<script>
    export default {
        name: "not-found",
        created(){

        },
        methods:{

        }
    }
</script>

<style scoped lang="less">
    .not-found{
        user-select:none;
        cursor:default;
        width:100%;
        height:100%;
        background-color:#eee;

        .main{
            position:relative;
            top:50vh;
            transform:translateY(-30vh);

            @icon-size:15em;
            .icon{
                width:@icon-size;
                margin:0 auto 1em auto;

                .layui-icon{
                    font-size:@icon-size;
                }
            }

            .tips-code{
                font-size:6em;
                margin:0;
                text-align:center;
                font-weight:lighter;
                color:#009688;
            }
        }
    }
</style>