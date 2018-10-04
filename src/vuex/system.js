import mine from '../util/mine'
let state = {
    userListDetail:{}, // 对应用户的详细信息（包含权限信息） key为用户ID
    role:[],
    roleListDetail:{},
    access:{},
    accessDetail:{},
    accessListDetail:{},
    api:{}, // 后端接口列表
    api_access_list:{} // 接口类型权限列表
}

async function getRoleList(fn){
    const {data} = await mine.http.post('/back/auth/getRoleList',{
        status:1
    });
    if(fn && toString.call(fn)==='[object Function]'){
        fn.call(this,data);
    }
}

let actions = {
    async getUserDetail({commit},uid){
        const {data} = await mine.http.post('/back/auth/getAuthDetail',{
            id:uid
        });
        if(data.success){
            commit('userDetail',data.detail)
        }
        return new Promise(function(resolve,reject){
            if(data.success){
                resolve()
            }else{
                reject(data)
            }
        })
    },
    async role({commit}){
        const {data} = await mine.http.post('/back/auth/getRoleList',{
            status:1
        });
        if(data.success){
            // console.log(data.role_list)
            commit('role',data.role_list);
        }
    },
    async access({commit}){
        const {data} = await mine.http.post('/back/auth/getAccessList')
        if(data.success){
            commit('access',data.access_list)
        }
    },
    async getRoleDetail({commit},id){
        if(!id){
            return Promise.reject(new Error('角色ID不能为空'));
        }
        const {data} = await mine.http.post('/back/auth/getRoleDetail',{
            id:id
        })
        if(data.success){
            commit('roleDetail',data)
            return Promise.resolve(data);
        }else{
            return Promise.reject(new Error(data.error || data.msg || '获取数据失败'),data);
        }
    },

    // 获取API类型权限
    async getApiAccessList({commit}){
        const {data} = await mine.http.post('/back/auth/getApiAccessList');
        commit('apiAccess',data.access_list);
    },

    // 获取所有接口
    async getApiList({commit}){
        const {data} = await mine.http.post('/back/auth/getApiList');
        if(data.success){
            commit('api',data.list);
        }
    },

    getAccessDetail({commit},id){
        if(!id){
            return Promise.reject(new Error('权限ID不能为空'));
        }
        mine.http.post('/back/auth/getAccessDetail',{
            id:id
        }).then(({data})=>{
            if(data.success){
                commit('accessDetail',data)
                return Promise.resolve(data);
            }else{
                return Promise.reject(new Error(data.error || data.msg || '获取数据失败'),data);
            }
        })
    },
}

let mutations = {
    userDetail(state,data){
        if(!mine.empty(data) && typeof data==='object'){
            state.userListDetail[data.uid] = data
        }
    },
    clearUserListDetail(state){
        state.userListDetail = {}
    },
    role(state,data){
        state.role = data
    },
    access(state,data){
        state.access = Object.assign({},data)
    },
    roleDetail(state,data){
        state.roleListDetail[data.detail.id] = data.detail
    },
    clearRoleDetail(state){
        state.roleListDetail = {}
    },
    apiAccess(state,data){
        state.api_access_list = Object.assign({},data)
    },
    api(state,data){
        state.api = Object.assign({},data)
    },
    accessListDetail(state,data){
        state.accessListDetail[data.detail.id] = data.detail
    },
    accessDetail(state,data){
        state.accessDetail = data
    },
    clearAceessDetail(){
        state.accessDetail = {}
    }
}

let getters = {
    userDetail({userListDetail},uid){
        if(!uid){
            return {};
        }
        return userListDetail[uid] || {};
    },
    allUserDetail(state){
        return state.userListDetail;
    },
    role(state){
        return state.role;
    },
    access(state){
        return state.access
    },
    roleDetail(state){
        return state.roleListDetail
    },
    api(state){
        return state.api
    },
    apiAccess(state){
        return state.api_access_list
    },
    accessListDetail(state){
        return state.accessListDetail
    },
    accessDetail(state){
        return state.accessDetail
    },
}

export default {
    state,
    actions,
    mutations,
    getters,
    namespaced:true
}