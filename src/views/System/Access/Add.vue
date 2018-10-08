<template>
    <div class="access-add">
        <el-form  ref="form" :model="form" :rules="rules" class="form" size="mini" label-width="80px">
            <el-form-item label="权限名称" prop="name">
                <el-input v-model="form.name"></el-input>
            </el-form-item>
            <el-form-item label="权限类型" prop="type">
                <el-radio v-model="form.type" :label="item.value" v-for="(item,index) in type_list" @change="type_change" :key="index">{{item.name}}</el-radio>
            </el-form-item>
            <el-form-item label="路径" prop="path">
                <el-input v-model="form.path"></el-input>
            </el-form-item>
            <el-form-item label="备选接口" v-if="form.type==='api'">
                <template v-for="(item,index) in api">
                    <div class="api-group" v-if="item.length > 0">
                        <p class="controller">{{index}}</p>
                        <div class="api-list">
                            <template v-for="iem in item">
                                <el-radio v-if="apiArray.includes('/'+index+'/'+iem)" disabled :label="'/'+index+'/'+iem" @click.native="$message.error('此接口已被添加')">{{iem}}</el-radio>
                                <el-radio v-else v-model="form.path" :label="'/'+index+'/'+iem">{{iem}}</el-radio>
                            </template>
                        </div>
                    </div>
                </template>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="submit" v-if="!submiting">添加</el-button>
                <el-button type="primary" @click="submit" v-else disabled>添加</el-button>
                <el-button type="info" @click="$refs.form.resetFields()">重置</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    export default {
        name: "Add",
        data(){

            const self = this;
            return {
                submiting:false,
                api_list:{},
                form:{
                    name:'',
                    type:'api',
                    path:''
                },
                type_list:[
                    {
                        value:'api',
                        name:'接口类型'
                    },{
                        value:'views',
                        name:'视图类型'
                    },{
                        value:'extend',
                        name:'扩展类型'
                    }
                ],
                rules:{
                    name:[
                        { required: true, message: '必须输入权限名称', trigger: 'blur' },
                        { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
                    ],
                    path:[
                        { required: true, message: '必须输入路径', trigger: 'blur' },
                        { min: 3, max: 255, message: '长度在 3 到 255 个字符', trigger: 'blur' }
                    ],
                    type:[
                        { required: true, message: '必须选择权限类型', trigger: 'blur' },
                        { validator(rule,value,callback){
                            if(['api','views','extend'].includes(value)){
                                callback();
                            }else{
                                callback(new Error('不存在的权限类型'))
                            }
                            }, trigger: 'blur' }
                    ]
                },
                api:[]
            }
        },
        created(){
            if(this.form.type==='api'){
                this.getApiList();
            }
            this.getApiAccessList();
        },
        computed:{
            apiArray(){
                let arr = [];
                for(let i in this.api_list){
                    arr.push(this.api_list[i].path);
                }
                return arr;
            }
        },
        methods:{
            async getApiAccessList(){
                const {data} = await this.$http.post('/back/auth/getApiAccessList');
                this.api_list = data.access_list;
            },
            async getApiList(){
                const {data} = await this.$http.post('/back/auth/getApiList');
                if(data.success){
                    this.api = data.list;
                }
            },
            type_change(v){
                if(v==='api'){
                    this.getApiList();
                }else{
                    this.form.path = ''
                }
            },
            submit(){
                this.submiting = true;
                this.$refs.form.validate(async valid=>{
                    if(valid){
                        const {data} = await this.$http.post('/back/auth/addAccess',this.form);
                        if(data.success){
                            this.$message({
                                message:'添加成功',
                                type:'success'
                            });
                            this.getApiAccessList();
                            this.$refs.form.resetFields();
                            this.submiting = false;
                        }else{
                            this.$message.error(data.error || data.msg || '添加失败');
                            this.submiting = false;
                        }
                    }
                });
            }

        }
    }
</script>

<style scoped lang="less">

    .access-add{

        .form{
            margin-top:1em;
            width:30em;

            .api-group{

                .controller{
                    margin:0;
                    padding-left:10px;
                    box-sizing:border-box;
                    border-left:3px solid #009688;
                    background-color:#f2f2f2;
                }

                .api-list{
                    width:100%;
                    padding-left:1em;
                    box-sizing:border-box;

                    label{
                        display:inline-block;
                        width:50%;
                        margin-left:0;
                    }
                }
            }
        }
    }
</style>