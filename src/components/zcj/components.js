import layerDialog from './layer/dialog'
import tabContainer from './tab/container'
import tabContent from './tab/content'
import store from "../../store"
require('./style/common.less')
require('./style/layui-icon/layui-icon.css')

const components = [layerDialog,tabContainer,tabContent]

function empty(data){
    if([undefined,null,"",''].includes(data)){
        return true
    }
    if(['string','number','boolean'].includes(typeof data)){
        return false
    }
    if(Array.isArray(data)){
        return !data.length
    }
    for(let i in data){
        return false
    }
    return true
}

export default {


    install(Vue) {

        let dialogIndex = 1

        Vue.prototype._zcjComponent = {
            zIndex:3000,
            list:[]
        }

        Vue.prototype.empty = empty

        Vue.prototype.hasProp = function(data,prop){
            if(typeof data!=='object'){
                return false
            }
            if(!prop || typeof prop!=='string'){
                return false
            }
            return data.hasOwnProperty(prop) && !empty(data[prop])
        }



        // 组件注册
        components.forEach(function(component){
            Vue.prototype._zcjComponent.list.push(component.name)
            Vue.component(component.name,component)
        })

        Element.prototype.css = function(name,value){
            if(value){
                this.style[name] = value
                return value
            }else{
                if(value===null){
                    if(this.style.hasOwnProperty(name) && this.style[name]){
                        this.style[name] = null
                    }
                    return null
                }
                if(this.style.hasOwnProperty(name) && this.style[name]){
                    return this.style[name]
                }else{
                    return getComputedStyle(this)[name]
                }
            }
        }

        // 遮罩层
        const selectorStartReg = /^\.|#/
        const selector = '.dialog-container'
        const selector2 = selector.replace(selectorStartReg,'')
        const shadeSelector = selector+'---shade'
        const shadeSelector2 = shadeSelector.replace(selectorStartReg,'')

        Vue.prototype.dialog = []
        Vue.prototype.setShade = function(opacity=0.5){
            if(toString.call(opacity)!=='[object Number]'){
                console.warn('参数不合法，必须是数字类型且必须是证书和小于等于1')
                return
            }
            const container = document.querySelector(selector)
            if(!container.classList.contains(shadeSelector2)){
                container.classList.add(shadeSelector2)
            }
            container.css('background-color',`rgba(0,0,0,${opacity})`)


        }
        Vue.prototype.removeShade = function(){
            const container = document.querySelector(selector)
            container.classList.remove(shadeSelector2)
            container.css('background-color',null)
        }

        // dialog相关
        Vue.mixin({
            created(){
                if(!this.hasOwnProperty('dialog') || !Array.isArray(this.dialog) || !this.dialog.length){
                    return
                }

                let container
                if(!document.body.querySelector(selector)){
                    container = document.createElement('div')
                    container.classList.add(selector2)
                    document.body.appendChild(container)
                }else{
                    container = document.body.querySelector(selector)
                }

                Vue.prototype.dialogContainer = container
                this.dialogInstance = {}

                this.dialog.forEach(el=>{
                    const dialog = Vue.extend(layerDialog)
                    const subContainer = document.createElement('div')
                    container.appendChild(subContainer)
                    const dialogInstance = new dialog({
                        el:subContainer,
                        store,
                        data(){
                            return {
                                title:el.name || null,
                                components:{el}
                            }
                        }
                    })

                    if(this.dialogInstance.hasOwnProperty(el.name)){
                        if(Array.isArray(this.dialogInstance[el.name])){
                            this.dialogInstance[el.name].push(dialogInstance)
                        }else{
                            const tempDialog = this.dialogInstance[el.name]
                            this.dialogInstance[el.name] = [tempDialog]
                            this.dialogInstance[el.name].push(dialogInstance)
                        }
                    }else{
                        this.dialogInstance[el.name] = dialogInstance
                    }

                })
            }
        })
    }
}