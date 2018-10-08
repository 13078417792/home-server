<template>
    <div class="video">

        <div class="list-container">

            <div class="item" v-for="(item,index) in list" :key="index">

                <!-- 封面图 -->
                <div class="thumb">
                    <img :src="item.thumb" alt="">
                </div>

                <div class="simple-info">

                    <div class="info">
                        <p class="video-title">

                            <span class="category sl">{{item.category_data[item.category_data.length-1].name}}</span>

                            <span class="inner-title sl">
                                <router-link :to="{name:'video-update',query:{id:item.id}}">{{item.title}}</router-link>
                            </span>
                        </p>

                        <p class="time">{{item.time_str.substr(2)}}</p>

                    </div>

                    <div class="button-container">
                        <el-button type="warning" size="mini" v-if="item.status===1" @click.stop="videoStatus(item.id,0,item.title)">禁用</el-button>
                        <el-button type="primary" size="mini" v-else @click.stop="videoStatus(item.id,1,item.title)">开启</el-button>
                        <el-button type="danger" size="mini" @click.stop="deleteVideo(item.id,item.title)">删除</el-button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    export default {
        name: "page-video",
        created(){
            this.getVideoList()
            // this.$store.dispatch('video/getCategory')
        },
        data(){
            return {
                page:{
                    size:20,
                    total:0,
                    current:1,
                    last:1
                },
                list:[]
            }
        },
        computed:{

        },
        methods:{

            async getVideoList(){
                const {data} = await this.$http.post('/back/video/list')
                const result = data.result
                this.list = new Array(...result.data)
                this.page = Object.assign({},this.page,{
                    size:result.per_page,
                    total:result.total,
                    current:result.current_page,
                    last:result.last_page
                })
            },
            async videoStatus(id,status,title){
                if(!id || status===undefined){
                    this.$tips('操作失败','error')
                    return
                }
                const statusStr = status===1?'开启':'禁用'
                await this.$confirm(title?`确定${statusStr}[${title}]吗`:`确定${statusStr}这个视频[id=${id}]吗`,'提示',{type: 'warning'})
                const {data} = await this.$http.post('/back/video/setStatus',{
                    id:id,
                    status:status
                })
                if(data.success){
                    this.getVideoList()
                }
            },
            async deleteVideo(id,title){
                if(!id){
                    this.$tips('操作失败','error')
                    return
                }
                await this.$confirm(title?`确定删除[${title}]吗`:'确定删除这个视频吗','提示',{type: 'warning'})
                const {data} = await this.$http.post('/back/video/deleteVideo',{
                    id:id
                })
                if(data.success){
                    this.getVideoList()
                }
            }

        }
    }
</script>

<style scoped lang="less">
    .video{


        .list-container {
            width: 1366px;
            max-width:50%;
            height: auto;
            margin:0 auto;


            @thumb-width:16em;
            @thumb-height:@thumb-width*(1080/1920);
            .item{
                border:1px solid #e5e9ef;
                border-radius:3px;
                padding:15px;
                margin-bottom:1.5em;
                height:@thumb-height;

                &:last-child{
                    margin-bottom:0;
                }

                &:after{
                    content:'';
                    display:block;
                    clear:both;
                }


                .thumb{
                    width:@thumb-width;
                    height:inherit;
                    display:inline-flex;
                    justify-content:center;
                    align-items:center;
                    overflow:hidden;
                    border-radius:5px;
                    margin-right:30px;
                    float:left;

                    img{
                        display:block;
                        max-width:100%;
                        max-height:100%;
                    }
                }

                .simple-info{
                    overflow:auto;
                    margin:auto;
                    height:inherit;
                    position:relative;


                    @button-container-width:10em;
                    .info{
                        width:100%;
                        height:100%;
                        padding-right:@button-container-width;
                        box-sizing:border-box;
                        position:relative;

                        p{
                            margin:0;
                        }

                        @category-name-width:5em;
                        @title-height:2em;
                        .video-title{
                            margin:0;
                            width:100%;
                            display:block;
                            padding:0 15px;
                            box-sizing:border-box;
                            height:@title-height;
                            line-height:@title-height;
                            position:relative;

                            @category-padding:(@title-height - 1)/1.6;
                            .category{
                                max-width:@category-name-width/.8;
                                position:absolute;
                                font-size:.8em;
                                height:(@title-height / .8 * .7);
                                line-height:(@title-height / .8 * .7);
                                padding:0 .75em;
                                top:50%;
                                transform:translateY(-50%);
                                border-radius:2em;
                                color:#666;
                                border:1px solid #e5e9ef;
                            }

                            .inner-title{
                                width:100%;
                                display:inline-block;
                                box-sizing:border-box;
                                padding-left:@category-name-width;

                                a{
                                    font-size:1em;
                                    color:#212121;

                                    &:hover{
                                        color:#00a1d6;
                                    }
                                }
                            }


                        }

                        .time{
                            font-size:.75em;
                            padding:0 15px;
                            box-sizing:border-box;
                            color:#99a2aa;
                            position:absolute;
                            top:50%;
                            transform:translateY(-50%);
                        }

                    }

                    .button-container{
                        width:@button-container-width;
                        height:100%;
                        position:absolute;
                        top:0;
                        right:0;
                        display:flex;
                        justify-content: space-around;
                        align-items: center;
                    }
                }
            }
        }


    }
</style>