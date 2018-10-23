<template>
    <div class="tab-container">
        <ul class="tab-header-wrap" >
            <template v-for="(item,index) in items">
                <tab-header v-if="isIcon(item.label)" :name="item.name" :class="item.className" v-html="item.label" :style="headerStyle" @toggle="toggleTab(item.name)"></tab-header>
                <tab-header v-else :name="item.name" :class="item.className" :style="headerStyle" @toggle="toggleTab(item.name)">
                    {{item.label}}
                </tab-header>
            </template>
        </ul>
        <div class="tab-content-wrap">
            <slot></slot>
        </div>

    </div>
</template>

<script>
    import tabHeader from './header'
    export default {
        name: "tab-container",
        components:{tabHeader},
        props:{
            current:{
                type:String,
                required:true
            }
        },
        data(){
            return {
                items:[],
                headerStyle:{}
            }
        },
        mounted(){
            this.$slots.default.forEach(el=>{
                this.items.push({
                    className:el.componentInstance.headerClass || el.data.attrs['header-class'] || '',
                    name:el.componentInstance.name,
                    label:el.componentInstance.label
                })
            })
            this.$set(this.headerStyle,'width',`${100 / this.items.length}%`)
            console.log(this.items)
        },
        methods:{
            isIcon(str){
                if(typeof str==='string' && str){
                    return /^\&\#x.+;$/.test(str)
                }
            },
            toggleTab(name){
                this.$emit('toggle',name)
            }
        }
    }
</script>

<style scoped lang="less">

    .tab-container{
        width:100%;
        height:100%;
        position:relative;

        @header-height:3em;
        .tab-header-wrap{
            margin:0;
            padding:0;
            width:100%;
            height:@header-height;
            line-height:@header-height;
            display:flex;
            justify-content:space-around;
            align-items:center;
            background-color:#F8F8F8;
            position:absolute;
            top:0;
            left:0;
            z-index:10;
        }

        .tab-content-wrap{
            padding-top:@header-height;
            box-sizing:border-box;
            width:100%;
            height:100%;
            overflow:hidden;
        }
    }
</style>