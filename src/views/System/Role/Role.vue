<template>
    <div class="role">

        <div class="area">
            <el-button type="primary" @click="$router.push({name:'role-add'})" size="mini">添加角色</el-button>
        </div>

        <div class="table">
            <el-table
                    :data="role"
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
                        label="角色名"
                        width="300">
                </el-table-column>
                <el-table-column
                        prop="add_time"
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
                        <span v-if="scope.row.status===1">可用</span>
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
                        <a class="action-btn el-icon-delete" title="删除此角色" @click="deleteRole(scope.row.id,scope.row.name || '')"></a>
                        <a class="action-btn el-icon-edit-outline" title="修改角色信息" @click="expandUpdateRole(scope.row.id)"></a>
                        <a class="action-btn el-icon-view" title="查看详细信息" @click="viewRoleDetail(scope.row.id)"></a>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <div class="edit" v-if="edit.id">
            <el-form ref="edit" :model="edit" label-width="80px" size="mini">
                <input type="hidden" v-model="edit.id">
                <el-form-item label="角色名">
                    <el-input v-model="edit.name"></el-input>
                </el-form-item>
                <el-form-item label="角色权限">
                    <div class="access-group" v-for="(item,key,index) in accessList" v-if="item.length" :key="index">
                        <p class="access-type">{{key}}</p>
                        <div class="access-list" >
                            <el-checkbox-group v-model="edit.access">
                                <el-checkbox class="access-item" v-for="(sub,subIndex) in item" :key="subIndex" :label="key+'-'+sub.id" >{{sub.name}}</el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="updateRole">修改</el-button>
                    <el-button type="info" @click="hideUpdateRole">收起</el-button>
                </el-form-item>
            </el-form>
        </div>

        <div class="edit" v-if="viewRoleID && roleListDetail[viewRoleID]">
            <el-form  label-width="80px" size="mini">
                <el-form-item label="角色名">
                    {{roleListDetail[viewRoleID].name}}
                </el-form-item>
                <el-form-item label="角色权限">
                    <span v-html="roleAccessString"></span>
                </el-form-item>
                <el-form-item label="添加时间">
                    {{roleListDetail[viewRoleID].add_time_fmt}}
                </el-form-item>
                <el-form-item label="修改时间">
                    {{roleListDetail[viewRoleID].update_time}}
                </el-form-item>
                <el-form-item label="状态">
                    {{roleListDetail[viewRoleID].status===1?'可用':'禁用'}}
                </el-form-item>
                <el-form-item>
                    <el-button type="info" @click="hideViewRoleDetail">收起</el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    export default {
        name: "Role",
        data(){
            return {
                role:[],
                form:{
                    name:''
                },
                edit:{
                    id:null,
                    name:'',
                    access:[]
                },
                viewRoleID:null
            };
        },
        created(){
            this.getRoleList();
        },
        computed:{
            ...mapState('system',{
                accessList:state=>{
                    const access = state.access
                    let access_group = {}
                    for(let i in access){
                        if(!access_group.hasOwnProperty(access[i].type)){
                            access_group[access[i].type] = []
                        }
                        access_group[access[i].type].push(access[i]);
                    }
                    return access_group;
                },
                roleListDetail:state=>state.roleListDetail,
                roleAccessString(){
                    if(!this.viewRoleID || !this.roleListDetail[this.viewRoleID]){
                        return ''
                    }
                    let access = ''
                    this.roleListDetail[this.viewRoleID].access.forEach(function(el){
                        access+=`${el.name}[${el.path}]<br />`
                    })
                    return access
                }
            })
        },
        methods:{
            async getRoleList(){
                const {data} = await this.$http.post('/back/auth/getRoleList');
                this.role = data.role_list;
                this.$store.commit('system/clearRoleDetail')
                this.hideUpdateRole()
                this.hideViewRoleDetail()
            },
            async updateStatus(id,status){
                const statusArr = ['封禁','启用'];
                try{
                    await this.$confirm(`确定要${statusArr[status]}此角色吗`,'更新角色状态');
                    const {data} = await this.$http.post('/back/auth/updateRoleStatus',{
                        id:id,
                        status:status
                    });
                    if(data.success){
                        this.getRoleList();
                        this.$message({
                            message:data.msg,
                            type:'success',
                        })
                        this.hideUpdateRole()
                    }else{
                        this.$message.error(data.error || data.msg || '操作失败')
                    }
                }catch(e){}
            },
            async deleteRole(id,name){
                try{
                    await this.$confirm(`确定要删除角色[${name || ''}]吗`,'删除角色');
                    const {data} = await this.$http.post('/back/auth/deleteRole',{
                        id:id
                    });
                    if(data.success){
                        this.getRoleList();
                        this.$message({
                            message:data.msg,
                            type:'success',
                        })
                        this.hideUpdateRole()
                    }else{
                        this.$message.error(data.error || data.msg || '操作失败')
                    }
                }catch(e){}
            },
            expandUpdateRole(id){
                this.hideViewRoleDetail()
                if(this.$empty(this.accessList)){
                    this.$store.dispatch('system/access');
                }
                if(!id){
                    this.$tips('角色ID不能为空','error')
                    return false;
                }
                this.$http.post('/back/auth/getRoleDetail',{
                    id:id
                }).then(({data})=>{
                    if(data.success){
                        let edit = {
                            id:id,
                            name:data.detail.name,
                            access:(function(access){
                                let result = [];
                                access.forEach(function(el){
                                    result.push(el.type+'-'+el.access_id);
                                })
                                return result
                            })(data.detail.access)
                        };
                        // console.log(id,data.detail,edit);
                        this.edit = Object.assign({},this.edit,edit);
                    }
                })
            },
            hideUpdateRole(){
                this.edit = {
                    id:null,
                    name:'',
                    access:[]
                }
            },
            updateRole(){
                this.$http.post('/back/auth/updateRole',Object.assign({},this.edit,{
                    access:this.$handleAccessArray(this.edit.access)
                }))
                    .then(({data})=>{
                        console.log(this.accessList)
                        if(data.success){
                            this.$success(data.msg || '修改成功');
                            this.getRoleList();
                        }
                    })
                    .catch(()=>{});
            },

            // 查看角色详细信息
            viewRoleDetail(id){
                this.hideUpdateRole()
                if(!id){
                    return false
                }
                if(!this.roleListDetail.hasOwnProperty(id) || this.$empty(this.roleListDetail[id])){
                    this.$store.dispatch('system/getRoleDetail',id).then(data=>{
                        this.viewRoleID = id
                    }).catch(e=>{
                        this.viewRoleID = null
                        console.error(e);
                    });
                }else{
                    this.viewRoleID = id
                }
            },

            // 收起角色详细信息
            hideViewRoleDetail(){
                this.viewRoleID = null
            }

        }
    }
</script>

<style scoped lang="less">

    .role{

        .add-role{
            overflow:hidden;
            transition:height .5s;
        }

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

        .edit{
            margin-top:3em;
            width:30em;

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