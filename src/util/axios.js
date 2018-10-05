import axios from 'axios'
import { MessageBox,Message } from 'element-ui'
import VueCookie from 'vue-cookie'
import qs from 'qs'
Object.assign(axios.defaults,{
    // baseURL: process.env.NODE_ENV==='production'?'http://192.168.51.231:84/':'http://192.168.51.231:84/',
    baseURL: process.env.NODE_ENV==='production'?'http://api.server.php/':'',
    timeout: 1500
});

function handlePostData(form,formData,key){
    if( !(typeof formData==='object' && formData instanceof FormData) ){
        formData = new FormData;
    }
    for(let i in form){
        let k = i
        if(key){
            k = `${key}[${i}]`
        }

        if(form[i] instanceof File){
            formData.append(k,form[i])
        }
        else if(typeof form[i]==='object'){
            formData = handlePostData(form[i],formData,k)
        }else{
            formData.append(k,form[i])
        }
    }
    return formData

}

axios.defaults.headers.common = Object.assign({},axios.defaults.headers.common,{
    Authorization:VueCookie.get('auth_token') || ''
})

axios.defaults.transformRequest = [function(data,headers){
    headers.common['Content-Type'] = 'multipart/form-data'
    headers.common.Authorization = VueCookie.get('auth_token') || ''
    if(data instanceof FormData){
        // console.log('transform',data)
        return data
    }
    data = handlePostData(data)
    return data
}]

axios.interceptors.request.use(function (config) {
    // 在发送请求之前做些什么
    return config
}, function (error) {
    // 对请求错误做些什么
    return Promise.reject(error)
})

axios.interceptors.response.use(async function (response) {
    // Do something with response data
    if(response.data.success===false && response.data.msg==='没有权限'){
        Message.error('没有权限');
    }else if(response.data.success===false){
        Message.error(response.data.error || response.data.msg || '操作失败');
    }
    // else{
    //     Message({
    //         message:response.data.msg || '操作失败',
    //         type:'success'
    //     });
    // }
    return response;
}, async function (error) {
    // Do something with response error
    await MessageBox.alert('接口又挂了~~刷新页面试试~~','接口错误')
    return Promise.reject(error)
})

export default axios
