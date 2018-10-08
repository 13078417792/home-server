import mine from '../../util/mine'
const state = {
    category:[]
}

const actions = {
    async getCategory({commit},form){
        const {data} = await mine.http.post('/back/video/category',form)
        if(data.success){
            commit('category',data.result)
        }
    }
}

const mutations = {
    category(state,data){
        state.category = toString.call(data)==='[object Array]'?new Array(...data):[]
    }
}

const getters = {
    category(state){
        function convertDisabled(data){
            data.forEach(function(el){
                el.disabled = !el.status
                if(!!el.child){
                    el.child = convertDisabled(el.child)
                }
            })
            return data
        }
        return convertDisabled(state.category)
        return state.category
    }
}

export default {
    state,
    actions,
    mutations,
    getters,
    namespaced:true
}