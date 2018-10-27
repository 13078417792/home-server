import Vue from 'vue'
import Router from 'vue-router'
import VueCookie from 'vue-cookie'
import axios from './util/axios'
import mine from './util/mine'

Vue.use(Router)

const config = {
    default_layout:'framework', // 默认布局组件,没有指定布局组件名时默认用这个作为布局组件
    not_check_auth_route:['not-found'], // 不检查登录认证的路由
    not_layout:['login','not-found'], // 不使用布局的路由
};
// console.log(Router.prototype);
// 路由记录
let routes = [{
        path: '/',
        name: 'index',
        component: () => import('./views/Index')
    }, {
        path:'/user',
        name:'user',
        component:()=>import('./views/System/User/User'),

    }, {
        path:'/user/add',
        name:'user-add',
        component:()=>import('./views/System/User/Add'),

    }, {
        path:'/role',
        name:'role',
        component:()=>import('./views/System/Role/Role')
    }, {
        path:'/role/add',
        name:'role-add',
        component:()=>import('./views/System/Role/Add'),
    }, {
        path:'/access',
        name:'access',
        component:()=>import('./views/System/Access/Access'),
    }, {
        path:'/access/add',
        name:'access-add',
        component:()=>import('./views/System/Access/Add'),
    }, {
        path: '/login',
        name: 'login',
        component: () => import('./views/Login')
    }, {
        path: '/video',
        name: 'video',
        component: () => import('./views/Video/Video2')
    }, {
        path:'/video/upload',
        name:'video-upload',
        component: () => import('./views/Video/VideoUpload2'),
    }, {
        path:'/video/update',
        name:'video-update',
        component: () => import('./views/Video/VideoUpdate'),
    }, {
        path:'/video/category/add',
        name:'video-category-add',
        component: () => import('./views/Video/VideoCategoryAdd'),
    }, {
        path:'/404',
        name:'not-found',
        component: () => import('./views/not-found')
    }, {
        path:'*',
        redirect:to=>{
            return '/404';
        },
    }
];


let name_list = []; // 路由名称列表（一维数组）
let path_list = []; // 路径列表（一维数组）


// 根据配置对象，递归处理路由记录
function handleRoutes(route,config){
    route.forEach(function(el,index){
        if(config.not_layout.includes(el.name) || el.path==='/404'){
            if(el.hasOwnProperty('meta') && el.meta.hasOwnProperty('layout')){
                delete el.meta.layout;
            }
        }else{
            if(!el.hasOwnProperty('meta') || !el.meta.hasOwnProperty('layout')){
                el.meta = Object.assign(el.meta || {},{
                    layout:config.default_layout
                });
            }
        }
        if(el.name){
            name_list.push(el.name);
        }
        // path_list.push(el.path);
        if(el.hasOwnProperty('children') && Array.isArray(el.children) && el.children.length > 0 ){
            el.children = [].concat(handleRoutes(el.children,config));
        }
    });

    return route;
}

// 处理路由记录
routes = handleRoutes(routes,config);

const R = new Router({
    mode: 'history',
    base: process.env.BASE_URL,
    routes: routes,
    name_list:name_list,
    config:config
    // path_list:path_list
});
const checkAuth = async function(to,from,next){
    if(config.not_check_auth_route.includes(to.name)){
        next();
        return false;
    }
    const auth_token = VueCookie.get('auth_token') || null;
    if(!auth_token){
        if(to.name==='login'){
            next();
        }else{
            next({name:'login'});
        }
        return false;
    }
    const {data} = await axios.post('/back/Auth/checkAuth');
    if(data.success){
        if(to.name==='login'){
            next('/');
            return false;
        }
    }else{
        if(to.name!=='login'){
            next({name:'login'});
            return false;
        }
    }
    return true;
}

R.beforeEach(async (to,from,next)=>{
    if(to.matched.length===0){
        next('/404');
        return false;
    }
    const isAuth = await checkAuth(to,from,next);
    // console.log('isAuth:',isAuth);
    if(!isAuth){
        return false;
    }

    next();

});

R.afterEach(to=>{
    if(!config.not_layout.includes(to.name)){
        mine.ws.auth = VueCookie.get('auth_token') || null
        if(!mine.ws.auth){
            return
        }
        mine.ws.connect().then(()=>{
            console.log('websocket 连接成功')
        })
    }else{
        mine.ws.auth = null
        mine.ws.client.close()
    }
})
export default R;