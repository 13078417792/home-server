<template>
    <div class="framework">

        <!-- 顶部 -->
        <header class="header-bar">
            <span id="web-name">
                <i class="layui-icon">&#xe653;</i>
                服务管理后台
            </span>

            <span class="user-info-menu">
                <keep-alive>
                    <el-dropdown trigger="click">
                        <span class="el-dropdown-link title-link">
                            {{adminInfo.nickname || adminInfo.username}}<i class="el-icon-arrow-down el-icon--right"></i>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item>个人信息</el-dropdown-item>
                            <el-dropdown-item @click.native="logout">退出登录</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </keep-alive>
            </span>
        </header>

        <!-- 主体容器 -->
        <div class="main-container">

            <!-- 左侧栏 -->
            <div class="left-sidebar">
                <keep-alive>
                    <el-menu :background-color="menuBackgroundColor" :text-color="menuFontColor" :text-active-color="menuActiveFontColor">
                        <el-menu-cp :menu="sideMenu" v-on:item-click="itemClick"></el-menu-cp>
                    </el-menu>
                </keep-alive>
            </div>

            <!-- 主体 -->
            <div class="main">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
    import sideMenu from '@/components/side-menu'
    import elMenuCp from '@/components/el-menu-cp'
    import tabBtn from '@/components/tab-btn'
    import { mapState } from 'vuex'
    export default {
        name: "framework",
        components:{sideMenu,elMenuCp,tabBtn},
        data(){
            return {
                adminInfo:{},
                menuBackgroundColor:'#4f576f',
                menuFontColor:'#b6bfca',
                menuActiveFontColor:'#fff',
                tabFilterClose:['index'],
                sideMenu:[
                    {
                        title:'首页',
                        icon:'layui-icon layui-icon-home',
                        routerName:'index'
                    },
                    {
                        title:'系统管理',
                        icon:'layui-icon layui-icon-set',
                        child:[{
                            title:'用户管理',
                            routerName:'user',
                            icon:'layui-icon layui-icon-user'
                        },{
                            title:'权限管理',
                            routerName:'access',
                            icon:'layui-icon layui-icon-auz'
                        },{
                            title:'角色管理',
                            routerName:'role',
                            icon:'layui-icon layui-icon-group'
                        },{
                            title:'查看日志',
                            routerName:'log',
                            icon:'layui-icon layui-icon-file'
                        }]
                    },{
                        title:'视频管理',
                        icon:'layui-icon layui-icon-video',
                        child:[{
                            title:'分类管理',
                            routerName:'video-category-add',
                            icon:'layui-icon layui-icon-upload-drag'
                        },{
                            title:'上传',
                            routerName:'video-upload',
                            icon:'layui-icon layui-icon-upload-drag'
                        },{
                            title:'查看',
                            routerName:'video',
                            icon:'layui-icon layui-icon-video'
                        },{
                            title:'私有库',
                            routerName:'video-private',
                            icon:'layui-icon layui-icon-password'
                        }]
                    },{
                        title:'图片管理',
                        icon:'layui-icon layui-icon-picture',
                        child:[{
                            title:'上传',
                            routerName:'picture-upload',
                            icon:'layui-icon layui-icon-upload-drag'
                        },{
                            title:'查看',
                            routerName:'picture',
                            icon:'layui-icon layui-icon-picture'
                        },{
                            title:'私有库',
                            routerName:'picture-private',
                            icon:'layui-icon layui-icon-password'
                        }]
                    },{
                        title:'数据采集',
                        icon:'',
                        child:[{
                            title:'视频',
                            icon:'layui-icon layui-icon-video',
                            child:[{
                                title:'采集目标',
                                routerName:'collect-video-target',
                                icon:'layui-icon layui-icon-list'
                            },{
                                title:'查看',
                                routerName:'collect-video-list',
                                icon:'layui-icon layui-icon-video'
                            }]
                        },{
                            title:'图片',
                            icon:'layui-icon layui-icon-picture',
                            child:[{
                                title:'采集目标',
                                routerName:'ccollect-picture-target',
                                icon:'layui-icon layui-icon-list'
                            },{
                                title:'查看',
                                routerName:'collect-picture-list',
                                icon:'layui-icon layui-icon-picture'
                            }]
                        }]
                    }
                ],
                all_tabs_list:{},
                tabs:{}
            }
        },
        created(){
            this.getAdminInfo();
        },
        mounted(){

        },
        computed:{

        },
        watch:{

        },
        methods:{

            each_sideMenu_get_alltabslist(data){
                let list = {};
                data.forEach(el=>{
                    if(!el.hasOwnProperty('child')){
                        list[el.routerName] = {
                            title:el.title,
                            routerName:el.routerName
                        };
                    }else{
                        list = Object.assign(list,this.each_sideMenu_get_alltabslist(el.child));
                    }
                });
                return list;
            },
            itemClick(ele,title,routerName){
                // 判断即将跳转的路由名是否已在router.js定义，未定义将不会被添加到tab状态中
                if(this.$router.options.name_list.includes(routerName)){
                    this.$store.commit('pageTabs/add_tab',{
                        title:title,
                        routerName:routerName
                    });
                }
                // console.log(routerName);
                // console.log(this.$router.options);
                this.$router.push({name:routerName});
            },
            async getAdminInfo(){
                const {data} = await this.$http.post('/back/auth/getAuthUserInfo');
                if(data.success){
                    this.adminInfo = data.info;
                }else{
                    await this.$alert(data.msg || 'token失效,请重新登录');
                    this.$cookie.delete('auth_token');
                    this.$router.push({name:'login'});
                }
            },
            async logout(){
                try{
                    await this.$confirm('是否确认退出登录?','退出登录');
                    this.$cookie.delete('auth_token');
                    this.$router.push({name:'login'});
                }catch(e){}
            }
        }
    }
</script>

<style scoped lang="less">
    .framework{
        width:100%;
        height:100%;
        overflow:hidden;
        position:relative;

        @header-height:4em;
        .header-bar{
            width:100%;
            height:@header-height;
            // line-height:@header-height;
            line-height:@header-height - (.8em * 2);
            background-color:#2F4056;
            position:relative;
            z-index:5;
            padding:.8em 1em;
            box-sizing:border-box;
            color:#fff;

            #web-name{
                font-size:1.5em;
                user-select:none;

                .layui-icon{
                    font-size:1em;
                }
            }

            .user-info-menu{
                float:right;
                margin-right:1.5em;
                user-select:none;

                .title-link{
                    color:#fff;
                    outline:none;
                    user-select:none;
                    cursor:pointer;
                }
            }
        }

        .main-container{
            width:100%;
            height:100%;
            padding-top:@header-height;
            box-sizing:border-box;
            position:absolute;
            top:0;
            left:0;
            z-index:10;

            @left-sidebar-width:15em;
            .left-sidebar{
                background-color:#4f576f;
                width:@left-sidebar-width;
                height:100%;
                overflow:auto;
                float:left;

                .layui-icon{
                    margin-right:.5em;
                }
            }

            .main{
                width:auto;
                height:100%;
                overflow:auto;
                position:relative;
                padding:10px;
                box-sizing:border-box;
                // background-color:#eee;
            }
        }
    }
</style>