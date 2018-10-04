<template>
    <div class="login">
        <div class="form" :class="{top:transition_top}" @keyup.enter="login" v-loading="submitting"element-loading-text="登录中"
             element-loading-spinner="el-icon-loading"
             element-loading-background="rgba(0, 0, 0, 0.8)">
            <div class="input">
                <label for="username">用户名</label>
                <input id="username" type="text" v-model="form.username">
            </div>
            <div class="input">
                <label for="password">密码</label>
                <input id="password" type="password" v-model="form.password">
            </div>

            <div class="submit">
                <button @click="login">登录</button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "login",
        data(){
            return {
                form:{
                    username:'',
                    password:''
                },
                submitting:false,
                transition_top:false
            }
        },
        mounted(){
            setTimeout(()=>{
                this.transition_top = true;
            },100);
        },
        methods:{
            async login(){
                if(this.submitting){
                    return false;
                }
                this.submitting = true;
                const {data} = await this.$http.post('/back/login/login',this.form).catch(()=>{
                    this.submitting = false;
                });
                // console.log(data);
                if(data.success){
                    this.$message({
                        showClose: true,
                        message: '登陆成功!跳转中……',
                        type: 'success',
                        duration:850,
                        onClose:()=>{
                            this.$cookie.set('auth_token',data.auth_token,{expires:data.expires+'s'});
                            this.$router.push('/');
                        }
                    });
                }else{
                    await this.$alert(data.error || data.msg || '登录失败');
                    this.submitting = false;
                }
            }
        }
    }
</script>

<style scoped lang="less">

    .login{
        width:100%;
        min-height:100%;
        background-color:#1782dd;
        display:flex;
        justify-content:center;
        align-items:start;

        @padding:50px;
        .form{
            display:block;
            padding:@padding;
            background-color:rgba(255,255,255,.1);
            box-shadow:0 0 5px rgba(0,0,0,.2);
            margin-top:0;
            border-radius:3px;
            font-size:1rem;
            transform:translate3d(0,0,0);
            transition:all .5s ease;

            .input{
                margin-bottom:@padding/2;
                position:relative;

                label{
                    font-size:1em;
                    width:@labelWidth;
                    position:absolute;
                    top:1px;
                    left:1px;
                    height:1em;
                    line-height:1em;
                    padding:15px 0;
                    color:#fff;
                    text-align:center;
                }

                @labelWidth:5em;
                input[type="password"],input[type="text"]{
                    height:1em;
                    font-size:1em;
                    width:15em + @labelWidth;
                    outline:none;
                    padding:15px 15px 15px @labelWidth;
                    border-radius:3px;
                    /*background-color:#236cb7;*/
                    background-color:rgba(0,0,0,.2);
                    border:1px solid #7aa9d8;
                    transition:all .3s;
                    color:#fff;

                    &:hover,&:focus{
                        border-color:#aec2d6;
                    }

                }


                &:last-child{
                    margin:0;
                }
            }

            .submit{

                button{
                    color:#fff;
                    border:0;
                    background-color:#1464a9;
                    width:100%;
                    padding:15px 0;
                    cursor:pointer;
                    outline:none;
                }
            }

        }

        .form.top{
            margin-top:15em;
        }
    }
</style>