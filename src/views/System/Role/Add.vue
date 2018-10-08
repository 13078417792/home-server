<template>
    <div class="role-add">
        <div class="add-form">
            <el-form ref="add" :model="form" :rules="rules" label-width="80px" size="small">
                <el-form-item label="角色名" prop="name">
                    <el-input v-model="form.name"></el-input>
                </el-form-item>
                <el-form-item label="角色权限" prop="access">
                    <div class="access-group" v-for="(item,key,index) in access" v-if="item.length" :key="index">
                        <p class="access-type">{{key}}</p>
                        <div class="access-list" >
                            <el-checkbox-group v-model="form.access">
                                <el-checkbox class="access-item" v-for="(sub,subIndex) in item" :key="subIndex" :label="key+'-'+sub.id" >{{sub.name}}</el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="add">添加</el-button>
                    <el-button type="info" @click="$refs.add.resetFields()">重置</el-button>
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
                    name:'',
                    access:[]
                },
                access_list:[],
                rules:{
                    name:[
                        { required: true, message: '必须输入角色名', trigger: 'blur' },
                        { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
                    ],
                    access:[
                        { type: 'array', required: true, message: '必须选择角色权限', trigger: 'blur' }
                    ],
                }
            };
        },
        created(){
            this.getAccessList();
        },
        mounted(){

        },
        computed:{
            access(){
                let access = {};
                this.access_list.forEach(ele=>{
                    if(!access.hasOwnProperty(ele.type)){
                        access[ele.type] = [];
                    }
                    access[ele.type].push(ele);
                });
                return access;
            },
            postAccess(){
                let access = {};
                this.form.access.forEach(el=>{
                    const ele = el.split('-');
                    if(!access.hasOwnProperty(ele[0])){
                        access[ele[0]] = [];
                    }
                    access[ele[0]].push(parseInt(ele[1]));
                });
                return access;
            }
        },
        methods:{
            async add(){

                // console.log(data);

                this.$refs.add.validate(async valid=>{
                    if(valid){
                        const post = Object.assign({},this.form,{
                            access:this.postAccess,
                        });
                        const {data} = await this.$http.post('/back/auth/addRole',post);
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
            async getAccessList(){
                const {data} = await this.$http.post('/back/auth/getAccessList');
                this.access_list = data.access_list;
            }
        }
    }
</script>

<style scoped lang="less">
    .role-add{

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

            .access-group{

                .access-type{
                    margin:0;
                    padding-left:10px;
                    box-sizing:border-box;
                    border-left:3px solid #009688;
                    background-color:#f2f2f2;
                }

                .access-list{
                    padding-left:1em;
                    box-sizing:border-box;

                    .access-item{
                        width:50%;
                        display:inline-block;
                        margin:0;
                    }
                }
            }
        }
    }
</style>