<template>
    <div class="user">
        <!--<blockquote class="layui-elem-quote">引用区域的文字</blockquote>-->
        <div class="form">
            <el-form ref="query" :model="condition" label-width="80px" :inline="true" size="small">
                <el-form-item label="">
                    <el-input v-model="condition.username" placeholder="用户名"></el-input>
                </el-form-item>
                <el-form-item label="">
                    <el-input v-model="condition.nickname" placeholder="昵称"></el-input>
                </el-form-item>
                <el-select v-model="condition.status" placeholder="请选择用户状态" size="small">
                    <el-option
                            v-for="item in userStatus"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                    </el-option>
                </el-select>
                <el-button icon="el-icon-search" class="query-btn" circle size="small" type="primary" title="搜索" @click.native="search"></el-button>
                <el-button class="query-btn" size="small" type="primary" title="清空搜索参数" @click.native="clear_condition" v-if="!condition_empty">清空</el-button>
            </el-form>
        </div>
        <div class="area">
            <el-button type="primary" size="small" @click="$router.push({name:'user-add'})">添加</el-button>
        </div>
        <div class="user-list">
            <el-table
                    :data="userList"
                    border
                    style="width: 100%" class="table">
                <el-table-column
                        fixed
                        label="用户ID"
                        prop="uid"
                        width="100">
                </el-table-column>
                <el-table-column
                        label="用户名"
                        prop="username"
                        width="350">
                </el-table-column>
                <el-table-column
                        label="昵称"
                        prop="nickname"
                        width="350">
                </el-table-column>
                <el-table-column
                        label="所属角色"
                        prop="role_name"
                        width="600">
                    <template slot-scope="scope">
                        <span :title="scope.row.role_name">{{scope.row.role_name}}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        label="注册时间"
                        prop="add_time_fmt"
                        width="200">
                </el-table-column>
                <el-table-column
                        label="最近活跃时间"
                        width="200">
                    <template slot-scope="scope">
                        {{scope.row.update_time}}
                    </template>
                </el-table-column>
                <el-table-column
                        label="状态"
                        width="200">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status===1">已启用</span>
                        <span v-else>已禁用</span>
                    </template>
                </el-table-column>
                <el-table-column
                        fixed="right"
                        label="操作"
                        width="200">
                    <template slot-scope="scope">
                        <a class="action-btn el-icon-error" title="封禁" v-if="scope.row.status===1" @click="updateStatus(scope.row.uid,0)"></a>
                        <a class="action-btn el-icon-success" title="启用" v-else @click="updateStatus(scope.row.uid,1)"></a>
                        <a class="action-btn el-icon-delete" title="删除此用户" @click="deleteUser(scope.row.uid,scope.row.nickname || scope.row.username)"></a>
                        <a class="action-btn el-icon-edit-outline" title="修改用户信息" @click="expandEditForm(scope.row.uid)"></a>
                        <a class="action-btn el-icon-view" title="查看详细信息" @click="viewUserDetaill(scope.row.uid)"></a>
                    </template>
                </el-table-column>
            </el-table>
            <div class="page" v-if="showPage">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        :total="page_var.total" :page-size="page_var.per_page" :current-page="page_var.current_page" @current-change="page_change">
                </el-pagination>
            </div>

            <div class="editForm" v-if="expandEdit">
                <el-form ref="edit" :model="edit" label-width="80px" size="mini">
                    <input type="hidden" v-model="edit.id">
                    <el-form-item label="用户名">
                        <el-input v-model="edit.username"></el-input>
                    </el-form-item>
                    <el-form-item label="昵称">
                        <el-input v-model="edit.nickname"></el-input>
                    </el-form-item>
                    <el-form-item label="密码">
                        <el-input type="password" v-model="edit.password"></el-input>
                    </el-form-item>
                    <el-form-item label="角色">
                        <el-checkbox-group v-model="edit.role">
                            <el-checkbox :label="item.id" v-for="(item,key,index) in role" :key="index">{{item.name}}</el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="updateUser">修改信息</el-button>
                        <el-button type="info" @click="hideEditForm">收起</el-button>
                    </el-form-item>
                </el-form>
            </div>

            <div class="viewUserDetail" v-if="expandViewUserID">
                <el-form  label-width="80px" size="mini">
                    <el-form-item label="ID">
                        <span>{{userDetail[expandViewUserID].uid}}</span>
                    </el-form-item>
                    <el-form-item label="用户名">
                        <span>{{userDetail[expandViewUserID].username}}</span>
                    </el-form-item>
                    <el-form-item label="昵称">
                        <span>{{userDetail[expandViewUserID].nickname}}</span>
                    </el-form-item>
                    <el-form-item label="状态">
                        <span>{{userDetail[expandViewUserID].status==1?'已启用':'已封禁'}}</span>
                    </el-form-item>
                    <el-form-item label="添加时间">
                        <span>{{userDetail[expandViewUserID].add_time_fmt}}</span>
                    </el-form-item>
                    <el-form-item label="更新时间">
                        <span>{{userDetail[expandViewUserID].update_time_fmt}}</span>
                    </el-form-item>
                    <el-form-item label="角色">
                        <span>{{Object.values(userDetail[expandViewUserID].role).join(',')}}</span>
                    </el-form-item>
                    <el-form-item label="拥有权限">
                        <span>{{userDetail[expandViewUserID].access_name.join(',').replace(/^,*/,'')}}</span>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="info" @click="hideViewUserDetail">收起</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</template>

<script>
    import qs from 'qs'
    import {mapState,mapGetters} from 'vuex';
    export default {
        name: "User",
        data(){
            return {
                expandEdit:false,
                edit:{
                    id:0,
                    username:'',
                    nickname:'',
                    password:'',
                    role:[]
                },
                userList:[],
                userStatus:[{
                    value:0,
                    label:'已禁用'
                },{
                    value:1,
                    label:'已启用'
                }],
                condition:{
                    username:'',
                    nickname:'',
                    status:'',
                },
                page_var:{
                    current_page:parseInt(this.$route.query.page) || 1
                },
                expandViewUserID:null
            }
        },
        created(){
            this.getUserList();
        },
        computed:{
            showPage(){
                return this.page_var.hasOwnProperty('last_page') && this.page_var.last_page>1;
            },
            condition_empty(){
                let empty = true;
                for(let i in this.condition){
                    empty = this.condition[i]==='';
                    if(!empty){
                        break;
                    }
                }
                return empty;
            },
            role(){
                return this.$store.getters['system/role'];
            },
            ...mapState('system',{
                userDetail:state=>state.userListDetail
            }),
        },
        methods:{
            async deleteUser(id,name){
                try{
                    await this.$confirm(`确认删除此用户${name || ''}吗?`);
                    const {data} = await this.$http.post('/back/auth/deleteUser',{
                        id:id
                    });
                    if(data.success){
                        this.getUserList();
                        this.$message({
                            message:data.msg || '操作成功',
                            type:'success'
                        })
                        this.hideViewUserDetail()
                    }
                }catch(e){}
            },
            async updateStatus(id,status){
                const statusArr = ['封禁','启用'];
                try{
                    await this.$confirm(`确定要${statusArr[status]}此用户吗`,'更新用户状态');
                    const {data} = await this.$http.post('/back/auth/updateUserStatus',{
                        id:id,
                        status:status
                    });
                    if(data.success){
                        this.getUserList();
                        this.$message({
                            message:data.msg,
                            type:'success',
                        })
                        this.hideViewUserDetail()
                    }else{
                        this.$message.error(data.error || data.msg || '操作失败')
                    }
                }catch(e){}
            },
            async getUserList(){
                this.$store.commit('system/clearUserListDetail');
                const get = {
                    size:10,
                    page:this.page_var.current_page
                };
                const {data} = await this.$http.post('/back/auth/getUserList?'+qs.stringify(get),this.condition);
                if(data.success){
                    this.userList = data.list;
                    this.page_var = data.page_var;
                    this.hideViewUserDetail()
                    // console.log(this.page_var.total<=1);
                }

            },
            page_change(page){
                this.$router.push({name:'user',query:{page:page}});
                this.page_var.current_page = page;
                this.getUserList();
            },
            search(){
                if(this.condition_empty){
                    this.$alert('请输入筛选条件');
                }else{
                    this.getUserList();
                }
            },
            clear_condition(){
               this.condition = {
                    username:'',
                        nickname:'',
                        status:'',
                };
               this.getUserList();
            },
            async expandEditForm(uid){
                if(this.$empty(this.role)){
                    this.$store.dispatch('system/role');
                }
                const {data} = await this.$http.post('/back/auth/getAuthDetail',{
                    id:uid
                });
                if(data.success){
                    let role = [];
                    Object.keys(data.detail.role).forEach(function(el){
                        role.push(parseInt(el));
                    });
                    this.edit = Object.assign({},this.edit,{
                        id:data.detail.uid,
                        nickname:data.detail.nickname,
                        username:data.detail.username,
                        role:role
                    });
                    this.hideViewUserDetail()
                }
                this.expandEdit = true;
            },
            hideEditForm(){
                if(this.$refs.edit){
                    this.$refs.edit.resetFields();
                }
                this.expandEdit = false;
            },
            async updateUser(){
                const {data} = await this.$http.post('/back/auth/updateUser',this.edit);
                if(data.success){
                    this.getUserList()
                    this.hideEditForm()
                    this.$success(data.msg || '修改成功')
                    this.hideViewUserDetail()
                }
            },

            // 查看用户详细信息
            async viewUserDetaill(uid){
                if(!uid){
                    this.$message('用户UID参数不能为空','error')
                    this.expandViewUserID = null
                }else if(this.userDetail.hasOwnProperty(uid) && !this.$empty(this.userDetail[uid])){
                    this.expandViewUserID = uid
                    this.hideEditForm()
                }else{
                    this.$store.dispatch('system/getUserDetail',uid).then(()=>{
                        this.expandViewUserID = uid
                        this.hideEditForm()
                    }).catch(()=>{
                        this.expandViewUserID = null
                    })
                }
            },

            hideViewUserDetail(){
                this.expandViewUserID = null;
            }
        }
    }
</script>

<style scoped lang="less">
    .user{
        width:100%;
        min-height:300px;

        .page{
            margin:1em 0;
            text-align:center;
        }

        .form{

            .query-btn{
                margin-left:1em;
            }
        }

        .user-list{

            .action-btn{
                font-size:1.5em;
                cursor:pointer;
                margin-right:.5em;

                &:last-child{
                    margin-right:0;
                }

                &:hover{
                    color:#409EFF;
                }
            }
        }

        .editForm{
            margin-top:3em;
            width:40%;
        }
    }
</style>