<template>
    <div class="video-update">

        <el-form ref="form" v-model="form" class="form" label-position="top">

            <div class="part">
                <h3>基本信息</h3>
                <p class="sub-title-setting">视频封面设置</p>

                <div class="thumb-part">
                    <div class="thumb" >
                        <input type="file" id="file" @change="uploadThumb">
                        <img :src="form.thumb || info.screenshoots[0]" alt="" v-if="!$empty(info.screenshoots)">
                        <span>上传封面</span>
                    </div>

                    <div class="more-thumb">

                        <p class="thumb-list-tips">
                            可选封面，系统会默认选中第一张作为视频封面。
                        </p>

                        <ul class="list" v-if="!$empty(info.screenshoots)">


                            <template v-for="item in 5">

                                <li class="list-item" :key="item" v-if="info.screenshoots[item-1]" @click="selectThumb" title="选择这张图片作为封面图">
                                    <img :src="info.screenshoots[item-1]" alt="" >
                                </li>
                                <li class="list-item default-thumb" :key="item" v-else>
                                    <img src="data:image/png;base64,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" alt="">
                                </li>

                            </template>

                        </ul>
                    </div>
                </div>

                <div class="data">

                    <el-form-item label="标题" prop="title">
                        <el-input v-model="form.title"></el-input>
                    </el-form-item>

                    <el-form-item label="标签" prop="tag" >
                        <div>
                            <el-tag
                                    :key="index"
                                    v-for="(tag,index) in form.tag"
                                    closable
                                    :disable-transitions="false"
                                    class="tags"
                                    @close="cancelTag(tag)">
                                {{tag}}
                            </el-tag>
                        </div>
                        <el-input @blur="addTag" @keyup.enter.native="addTag"></el-input>
                    </el-form-item>

                    <el-form-item label="分类" prop="category">
                        <el-cascader
                                :props="{
                                    value:'id',
                                    label:'name',
                                    children:'child'
                                }"
                                :options="category"
                                v-model="form.category">
                        </el-cascader>

                    </el-form-item>

                    <el-form-item label="简介" prop="intro">
                        <el-input
                                type="textarea"
                                :rows="5"
                                v-model="form.intro">
                        </el-input>
                    </el-form-item>

                    <el-form-item label="加入私密库" prop="is_private" class="relate-form-item">
                        <el-switch
                                v-model="form.is_private"
                                class="el-switch"
                                active-color="#00A1D6"
                                inactive-color="#CCD0D7"
                                @change="privateChange">
                        </el-switch>
                    </el-form-item>

                    <el-form-item label="分享" prop="share" class="relate-form-item">
                        <el-switch
                                v-model="is_share"
                                class="el-switch"
                                active-color="#00A1D6"
                                inactive-color="#CCD0D7"
                                :disabled="!!form.is_private">
                        </el-switch>
                        <el-input
                                v-if="is_share"
                                type="text"
                                v-model="form.share">
                        </el-input>
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="submit">保存</el-button>
                    </el-form-item>


                </div>

            </div>



        </el-form>
    </div>
</template>

<script>
    import {mapGetters,mapState} from 'vuex'
    export default {
        name: "VideoUpdate",
        created(){
            this.getInfo()
            this.$store.dispatch('video/getCategory')
        },
        beforeRouteEnter(to, from, next){
            if(!to.query.hasOwnProperty('id')){
                next(from.fullPath || '/')
            }else{
                next()
            }
        },
        data(){
            return {
                submited:false,
                info:{},
                thumb_file:null,
                is_share:false,
                form:{
                    id:null,
                    thumb:null,
                    title:null,
                    tag:[],
                    intro:null,
                    is_private:false,
                    share:null,
                    category:[]
                },
            }
        },
        computed:{
            ...mapGetters('video',[
                'category'
            ]),
            hasInfo(){
                return !this.$empty(this.info)
            },
        },
        methods:{
            async submit(){
                if(this.submited){
                    this.$tips('数据已提交','info')
                    return
                }
                let form = Object.assign({},this.form)
                form.is_private = form.is_private?1:0
                form.is_share = this.is_share?1:0
                const {data} = await this.$http.post('/back/video/updateInfo',form)
                console.log(data)
                if(data.success){
                    this.$success(data.msg || '保存成功')
                    // this.form = {
                    //     id:null,
                    //     thumb:null,
                    //     title:null,
                    //     tag:[],
                    //     intro:null,
                    //     is_private:false,
                    //     share:null,
                    //     category:[]
                    // }
                    // this.$refs.form.resetFields()
                    this.submited = true
                    setTimeout(()=>{
                        this.$router.push({name:'video'})
                    },500)

                }
            },
            // handleCategoryChange(){},
            async getInfo(){
                const {data} = await this.$http.post('/back/video/info',{
                    id:this.$route.query.id
                })
                const info = data.info
                this.info = data.info
                this.is_share = !!info.share
                let category = info.category.split(',')
                category.forEach(function(el,index,arr){
                    arr[index] = parseInt(el)
                })
                this.form = Object.assign({},this.form,{
                    id:info.id,
                    thumb:info.thumb || (this.$empty(info.screenshoots)?null:info.screenshoots[0]),
                    title:info.title,
                    tag:info.tag?new Array(...(info.tag.split(','))):[],
                    intro:info.intro,
                    is_private:info.is_private,
                    share:info.share,
                    category:category
                })
                // console.log(data)
            },
            uploadThumb({target,srcElement}){
                // 仅允许上传JPG PNG
                const el = target || srcElement
                let file = el.files
                if(!file.length){
                    return;
                }
                file = file[0]
                const ext_allow = /^image\/jpe?g|png/.test(file.type)
                console.log(ext_allow)
                if(!ext_allow){
                    this.$tips('封面图只允许上传的格式：JPG,PNG','error')
                    return;
                }
                console.log(file)
                this.thumb_file = file
            },
            selectThumb({target,srcElement}){
                const el = target || srcElement
                this.form.thumb = el.src
            },
            addTag({target,srcElement}){
                const el = target || srcElement
                const value = el.value.trim().replace(/\s*/g,'')
                if(!value || this.form.tag.indexOf(value)>=0){
                    el.value = ''
                    return
                }
                this.form.tag.push(value)
                el.value = ''
            },
            cancelTag(tag){
                if(tag && this.form.tag.indexOf(tag)>=0){
                    this.form.tag.splice(this.form.tag.indexOf(tag),1)
                }

            },
            privateChange(value){
                if(value){
                    this.form.share = null
                    this.is_share = false
                }
            },
            shareSwitchChange(value){
                if(!value){
                    this.form.share = null
                }
            }
        }
    }
</script>

<style scoped lang="less">

    .video-update{
        width:45%;
        margin:0 auto;

        .form{

            .part{

                .sub-title-setting{
                    margin:0;
                    font-size:.85em;
                }


                .thumb-part{
                    margin-top:1em;

                    @thumb-width:15em;
                    @thumb-height:@thumb-width*(1080/1920);
                    .thumb{
                        width:@thumb-width;
                        height:@thumb-height;
                        position:relative;
                        border-radius:5px;
                        overflow:hidden;
                        cursor:pointer;
                        float:left;
                        // margin-right:3.5em;

                        img{
                            position:absolute;
                            top:0;
                            bottom:0;
                            left:0;
                            right:0;
                            margin:auto;
                            display:block;
                            max-width:100%;
                            max-height:100%;
                        }

                        span{
                            position:absolute;
                            right:0;
                            bottom:0;
                            font-size:.6em;
                            padding:3px;
                            background-color:rgba(0,0,0,.45);
                            color:#fff;
                            border-top-left-radius:5px;
                        }

                        #file{
                            width:100%;
                            height:100%;
                            display:block;
                            filter:opacity(0);
                            position:absolute;
                            top:0;
                            left:0;
                            z-index:2;
                            cursor:pointer;
                        }
                    }

                    .more-thumb{
                        height:@thumb-height;
                        margin-left:@thumb-width+3.5em;
                        position:relative;
                        border:1px solid #CCD0D7;
                        border-radius:3px;
                        padding:15px;
                        box-sizing:border-box;

                        .thumb-list-tips{
                            margin:0 0 0 1.5em;
                            line-height:1em;
                            color:#99a2aa;
                            font-size:.7em;
                        }

                        @san-size:15px;
                        .arrow-left{
                            display:block;
                            position:absolute;
                            left:-1px;
                            top:50%;
                            margin:-@san-size/2 0 0 -@san-size/2;
                            width:@san-size;
                            height:@san-size;
                            border:1px solid #CCD0D7;
                            border-top:0;
                            border-right:0;
                            background-color:#fff;
                            transform:rotate(45deg);
                        }

                        .list{
                            width:100%;
                            height:calc(100% - .7em);
                            margin:0;
                            padding:0;
                            overflow:hidden;
                            white-space:nowrap;

                            @list-item-margin:15px;
                            .list-item{
                                display:inline-block;
                                list-style:none;
                                width:calc(100% / 5 - @list-item-margin);
                                height:100%;
                                margin-right:@list-item-margin;
                                position:relative;
                                border-radius:5px;
                                overflow:hidden;
                                cursor:pointer;

                                img{
                                    display:block;
                                    max-width:100%;
                                    max-height:100%;
                                    position:absolute;
                                    top:0;
                                    left:0;
                                    right:0;
                                    bottom:0;
                                    margin:auto;
                                }

                            }

                            .list-item.default-thumb{
                                background-color:#F4F5F7;
                                cursor:default;
                            }
                        }

                        &:after{
                            .arrow-left;
                            content:'';
                        }
                    }
                }

                .data{
                    margin-top:5em;

                    .tags{
                        margin-right:1em;

                        &:last-child{
                            margin-right:0;
                        }
                    }



                    .relate-form-item{
                        position:relative;

                        .el-switch{
                            top: -42px;
                            left:90px;
                            position:absolute;
                            display:inline-block;
                        }
                    }
                }

            }
        }
    }
</style>