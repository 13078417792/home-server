<template>
    <div class="video-category">

        <el-form class="form" ref="form" :model="form" :rules="rules" label-position="top">

            <el-form-item label="分类名" prop="name">
                <el-input v-model="form.name"></el-input>
            </el-form-item>

            <el-form-item label="别名" prop="alias">
                <el-input v-model="form.alias"></el-input>
            </el-form-item>

            <el-form-item label="父分类" prop="pid">
                <el-cascader
                        :props="{
                            value:'id',
                            label:'name',
                            children:'child'
                        }"
                        :options="category"
                        :value="cascader"
                        :change-on-select="true"
                        @change="handleCategoryChange">
                </el-cascader>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="submit">添加分类</el-button>
            </el-form-item>

        </el-form>
    </div>
</template>

<script>
    import {mapGetters,mapState} from 'vuex'
    export default {
        name: "VideoCategoryAdd",
        data(){
            return {
                form:{
                    name:null,
                    pid:0,
                    alias:null
                },
                cascader:[],
                rules:{
                    name:[
                        { required: true, message: '请输入分类名', trigger: 'blur' },
                        { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
                    ],
                    pid:[
                        // { required: true, message: '请选择父分类', trigger: 'blur' },
                    ],
                    alias:[
                        { required: true, message: '请输入别名', trigger: 'blur' },
                        { min: 2, max: 30, message: '长度在 2 到 30 个字符', trigger: 'blur' },
                        { validator(rule, value, callback){
                            if(/[a-zA-Z-]+/.test(value)){
                                callback()
                            }else{
                                callback(new Error('别名只允许输入大小写英文字符和横杠'));
                            }
                            } , trigger: 'blur' },
                    ],
                },
                // category:[]
            }
        },
        computed:{
            ...mapGetters('video',[
                'category'
            ]),
        },
        created(){
            this.$store.dispatch('video/getCategory')
        },
        methods:{
            handleCategoryChange(value){
                if(value.length){
                    this.form.pid = value[value.length-1]
                }
            },
            submit(){
                this.$refs.form.validate(async valid=>{
                    // if(valid){
                        const {data} = await this.$http.post('/back/video/addCategory',this.form)
                        console.log(data)
                        if(data.success){
                            this.$success(data.msg || '添加成功')
                            this.$store.dispatch('video/getCategory')
                            this.form = {
                                name:null,
                                pid:0,
                                alias:null
                            }
                            this.cascader = []
                        }
                    // }
                })
            },
            async getCategory(){
                let {data} = await this.$http.post('/back/video/category')
                console.log(data)
                this.category = new Array(...(data.result))
            }
        }
    }
</script>

<style scoped lang="less">

    .form{
        width:45%;
        margin:0 auto;
    }
</style>