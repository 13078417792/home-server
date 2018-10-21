<template>
    <dialog v-if="show" class="layer-dialog" :class="dialogClass" ref="dialog" :style="dialogStyle" @mousedown.stop >
        <div class="layer-dialog--title overflow-line" @mousedown.stop="mousedown" draggable="false">
            {{title}}
            <i class="layui-icon" @click.stop="close">&#x1006;</i>
        </div>
        <div class="layer-dialog--content">
            <slot></slot>
        </div>

    </dialog>
</template>

<script>
    export default {
        name: "layer-dialog",
        props:{
            title:{
                type:String,
                default:'信息',
            },
            width:{
                type:[String,Number],
                default:'18em'
            },
            height:{
                type:[String,Number],
                default:'10em'
            }
        },
        data(){
            let dialogStyle = {
                zIndex:this._zcjComponent.zIndex
            }
            if(this.$props.width){
                dialogStyle.width = this.$props.width
            }
            if(this.$props.height){
                dialogStyle.height = this.$props.height
            }
            return {
                dialogStyle:dialogStyle,
                initPosition:[],
                position:[0,0],
                show:false,
                dialogClass:['layer-animate']
            }
        },
        mounted(){
            // console.log('dialog',this)
        },
        computed:{
            dialogSize(){
                const dialog = this.$refs.dialog
                const style = getComputedStyle(dialog)
                return [parseFloat(style.width),parseFloat(style.height)]
            }
        },
        methods:{

            // 拖动处理
            move(){
                this.$set(this.dialogStyle,'transform',`translate(${this.position[0]}px,${this.position[1]}px)`)
            },

            // 标题元素事件 Start
            mousedown(e){
                let initPosition = [e.clientX,e.clientY]
                const self = this
                const dialog = this.$refs.dialog
                const body = document.body
                body.classList.add('layer--no-select')

                function move(e){
                    const diffX = e.clientX - initPosition[0]
                    const diffY = e.clientY - initPosition[1]
                    self.position[0] += diffX
                    self.position[1] += diffY
                    initPosition = [e.clientX,e.clientY]
                    self.overRange()
                    self.move()
                }

                function mouseup(e){
                    document.removeEventListener('mousemove',move)
                    document.removeEventListener('mouseup',mouseup)
                    body.classList.remove('layer--no-select')
                }

                document.addEventListener('mousemove',move)
                document.addEventListener('mouseup',mouseup)

            },

            // 超出边界判断
            overRange(){

                // 横向
                if(Math.abs(this.position[0]) > (innerWidth - this.dialogSize[0]) / 2){
                    if(this.position[0]>0){
                        this.position[0] = (innerWidth - this.dialogSize[0]) / 2
                    }else{
                        this.position[0] = (innerWidth - this.dialogSize[0]) / -2
                    }
                }

                // 纵向
                if(Math.abs(this.position[1]) > (innerHeight - this.dialogSize[1]) / 2){
                    if(this.position[1]>0){
                        this.position[1] = (innerHeight - this.dialogSize[1]) / 2
                    }else{
                        this.position[1] = (innerHeight - this.dialogSize[1]) / -2
                    }
                }
            },
            // 事件End

            // 关闭弹窗并触发自定义事件
            close(){
                const self = this

                // 监听过渡结束事件
                function transitionend(){
                    self.show = false // 删除节点
                    self.$refs.dialog.removeEventListener('transitionend',transitionend) // 解除事件监听
                    self.$emit('close') // 触发外部关闭事件
                }
                this.$refs.dialog.addEventListener('transitionend',transitionend)
                const classIndex = this.dialogClass.indexOf('layer-animate--open')
                if(classIndex!==-1){
                    this.dialogClass.splice(classIndex,1)
                }
            },

            // 打开弹窗
            open(){
                if(!this.show){
                    this.show = true
                    setTimeout(()=>{
                        this.dialogClass.push('layer-animate--open')
                    },0)
                }
            }

        }
    }
</script>

<style scoped lang="less">

    @padding:1.5em;
    .layer-dialog{
        display:block;
        padding:0;
        min-width:13em;
        max-width:100%;
        max-height:100%;
        border:0;
        top:0;
        left:0;
        bottom:0;
        right:0;
        margin:auto;
        position:fixed;
        box-shadow:1px 1px 50px rgba(0,0,0,.3);


        @title-height:2.5em;
        @title-font-size:.9em;
        .layer-dialog--title{
            width:100%;
            height:@title-height / @title-font-size;
            line-height:@title-height / @title-font-size;
            font-size:@title-font-size;
            color:#333;
            padding:0 @padding*2/@title-font-size 0 @padding/@title-font-size;
            box-sizing:border-box;
            background-color:#F8F8F8;
            border-bottom:#eee;
            cursor:move;
            position:relative;

            .layui-icon{
                position:absolute;
                top:0;
                right:1em;
                z-index:10;
                cursor:pointer;
                transform:scale(1.3);

                &:hover{
                    color:#5FB878;
                }
            }
        }

        .layer-dialog--content{
            padding:0 @padding;
            box-sizing:border-box;
            width:100%;
            height:calc(100% - @title-height);
            overflow:auto;
            min-height:2em;
        }
    }
</style>