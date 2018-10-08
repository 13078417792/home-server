<template>
    <div class="user-add">
        <div class="add-form">
            <el-form ref="add" :model="form" :rules="rules" label-width="80px" size="small">
                <el-form-item label="用户名" prop="username">
                    <el-input v-model="form.username"></el-input>
                </el-form-item>
                <el-form-item label="密码" class="form-item" prop="password">
                    <el-input type="password" v-model="form.password"></el-input>
                    <span class="tips">默认123456</span>
                </el-form-item>
                <el-form-item label="角色" prop="role" >
                    <el-checkbox-group
                            v-model="form.role">
                        <el-checkbox v-for="item in role" :label="item.id" :key="item.id">{{item.name}}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="add">添加</el-button>
                    <el-button type="info" @click="reset">重置</el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Add",
        data(){
            return {
                form:{
                    username:'',
                    password:'',
                    role:[],
                },
                role:[],
                rules:{
                    username:[
                        { required: true, message: '请输入用户名', trigger: 'blur' },
                        { min:2,max:15,message: '用户名长度在 2 到 15 个字符', trigger: 'blur' }
                    ],
                    password:[
                        { min:5,max:25,message: '密码长度在 5 到 25 个字符', trigger: 'blur' }
                    ],
                    role:[
                        { required: true, message: '请选择角色', trigger: 'blur' },
                        { validator(rule, value, callback){
                                if(Array.isArray(value)){
                                    callback()
                                }else{
                                    callback(new Error('角色数据错误'))
                                }
                            },trigger: 'blur'}
                    ]
                }
            };
        },
        created(){
            this.getRoleList();
        },
        methods:{
            async getRoleList(){
                const {data} = await this.$http.post('/back/auth/getRoleList');
                if(data.success){
                    this.role = data.role_list;
                }
            },
            add(){
                this.$refs.add.validate(async valid=>{
                    if(valid){
                        const {data} = await this.$http.post('/back/auth/addUser',this.form);
                        if(data.success){
                            this.$message({
                                message:data.msg || '添加成功',
                                type:'success'
                            });
                            this.$refs.add.resetFields();
                        }else{
                            this.$message.error(data.error || data.msg || '添加失败');
                        }
                    }
                });
            },
            reset(){
                this.$refs.add.resetFields();
            }
        }
    }
</script>

<style scoped lang="less">
    .user-add{

        @width:30em;
        .add-form{
            margin-top:3em;
            width:@width;

            .form-item{
                position:relative;

                @tips-left:@width*.8em;
                .tips{
                    margin:0;
                    font-size:.8em;
                    color:#FF5722;
                    position:absolute;
                    top:0;
                    left:calc(100% + 1em);
                    width:auto;
                    white-space: nowrap;
                }
            }
        }
    }
</style>