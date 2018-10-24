<template>
    <dialog v-if="show" class="layer-dialog" :class="dialogClass" ref="dialog" :style="dialogStyle" @mousedown.stop >
        <div class="layer-dialog--title overflow-line" @mousedown.stop="mousedown" draggable="false" :style="titleStyle">
            {{title}}
            <i class="icon layui-icon" @click.stop="close">&#x1006;</i>
            <template v-if="maxmin">

                <i class="icon maxmin iconfont-zcj" v-if="maxminStatus===2" @click.stop="setMaxmin(1)">&#xe621;</i>

                <i class="icon maxmin iconfont-zcj" v-else @click.stop="setMaxmin(2)">&#xe64a;</i>

                <i class="icon maxmin-hide iconfont-zcj" v-if="maxminStatus!==0" @click.stop="maxmin_hide()">&#xe60c;</i>
            </template>


        </div>
        <div class="layer-dialog--content" v-if="!hideContent" :style="contentStyle">
            <template v-for="(item) in components">
                <component :is="item"></component>
            </template>
        </div>

        <!-- 向左上拉伸 -->
        <div class="resize-base resize-left-top layui-icon" v-if="resize" @mousedown.stop="resizestart">
            &#xe65a;
        </div>
    </dialog>
</template>

<script>
    export default {
        name: "layer-dialog",
        props:{

        },

        data(){

            return {
                initPosition:[],
                position:[0,0],
                show:false,
                dialogClass:['layer-animate'],
                title:'信息',
                width:'18em',
                height:'10em',
                fixed:5, // 固定定位在什么位置，默认居中 1:左上角 2.右上角 3.右下角 4.左下角 5.居中
                dialogStyle:{},
                reserved:[false,false],
                drag:true, // 允许拖动窗口,
                titleStyle:{},
                contentStyle:{},
                resize:false,
                maxmin:false,
                maxminStatus:2, // 0.最小化 1.最大化 2.正常
                backup:{
                    width:null,
                    height:null,
                    drag:true
                },
                hideContent:false
            }
        },
        created(){
            this.backupData()
            this.computedDialogStyle()
            this.onresize()
        },
        mounted(){
            this.innerWidth = innerWidth
            this.innerHeight = innerHeight
            // console.log('dialog',this)
        },
        computed:{
            dialogSize(){
                const dialog = this.$refs.dialog
                if(dialog instanceof Element){
                    const style = getComputedStyle(dialog)
                    return [parseFloat(style.width),parseFloat(style.height)]
                }else{
                    return [0,0]
                }
            },
        },
        watch:{
            width(){
                this.computedDialogStyle()
            },
            height(){
                this.computedDialogStyle()
            },
            fixed(newVal){
                this.reserved[0] = [2,3].includes(newVal)
                this.reserved[1] = [3,4].includes(newVal)
            }
        },
        methods:{
            backupData(){
                const field = ['width','height','drag']
                field.forEach(el=>{
                    this.$set(this.backup,el,this[el])
                })
            },
            maxmin_hide(){
                this.setMaxmin(0)
            },
            // 最大最小化
            setMaxmin(status){
                this.maxminStatus = status
                if(status===1){
                    this.width = '100vw'
                    this.height = '100vh'
                    this.drag = false
                    this.hideContent = false
                }else if(status===2){
                    for(let i in this.backup){
                        this[i] = this.backup[i]
                    }
                    this.hideContent = false
                    this.$set(this.contentStyle,'display',null)
                }else{
                    this.drag = false
                    this.width = '18em'
                    this.height = 'auto'
                    this.hideContent = true
                }
                this.computedDialogStyle()
            },
            // 窗口大小拉伸
            resizestart(event,reserved=true){
                // console.log(event)
                let clientX = event.clientX
                let clientY = event.clientY
                const body = document.body
                const dialog = this.$refs.dialog
                body.classList.add('layer--no-select')

                const mousemove = e =>{

                    const moveClientX = e.clientX
                    const moveClientY = e.clientY
                    const diff = [
                        (reserved?-1:1) * (moveClientX - clientX),
                        (reserved?-1:1) * (moveClientY - clientY)
                    ]
                    // console.log(diff)
                    clientX = moveClientX
                    clientY = moveClientY
                    let width = parseFloat(dialog.css('width')) + diff[0]
                    let height = parseFloat(dialog.css('height')) + diff[1]
                    if(width<288){
                        width = 288
                    }
                    if(height<160){
                        height = 160
                    }
                    dialog.css('width',`${width}px`)
                    dialog.css('height',`${height}px`)

                    // dialog.css('height',`${parseFloat(dialog.css('height')) + diff[1]}px`)
                }

                const mouseup = e => {
                    body.classList.remove('layer--no-select')
                    document.removeEventListener('mousemove',mousemove)
                    document.removeEventListener('mouseup',mouseup)
                }

                document.addEventListener('mousemove',mousemove)
                document.addEventListener('mouseup',mouseup)
            },


            // 监听窗口大小变化
            onresize(){
                window.addEventListener('resize',e=>{
                    this.position[0] = this.position[0] / this.innerWidth * innerWidth
                    this.position[1] = this.position[1] / this.innerHeight * innerHeight
                    this.move(true)
                    this.innerWidth = innerWidth
                    this.innerHeight = innerHeight
                },false)
            },

            computedDialogStyle(){
                this.$set(this.dialogStyle,'zIndex',this._zcjComponent.zIndex)
                this.$set(this.dialogStyle,'width',this.width)
                this.$set(this.dialogStyle,'height',this.height)
            },
            // 拖动处理
            move(drag){
                let localDrag = drag===undefined?this.drag:drag
                if(!localDrag){
                    return
                }

                this.$set(this.dialogStyle,this.reserved[0]?'right':'left',`${this.position[0]}px`)
                this.$set(this.dialogStyle,this.reserved[1]?'bottom':'top',`${this.position[1]}px`)
            },

            // 标题元素事件 Start
            mousedown(e){
                if(!this.drag){
                    return
                }
                let initPosition = [e.clientX,e.clientY]
                const dialog = this.$refs.dialog
                const body = document.body
                body.classList.add('layer--no-select')

                const move = e => {
                    // console.log(e.clientX,e.clientY)
                    const diffX = (this.reserved[0]?-1:1) * (e.clientX - initPosition[0])
                    const diffY = (this.reserved[1]?-1:1) * (e.clientY - initPosition[1])
                    this.position[0] += diffX
                    this.position[1] += diffY
                    initPosition = [e.clientX,e.clientY]
                    this.overRange()
                    this.move()
                }

                const mouseup = e => {
                    document.removeEventListener('mousemove',move)
                    document.removeEventListener('mouseup',mouseup)
                    body.classList.remove('layer--no-select')
                }

                document.addEventListener('mousemove',move)
                document.addEventListener('mouseup',mouseup)

            },

            // 超出边界判断
            overRange(){
                let compare = [innerWidth - this.dialogSize[0],innerHeight - this.dialogSize[1]]

                if(this.position[0] > compare[0]){
                    this.position[0] = compare[0]
                }else if(this.position[0] < 0){
                    this.position[0] = 0
                }


                if(this.position[1] > compare[1]){
                    this.position[1] = compare[1]
                }else if(this.position[1] < 0){
                    this.position[1] = 0
                }

            },
            // 事件End

            // 关闭弹窗并触发自定义事件
            close(){
                const self = this
                if(!this.$refs.dialog || !(this.$refs.dialog instanceof Element)){
                    return
                }
                // 监听过渡结束事件
                function transitionend(){
                    self.show = false // 删除节点
                    self.$refs.dialog.removeEventListener('transitionend',transitionend) // 解除事件监听
                    self.$emit('close') // 触发外部关闭事件
                }
                console.log(this.$refs.dialog)
                this.$refs.dialog.addEventListener('transitionend',transitionend)
                const classIndex = this.dialogClass.indexOf('layer-animate--open')
                if(classIndex!==-1){
                    this.dialogClass.splice(classIndex,1)
                }
                this.removeShade()
            },

            // 打开弹窗
            open(options={}){
                options = Object.assign({},options)
                if(!this.show){
                    if(options.hasOwnProperty('opacity') && options.opacity){
                        this.setShade(options.opacity)
                        delete options.opacity
                    }

                    if(this.hasProp(options,'fixed') && [1,2,3,4,5].includes(options.fixed)){
                        this.fixed = options.fixed
                    }
                    delete options.fixed


                    // console.log(options,this.empty(options.drag))
                    if(typeof options==='object'){
                        for(let i in options){
                            if(options.hasOwnProperty(i) && !this.empty(options[i])){
                                this.$set(this,i,options[i])
                            }
                        }
                    }
                    this.backupData()
                    this.show = true
                    if(!this.drag){
                        this.$set(this.titleStyle,'cursor','default')
                    }else{
                        this.$set(this.titleStyle,'cursor',null)
                    }

                    setTimeout(()=>{
                        this.dialogClass.push('layer-animate--open')
                        this.handleFixed()

                    },0)

                }

            },

            // 初始化定位
            handleFixed(){
                if(![1,2,3,4,5].includes(this.fixed)){
                    return
                }
                if(this.fixed===5){
                    const x = (innerWidth - this.dialogSize[0]) / 2
                    const y = (innerHeight - this.dialogSize[1]) / 2
                    this.position[0] = x
                    this.position[1] = y
                    this.$set(this.dialogStyle,'left',`${x}px`)
                    this.$set(this.dialogStyle,'top',`${y}px`)
                    this.$set(this.dialogStyle,'right','unset')
                    this.$set(this.dialogStyle,'bottom','unset')
                    return
                }
                // dialogStyle
                if([2,3].includes(this.fixed)){
                    this.$set(this.dialogStyle,'right',0)
                    this.$set(this.dialogStyle,'left','unset')
                }else{
                    this.$set(this.dialogStyle,'left',0)
                    this.$set(this.dialogStyle,'right','unset')
                }
                if([3,4].includes(this.fixed)){
                    this.$set(this.dialogStyle,'bottom',0)
                    this.$set(this.dialogStyle,'top','unset')
                }else{
                    this.$set(this.dialogStyle,'top',0)
                    this.$set(this.dialogStyle,'bottom','unset')
                }
            }

        }
    }
</script>

<style scoped lang="less">

    @padding:1.5em;
    @border:2px;
    .layer-dialog{
        font-size:1rem;
        display:block;
        padding:0;
        min-width:13em;
        max-width:100vw;
        max-height:100vh;
        // border:@border solid rgba(0,0,0,.1);
        border:0;
        position:fixed;
        margin:0;
        box-shadow:1px 1px 50px rgba(0,0,0,.3);

        .resize-base{
            position:absolute;
            box-sizing:border-box;
            display:inline-block;
            z-index:10;
            font-size:1em;
        }

        .resize-left-top{
            width:@border*10;
            height:@border*10;
            line-height:@border*10;
            left:0;
            top:0;
            cursor:nw-resize;
            z-index:15;
            text-align:center;
            background-color:transparent;
            transform:rotate(45deg) scale(.7);
        }

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

            .icon{
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

            .icon.maxmin{
                right:3em;
                transform:scale(.9);
            }

            .icon.maxmin-hide{
                right:5em;
                transform:scale(.9);
            }
        }

        .layer-dialog--content{
            // padding:0 @padding;
            padding:0;
            box-sizing:border-box;
            width:100%;
            height:calc(100% - @title-height);
            overflow:auto;
            min-height:2em;
        }
    }
</style>