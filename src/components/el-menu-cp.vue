<template>
    <div>
        <template v-for="(item,index) in menu">
            <template v-if="!$empty(item.child)">
                <el-submenu  :index="(pid?pid+'-':'')+String(index)" >
                    <template slot="title">
                        <i :class="item.icon || 'layui-icon layui-icon-file'"></i>
                        <span slot="title">{{item.title}}</span>
                    </template>
                    <el-menu-cp v-on:item-click="emitItemClick" :menu="item.child" :background-color="$props['background-color']" :text-color="$props['text-color']" :text-active-color="$props['text-active-color']" :pid="(pid?pid+'-':'')+String(index)"></el-menu-cp>
                </el-submenu>
            </template>
            <template v-else>
                <el-menu-item :index="(pid?pid+'-':'')+String(index)" ref="item" @click.stop.native="menuItemClick(index)" :data-router-name="item.routerName">
                    <i :class="item.icon || 'layui-icon layui-icon-file'"></i>
                    <span slot="title">{{item.title}}</span>
                </el-menu-item>
            </template>
        </template>
    </div>
</template>

<script>
    import elMenuCp from '@/components/el-menu-cp'
    export default {
        name: "el-menu-cp",
        components:{elMenuCp},
        props:{
            menu:{
                type:Array,
                required:true
            },
            'background-color':{
                type:String,
            },
            'text-color':{
                type:String
            },
            'text-active-color':{
                type:String
            },
            pid:{
                type:String,
                default:''
            }
        },
        methods:{
            menuItemClick(index){
                let el = this.$refs.item[index].$el;
                let event = [
                    el,
                    el.innerText,
                    el.dataset.routerName
                ];
                this.$emit('item-click',...event);
            },
            emitItemClick(){
                this.$emit('item-click',...arguments);
            }
        }
    }
</script>

<style scoped lang="less">
    *{
        user-select:none;
    }

    .layui-icon{
        margin-right:.5rem;
    }
</style>