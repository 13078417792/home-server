<template>
    <div class="access">
        <div class="area">
            <el-button type="primary" @click="$router.push({name:'access-add'})" size="mini">添加</el-button>
        </div>

        <div class="table">
            <el-table
                    :data="access_list"
                    border
                    style="width: 100%">
                <el-table-column
                        fixed
                        prop="id"
                        label="ID"
                        width="100">
                </el-table-column>
                <el-table-column
                        fixed
                        prop="name"
                        label="权限名称"
                        width="300">
                </el-table-column>
                <el-table-column
                        prop="path"
                        label="路径"
                        width="300">
                </el-table-column>
                <el-table-column
                        label="权限类型"
                        width="300">
                    <template slot-scope="scope">
                        <span>{{type[scope.row.type]}}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="add_time_fmt"
                        label="添加时间"
                        width="200">
                </el-table-column>
                <el-table-column
                        prop="update_time"
                        label="更新时间"
                        width="300">
                </el-table-column>
                <el-table-column
                        label="状态"
                        width="300">
                    <template slot-scope="scope">
                        <span v-if="parseInt(scope.row.status)===1">可用</span>
                        <span v-else>禁用</span>
                    </template>
                </el-table-column>
                <el-table-column
                        fixed="right"
                        label="操作"
                        width="auto">
                    <template slot-scope="scope">
                        <a class="action-btn el-icon-error" title="封禁" v-if="parseInt(scope.row.status)===1" @click="updateStatus(scope.row.id,0)"></a>
                        <a class="action-btn el-icon-success" title="启用" v-else @click="updateStatus(scope.row.id,1)"></a>
                        <a class="action-btn el-icon-delete" title="删除此权限" @click="deleteAccess(scope.row.id,scope.row.name || '')"></a>
                        <a class="action-btn el-icon-edit-outline" title="修改权限信息" @click="expandUpdateAccess(scope.row.id)"></a>
                        <a class="action-btn el-icon-view" title="查看详细信息" @click="viewAccessDetail(scope.row.id)"></a>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <div class="edit" v-if="edit.id">
            <el-form ref="edit" :model="edit" label-width="80px" size="mini">
                <el-form-item label="权限名称">
                    <el-input v-model="edit.name"></el-input>
                </el-form-item>
                <el-form-item label="权限路径">
                    <el-input v-model="edit.path"></el-input>
                </el-form-item>
                <el-form-item label="权限类型">
                    <el-radio-group v-model="edit.type">
                        <el-radio :label="key" v-for="(item,key,index) in type" :key="index" @change="editTypeChange">{{item}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="备选接口" v-if="edit.type==='api'">
                    <template v-for="(item,index) in api">
                        <div class="api-group" v-if="item.length > 0">
                            <p class="controller">{{index}}</p>
                            <div class="api-list">
                                <template v-for="iem in item">
                                    <el-radio v-if="apiAccessPathArray.includes(`/${index}/${iem}`) && edit.path!=='/'+index+'/'+iem" disabled :label="`/${index}/${iem}`" @click.native="$message.error('此接口已被添加')">{{iem}}</el-radio>
                                    <el-radio v-else v-model="edit.path" :label="`/${index}/${iem}`">{{iem}}</el-radio>
                                </template>
                            </div>
                        </div>
                    </template>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="updateAccess">修改</el-button>
                    <el-button type="info" @click="hideUpdateAccess">收起</el-button>
                </el-form-item>
            </el-form>
        </div>

        <div class="view" v-if="viewAccessDetailStatus && accessDetail">
            <el-form label-width="80px" size="mini">
                <el-form-item label="权限ID">
                    {{accessDetail.id}}
                </el-form-item>
                <el-form-item label="权限名称">
                    {{accessDetail.name}}
                </el-form-item>
                <el-form-item label="权限路径">
                    {{accessDetail.path}}
                </el-form-item>
                <el-form-item label="权限类型">
                    {{type[accessDetail.type]}}
                </el-form-item>
                <el-form-item label="添加时间">
                    {{accessDetail.add_time_fmt}}
                </el-form-item>
                <el-form-item label="修改时间">
                    {{accessDetail.update_time}}
                </el-form-item>
                <el-form-item label="权限状态">
                    {{accessDetail.status==1?'可用':'禁用'}}
                </el-form-item>
                <el-form-item>
                    <el-button type="info" @click="hideViewAccessDetail">收起</el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    export default {
        name: "Access",
        data(){
            return {
                access_list:[],
                type:{
                    api:'接口',
                    views:'视图',
                    extend:'扩展'
                },
                edit:{
                    id:null,
                    name:'',
                    type:'api',
                    path:''
                },
                viewAccessDetailStatus:false
            }
        },
        created(){
            this.getAccessList()
        },
        methods:{
            async getAccessList(){
                const {data} = await this.$http.post('/back/auth/getAccessList');
                this.access_list = data.access_list;
                this.hideUpdateAccess()
                this.hideViewAccessDetail()
            },
            async updateStatus(id,status){
                const statusArr = ['封禁','启用'];
                try{
                    await this.$confirm(`确定要${statusArr[status]}此权限吗`,'更新权限状态');
                    const {data} = await this.$http.post('/back/auth/updateAccessStatus',{
                        id:id,
                        status:status
                    });
                    if(data.success){
                        this.getAccessList();
                        this.$message({
                            message:data.msg,
                            type:'success',
                        })
                    }else{
                        this.$message.error(data.error || data.msg || '操作失败')
                    }
                }catch(e){}
            },
            async deleteAccess(id,name){
                try{
                    await this.$confirm(`确定要删除权限[${name || ''}]吗`,'删除权限');
                    const {data} = await this.$http.post('/back/auth/deleteAccess',{
                        id:id
                    });
                    if(data.success){
                        this.getAccessList();
                        this.$message({
                            message:data.msg,
                            type:'success',
                        })
                    }else{
                        this.$message.error(data.error || data.msg || '操作失败')
                    }
                }catch(e){}
            },
            editTypeChange(e){
                if(e.type==='api'){
                    if(this.$empty(this.api)){
                        this.$dispatch('system/api')
                    }
                }
            },
            hideUpdateAccess(){
                this.edit = {
                    id:null,
                    name:'',
                    type:'api',
                    path:''
                }
            },
            updateAccess(){
                this.$http.post('/back/auth/updateAccess',this.edit).then(({data})=>{
                    if(data.success){
                        this.$success(data.msg || '修改成功')
                        this.getAccessList();
                    }
                })
            },
            async expandUpdateAccess(id){
                this.hideViewAccessDetail()
                if(!id){
                    this.hideUpdateAccess()
                    return false;
                }
                const {data} = await this.$http.post('/back/auth/getAccessDetail',{
                    id:id
                })
                if(data.success){
                    const detail = data.detail
                    // console.log(detail)
                    this.edit = Object.assign({},this.edit,{
                        id:id,
                        name:detail.name,
                        type:detail.type,
                        path:detail.path
                    })
                    this.$store.dispatch('system/getApiAccessList')
                    if(this.$empty(this.api)){
                        this.$store.dispatch('system/getApiList')
                    }
                }else{
                    this.hideUpdateAccess();
                }
            },
            viewAccessDetail(id){
                this.hideUpdateAccess()
                if(!id){
                    this.hideViewAccessDetail()
                    this.$store.commit('system/clearAceessDetail')
                    return false
                }
                this.$store.dispatch('system/getAccessDetail',id).then(()=>{
                    this.viewAccessDetailStatus = true
                }).catch(()=>{
                    this.hideViewAccessDetail()
                })
            },
            hideViewAccessDetail(){
                this.viewAccessDetailStatus = false
            }
        },
        computed:{
            ...mapState('system',{
                api:state=>state.api,
                apiAccessPathArray:state=>{
                    const list = Object.values(state.api_access_list)
                    // return list
                    let result = []
                    list.forEach(function(el){
                        result.push(el.path)
                    })
                    return result
                },
                accessDetail:state=>state.accessDetail.detail
            })
        }
    }
</script>

<style scoped lang="less">

    .access{

        .table{

            .action-btn{
                font-size:1.5em;
                cursor:pointer;
                margin-right:.3em;

                &:last-child{
                    margin-right:0;
                }

                &:hover{
                    color:#409EFF;
                }
            }
        }

        .view{
            margin:3em 0;
            width:50em;
        }

        .edit{
            .view;

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