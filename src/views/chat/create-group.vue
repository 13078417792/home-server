<template>
    <div class="create-group">

        <el-form class="form" ref="form" :model="form" label-width="80px" label-position="top" size="mini">

            <el-form-item label="群头像">
                <label class="upload" for="avatar">
                    <input type="file" id="avatar" @change="avatarChange">
                    <i class="el-icon-plus"></i>
                    <img v-if="form.thumb || avatarImageUrl" :src="form.thumb || avatarImageUrl" alt="">
                </label>

            </el-form-item>

            <el-form-item label="群组名称">
                <el-input v-model="form.name"></el-input>
            </el-form-item>


            <el-form-item label="选择成员">
                <el-transfer v-model="form.member" :data="member" :props="{key:'uid',label:'username'}"></el-transfer>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="create">创建</el-button>
            </el-form-item>




        </el-form>
    </div>
</template>

<script>
    import {mapState,mapGetters} from 'vuex'
    export default {
        name: "create-group",
        data(){
            return {
                form:{
                    name:'',
                    member:[],
                    thumb:null
                },
                avatarImageUrl:null
            }
        },

        computed:{
            ...mapState('system',{
                member:state=>{
                    return state.user
                }
            })
        },
        created(){

        },
        methods:{
            async avatarChange(e){
                const el = e.srcElement || e.target
                console.log(el,el.files)
                if(el.files.length){
                    const file = el.files[0]
                    let avatarUrl
                    try{
                        avatarUrl = await file.toBase64()
                    }catch(e){
                        throw e
                    }
                    this.avatarImageUrl = avatarUrl
                    let upload = new FormData()
                    upload.append('avatar',file)
                    const {data} = await this.$http.post('/back/chat/uploadGroupAvatar',upload)
                    if(data.success){
                        this.form.thumb = data.avatar
                        console.log(`群组头像上传成功:${data.avatar}`)
                    }else{
                        this.$tips(data.error,'error')
                    }
                }else{
                    this.avatarImageUrl = null
                }

            },
            async create(){
                if(this.validateForm()){
                    const {data} = await this.$http.post('/back/chat/createGroup',this.form)
                    if(data.success){
                        this.$store.dispatch('chat/getGroup')
                        this.$ws.send('chat-group/create',{
                            group:data.createGroup_token
                        })
                        this.$notify({
                            title:'创建成功',
                            message:'创建成功'
                        })
                        if(this.$parent && this.$parent.$options.name==='layer-dialog'){
                            this.$parent.close()
                        }
                        return true
                    }else{
                        this.$notify.error({
                            title:'创建失败',
                            message:data.error
                        })
                        return false
                    }
                }
            },
            validateForm(){
                let form = this.form
                if(this.$empty(form.name)){
                    this.$notify.error({
                        title: '校验失败',
                        message: '必须输入群名称'
                    })
                    return false
                }
                if(this.$empty(form.member)){
                    this.$notify.error({
                        title: '校验失败',
                        message: '没有群成员'
                    })
                    return false
                }
                if(this.$empty(form.thumb)){
                    this.$notify.error({
                        title: '校验失败',
                        message: '必须输上传群头像'
                    })
                    return false
                }

                if(form.name.length>30){
                    this.$notify.error({
                        title: '校验失败',
                        message: '群名称不能超过30个字符'
                    })
                    return false
                }

                if(!/^http|https:\/\/.+/.test(form.thumb)){
                    this.$notify.error({
                        title: '校验失败',
                        message: '群头像不合法'
                    })
                    return false
                }
                return true

            }
        }
    }
</script>

<style scoped lang="less">

    .create-group{
        width:100%;
        height:100%;

        .form{
            width:80%;
            margin: auto;
            padding:15px 0;
            box-sizing:border-box;

            @upload-size:100px;
            .upload{
                display:block;
                width:@upload-size;
                height:@upload-size;
                border-radius:6px;
                border: 1px dashed #d9d9d9;
                cursor: pointer;
                overflow: hidden;
                position:relative;

                img{
                    display:block;
                    width:100%;
                    height:100%;
                }

                #avatar{
                    position:absolute;
                    top:0;
                    left:0;
                    display:block;
                    width:100%;
                    height:100%;
                    opacity:0;
                    cursor:pointer;
                    font-size:0;
                    z-index:-1;
                }

                &:hover{
                    border-color: #409EFF;
                }

                .el-icon-plus{
                    position:absolute;
                    width:1em;
                    height:1em;
                    line-height:1em;
                    text-align:center;
                    margin:50% 0 0 50%;
                    transform:translate(-50%,-50%);
                    color: #8c939d;
                    z-index:-1;
                }

            }

        }
    }
</style>