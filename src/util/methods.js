import mine from './mine'
// import video from './video'

const install = function(Vue,options){

    for(let i in mine){
        Vue.prototype['$'+i] = mine[i];
        Vue[i] = mine[i];
    }

    let methods = {};

    for(let i in methods){
        Vue.prototype['$'+i] = methods[i];
        Vue[i] = methods[i];
    }

    // 实例方法
    let insMethods = {
        // cutVideo:video.cut
    }


    for(let i in insMethods){
        Vue.prototype['$'+i] = insMethods[i];
    }

    Vue.mixin({
        layout:'default'
    })
};

export default install;