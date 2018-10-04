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

                        <el-form-item label="标题">
                            <el-input v-model="form.title"></el-input>
                        </el-form-item>
                        <el-form-item label="标签">
                            <el-input v-model="form.tag"></el-input>
                        </el-form-item>
                        <el-form-item label="简介">
                            <el-input type="textarea" :rows="6" v-model="form.intro"></el-input>
                        </el-form-item>

                        <el-form-item label="加入私密库">
                            <el-switch
                                    v-model="form.private"
                                    active-color="#00A1D6"
                                    inactive-color="#CCD0D7">
                            </el-switch>
                        </el-form-item>

                        <el-form-item label="是否分享">
                            <el-switch
                                    v-model="form.is_share"
                                    active-color="#00A1D6"
                                    inactive-color="#CCD0D7">
                            </el-switch>
                        </el-form-item>

                        <el-form-item label="分享密码" v-if="form.is_share">
                            <el-input v-model="form.share"></el-input>
                        </el-form-item>

                        <el-form-item>
                            <el-button type="primary">立即保存</el-button>
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
    export default {
        name: "VideoUpload",
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
                form:{
                    id:null,
                    title:null,
                    tag:null,
                    intro:null,
                    private:false,
                    is_share:false,
                    share:null
                },
                continue_list:[], // 续传，已上传的片段索引
                upload_token:null,
                test_files:[],
                file_md5:null,
                extname:''
            }
        },
        computed:{
            emptyVideo(){
                return this.$empty(this.file)
            }
        },
        methods:{
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