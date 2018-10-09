<template>
    <div class="video-upload">

        <div class="upload-main">

            <!-- 拖动、按钮上传 -->
            <div class="drag-btn--upload"  @dragenter.prevent @dragleave.prevent @dragover.prevent @drop.prevent="drop" v-if="emptyVideo">

                <div class="click-btn-upload">
                    <label for="video">上传视频</label>
                    <input type="file" id="video" @change="video">
                </div>

                <span class="tips">
                    拖曳视频到此处也可上传
                </span>
            </div>

            <!-- 上传进度 + 视频信息表单 -->
            <div class="upload-progress-form" v-else>

                <div class="part">
                    <h4>文件上传进度</h4>
                    <div class="progress-container" >
                        <p class="video-name sl">
                            {{file.name}}

                            <span class="percent">{{parseInt(uploadInfo.percent*100)+'%'}}</span>
                        </p>

                        <div class="progress">
                            <span class="upload-info" v-if="uploadStatus!=='upload'">
                                {{uploadStatusList[uploadStatus]}}
                            </span>
                            <span class="upload-info" v-else>已经上传：{{uploadInfo.uploadedSize}}/{{uploadInfo.totalSize}}</span>
                            <div class="uploaded" :style="{width:uploadInfo.percent?(uploadInfo.percent)*100+'%':''}"></div>
                        </div>
                    </div>
                </div>

                <div class="part">
                    <h4>基本信息</h4>

                    <el-form class="info-form" label-position="top" :model="form">
                        <input type="hidden" v-model="form.id">

                        <el-form-item label="视频封面设置">

                            <div class="thumb-part">
                                <div class="thumb" >
                                    <input type="file" id="file" @change="uploadThumb">
                                    <img :src="form.thumb || screenshoots[0] || thumb_dataurl" alt="" v-if="!!form.thumb || !$empty(screenshoots) || thumb_dataurl">
                                    <img v-else src="data:image/png;base64,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" alt="">
                                    <span>上传封面</span>
                                </div>

                                <div class="more-thumb">

                                    <p class="thumb-list-tips">
                                        <template v-if="form.id && !$empty(screenshoots)">
                                            可选封面，系统会默认选中第一张作为视频封面。
                                        </template>
                                        <template v-else>
                                            等待上传完毕后生成封面图
                                        </template>

                                    </p>

                                    <ul class="list" v-if="!$empty(screenshoots)">


                                        <template v-for="item in 5">

                                            <li class="list-item" :key="item" v-if="screenshoots[item-1]" @click="selectThumb" title="选择这张图片作为封面图">
                                                <img :src="screenshoots[item-1]" alt="" >
                                            </li>
                                            <li class="list-item default-thumb" :key="item" v-else>
                                                <img src="data:image/png;base64,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" alt="">
                                            </li>

                                        </template>

                                    </ul>
                                </div>
                            </div>
                        </el-form-item>

                        <el-form-item label="标题">
                            <el-input v-model="form.title"></el-input>
                        </el-form-item>


                        <el-form-item label="标签">
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


                        <el-form-item label="简介">
                            <el-input type="textarea" :rows="6" v-model="form.intro"></el-input>
                        </el-form-item>

                        <el-form-item label="加入私密库" class="form-item">
                            <el-switch
                                    class="el-switch"
                                    v-model="form.is_private"
                                    active-color="#00A1D6"
                                    inactive-color="#CCD0D7"
                                    @change="privateChange">
                            </el-switch>
                        </el-form-item>

                        <el-form-item label="是否分享" class="form-item">
                            <el-switch
                                    class="el-switch"
                                    v-model="form.is_share"
                                    active-color="#00A1D6"
                                    :disabled="!form.is_private"
                                    inactive-color="#CCD0D7"
                                    @change="isShareChange">
                            </el-switch>

                            <el-input v-if="form.is_share" v-model="form.share"></el-input>
                        </el-form-item>

                        <el-form-item>
                            <el-button type="primary" @click.stop="submit">立即保存</el-button>
                        </el-form-item>
                    </el-form>

                </div>

            </div>

        </div>
    </div>
</template>

<script>
    import cutWorker from '@/workers/video/cut.worker'
    import md5Worker from '@/workers/video/md5-chunk.worker'
    import {mapGetters} from 'vuex'
    export default {
        name: "VideoUpload",
        created(){
            this.$store.dispatch('video/getCategory')
        },
        data(){
            return {
                file:null,
                chunk:[], // 文件片段集合
                uploadStatus:'ready',
                uploadStatusList:{
                    upload:'正在上传',
                    md5:'校验MD5中...',
                    ready:'处理数据中...准备上传',
                    stop:'已暂停',
                    finished:'上传完毕'
                },
                uploadBarWord:'等待上传',
                uploadInfo:{
                    blockIndex:0, // 正在上传的视频片段的索引
                    percent:0, // 上传进度
                    uploadedSize:0, // 已上传文件的大小
                    totalSize:0 // 文件总大小
                },
                screenshoots:[], // 视频截图，视频上传完成后轮询获取截图
                form:{
                    id:null,
                    title:null,
                    tag:[],
                    intro:null,
                    is_private:false,
                    is_share:false,
                    share:null,
                    category:[],
                    thumb:null
                },
                thumb_dataurl:null,
                continue_list:[], // 续传，已上传的片段索引
                upload_token:null,
                test_files:[],
                file_md5:null,
                extname:''
            }
        },
        computed:{
            ...mapGetters('video',[
                'category'
            ]),
            emptyVideo(){
                return this.$empty(this.file)
            }
        },
        methods:{
            selectThumb(){},
            async uploadThumb({target,srcElement}){
                const el = target || srcElement
                let file = el.files
                if(!file.length){
                    return
                }
                file = file[0]
                console.log(file)
                if(!/^image\/(jpe?g|png)$/.test(file.type)){
                    this.$tips('不是图片，支持JPG,PNG格式','error')
                    return
                }
                if(file.size > 2*1024*1024){
                    this.$tips('文件太大，不能超过2M','error')
                    return
                }

                let reader = new FileReader()
                reader.addEventListener('load',()=>{
                    this.thumb_dataurl = reader.result
                })

                reader.readAsDataURL(file)

                let form = new FormData()
                form.append('thumb',file)
                const {data} = await this.$http.post('/back/video/uploadThumb',form)
                if(data.success){
                    console.log(data)
                    this.form.thumb = data.thumb
                }
            },
            async submit(){
                if(!this.form.id){
                    this.$tips('等待上传完毕后再提交视频信息','info')
                    return
                }
                const {data} = await this.$http.post('/back/video/updateInfo',this.form)
                if(data.success){
                    this.$success(data.msg || '操作成功')
                    this.$router.push({name:'video'})
                }
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
                if(!value){
                    this.form.is_share = false;
                    this.form.share = null
                }
            },
            isShareChange(value){
                if(value){
                    this.form.share = null
                }
            },
            // 拖曳上传
            drop(e){
                const files = e.dataTransfer.files
                if(!files.length){
                    return false
                }
                const file = files[0]
                this.handleFileAndUpload(file)
            },

            // 选择文件上传
            video({srcElement,target}){
                const el = srcElement || target,
                    file = el.files[0]
                this.handleFileAndUpload(file)
            },

            // 公用方法，切割大文件，分片上传
            handleFileAndUpload(file){
                if( !(file
                    && file instanceof File
                    && /^video/.test(file.type))
                ){
                    this.$tips('文件类型不是视频或文件列表为空','error')
                    return false
                }

                const self = this
                this.file = file
                this.extname = file.name.replace(/.+\.(.+)$/,'$1')
                this.form.title = file.name.replace(/\..+$/,'')
                this.uploadInfo.totalSize = (file.size / Math.pow(1024,2)).toFixed(2) + 'MB' // 文件总大小

                const cut = new cutWorker()
                cut.postMessage({
                    file:file
                })

                // 切割文件
                this.$workerMsg(cut).then(({data})=>{
                    const md5 = new md5Worker()
                    if(!data.files.length){
                        this.chunk = []
                        return Promise.reject('没有文件');
                    }
                    this.chunk = new Array(...data.files)

                    // 切换页面显示状态至校验MD5
                    this.uploadStatus = 'md5'

                    md5.postMessage({
                        files:data.files
                    })
                    return this.$workerMsg(md5)
                }).then(async event=>{
                    // 开始计算MD5

                    const md5 = event.data.md5
                    this.file_md5 = md5

                    // return;
                    const {data} = await this.$http.post('/back/video/beforeUploadCheck',{
                        md5
                    })
                    if(!data.success){
                        return Promise.reject(data.error || data.msg)
                    }
                    this.upload_token = data.token

                    // return false;
                    if(!!data.quick===true && !!data.continue===false){
                        this.uploadInfo = {
                            blockIndex:this.chunk.length-1,
                            percent:1, // 上传进度
                            uploadedSize:this.uploadInfo.totalSize, // 已上传文件的大小
                        }
                        this.uploadStatus = 'finished'
                        this.getScreenShoots()
                        this.$success('秒传完成')
                        return ;
                    }

                    this.continue_list = new Array(...(data.list || []))

                    // 切换状态至上传中
                    this.uploadStatus = 'upload'
                    this.$tips('校验完成，开始上传!','info')


                    this.eachUpload(this.chunk)
                }).catch(err=>{
                    this.$tips(err,'error')
                })
            },

            // 下一个片段的进度数据
            nextChunkProcess(){
                let blockIndex = this.uploadInfo.blockIndex+1,
                    percent = blockIndex / this.chunk.length
                percent = percent.toFixed(3)
                this.uploadInfo = Object.assign({},this.uploadInfo,{
                    blockIndex:blockIndex,
                    percent:percent,
                    uploadedSize:(percent * this.file.size / Math.pow(1024,2)).toFixed(2) + 'MB'
                })
            },
            // 分片上传
            eachUpload(fileList,start=0){
                if(start<fileList.length){
                    this.uploadStatus = 'upload'
                    if(this.continue_list.includes(start+'.'+this.extname)){
                        this.nextChunkProcess()
                        this.eachUpload(fileList,start+1)
                        return ;
                    }
                    this.upload(fileList,null,start).then(data=>{
                        if(!data.success){
                            this.$error(data.error || data.msg || '上传失败')
                            return false
                        }
                        this.nextChunkProcess()
                        this.eachUpload(fileList,start+1)
                    })
                }else{
                    this.uploadStatus = 'finished'
                    this.getScreenShoots()
                    this.$success('上传完成')
                }
            },
            // 上传
            upload(data,name,index){
                const self = this
                if(!index){
                    index = 0
                }
                let upload = new FormData()
                upload.append('file',data[index])
                upload.append('unique',this.file_md5)
                upload.append('token',this.upload_token)
                upload.append('index',index)
                upload.append('end',data.length-1)
                return new Promise(function(resolve,reject){
                    self.$http.post('/back/video/upload3',upload).then(function({data}){
                        if(data.success){
                            resolve(data)
                        }else{
                            reject(data.error || data.msg || '上传失败')
                        }
                    }).catch(function(e){
                        reject(e)
                    })
                })

            },
            async getScreenShoots(){
                const {data} = await this.$http.post('/back/video/getScreenshoots',{
                    video:this.file_md5
                })
                if(data.finished){
                    this.screenshoots = new Array(...data.screenshoots)
                    if(!this.form.thumb || !/^http/.test(this.form.thumb)){
                        this.form.thumb = this.screenshoots[0]
                    }
                }else{
                    setTimeout(this.getScreenShoots,1500)
                }
            }
        }
    }
</script>

<style scoped lang="less">
    .video-upload{

        .upload-main{
            width:45%;
            margin:0 auto;

            .upload-progress-form{
                width:100%;

                .part{
                    margin-top:5em;

                    &:first-child{
                        margin-top:0;
                    }



                    .info-form{

                        .tags{
                            margin-right:1em;

                            &:last-child{
                                margin-right:0;
                            }
                        }

                        .form-item{
                            position:relative;

                            .el-switch{
                                position:absolute;
                                top:-42px;
                                left:90px;
                            }

                        }
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


                }

                .progress-container{
                    font-size:.8em;
                    width:100%;

                    .video-name{
                        margin:0;
                        height:2em;
                        line-height:2em;
                        padding-right:5em;
                        box-sizing:border-box;
                        position:relative;

                        .percent{
                            position:absolute;
                            top:0;
                            right:.2em;
                            color:#A8A6B5;
                        }
                    }

                    @upload-info-height:1em;
                    .progress{
                        margin-top:@upload-info-height+1em;
                        width:100%;
                        height:3px;
                        background-color:#EAEAEA;
                        position:relative;

                        /* 已上传进度条 */
                        .uploaded{
                            position:absolute;
                            top:0;
                            left:0;
                            height:100%;
                            background-color:#00A1D6;
                        }

                        .upload-info{
                            font-size:.9em;
                            position:absolute;
                            top:-1*(@upload-info-height+.5em)/.9;
                            left:0;
                            color:#A8A6B5;
                        }
                    }
                }
            }

            @dragSize:15em;
            .drag-btn--upload{
                max-width:100%;
                margin:0 auto;
                width:@dragSize;
                height:@dragSize;
                border:1px dashed #ccc;
                border-radius: @dragSize*.05;
                cursor:default;
                position:relative;
                user-select:none;

                @btn-height:2em;
                @btn-width:6em;
                .click-btn-upload{
                    position:relative;
                    margin-top:50%;
                    margin-left:50%;
                    top:@btn-height / -2;
                    left:@btn-width / -2;
                    width:@btn-width;
                    height:@btn-height;
                    line-height:@btn-height;
                    border-radius:@btn-height / 2;
                    background-color:#00A1D6;
                    color:#fff;
                    text-align:center;
                    cursor:pointer;
                    overflow:hidden;

                    label{
                        font-size:.85em;
                        cursor:pointer;
                        width:100%;
                        height:100%;
                        display:block;
                    }

                    input[type="file"]{
                        position:absolute;
                        top:0;
                        left:0;
                        visibility:hidden;
                    }
                }

                span.tips{
                    width:100%;
                    text-align:center;
                    font-size:.85em;
                    position:absolute;
                    bottom:5em;
                    color:#ccc;
                }

                &:hover{
                    border-color:#409EFF;
                }
            }
        }
    }
</style>