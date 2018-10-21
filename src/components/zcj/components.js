import layerDialog from './layer/dialog'
require('./style/common.less')
require('./style/layui-icon/layui-icon.css')

const components = [layerDialog]


export default {


    install(Vue) {

        Vue.prototype._zcjComponent = {
            zIndex:3000,
            list:[]
        }

        components.forEach(function(component){
            Vue.prototype._zcjComponent.list.push(component.name)
            Vue.component(component.name,component)
        })
    }
}