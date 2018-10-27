import VueCookie from 'vue-cookie'
class ws{

    constructor(url=''){
        this.url = url
        this.client = null
        // this.connected = false
        this.auth = VueCookie.get('auth_token') || null
        // this.broadcastEventList = {}
        return this
    }

    // 设置连接URL
    url(url){
        if(!url || toString.call(url)!=='[object String]' || !/^wss?:\/\//.test(url)){
            throw new Error('必须传递一个连接路径，且路径必须是字符串，开头为ws://')
        }
        this.url = url
        return this
    }

    // 验证URL
    validateUrl(){
        if(!this.url){
            this.error = 'URL为空'
            return false
        }
        if(toString.call(this.url)!=='[object String]'){
            this.error = 'URL不是字符串'
            return false
        }
        if(!/^wss?:\/\//.test(this.url)){
            this.error = 'URL不合法'
            return false
        }
        return true
    }

    // 连接
    connect(){
        // if(this.connected){
        if(this.client && this.client.readyState===1){
            console.warn('websocket 已连接')
            return
        }
        const url_result = this.validateUrl()
        if(url_result!==true){
            throw new Error(url_result)
        }
        const client = new WebSocket(this.url,[this.auth])
        const self = this
        this.client = client
        const promise = new Promise(function(resolve,reject){
            client.addEventListener('open',function(e){
                self.broadcast()
                resolve(e)
            })
            client.addEventListener('error',reject)
        })
        return promise
    }

    error(msg){
        throw new Error(msg || '未知错误')
    }

    empty(data){
        if(!data){
            return true
        }
        if(Array.isArray(data)){
            return data.length===0?true:false
        }
        if(toString.call(data)==='[object Object]'){
            for(let i in data){
                return false
            }
            return true
        }
        return false
    }

    message(path){
        return new Promise((resolve,reject)=>{
            const handle = msg=>{
                let data = msg.data
                if(data instanceof Blob){
                    this.reader(data).then(function(res){
                        resolve(res)
                    })

                }else{

                    reject('返回数据不合法')
                }

            }
            this.client.addEventListener('message',handle)
        })

    }

    reader(data){

        return new Promise((resolve,reject)=>{
            if(this.empty(data)){
                reject('没有数据')
            }
            if(!data instanceof Blob){
                reject('数据格式不合法')
            }
            const reader = new FileReader()
            reader.addEventListener('load',()=>{
                let responseData
                try{
                    responseData = JSON.parse(reader.result)
                }catch(e){
                    responseData = reader.result
                }
                resolve(responseData)
            })
            reader.readAsText(data)

        })

    }

    send(path,data,options={}){
        if(!path){
            this.error('路径为空')
        }
        if(toString.call(path)!=='[object String]'){
            this.error('路径不合法')
        }
        if(this.empty(data)){
            this.error('没有发送数据')
        }
        if(!options.isReturn){
            options.isReturn = false
        }
        // options.auth = 123 // this.auth
        options.auth = this.auth
        let sendData = Object.create(null)

        sendData.path = path
        sendData.data = data
        sendData.return = options.isReturn
        sendData.auth = options.auth
        // console.log(sendData)
        let blob = new Blob([JSON.stringify(sendData)],{
            type:'application/json'
        })
        // console.log(blob)

        try{
            this.client.send(blob)
        }catch(e){
            // this.client.close()
            console.warn('websocket出错，关闭连接')
        }

        return this
    }

    close(){
        const self = this
        return new Promise(function(resolve,reject){
            try{
                self.client.addEventListener('close',resolve)
            }catch(e){
                console.error(e)
                reject(e)
            }
        })
    }

    // 监听广播
    broadcast(){
        this.client.addEventListener('message',async message=>{
            let data = message.data
            // console.log(data,typeof data)
            if(data instanceof Blob){
                const result = await this.reader(data)
                // console.log(result)
                if(result.type==='broadcast'){
                    for(let name in this.broadcastEventList){
                        if(result.path===name){
                            this.broadcastEventList[name](result)
                        }
                    }

                }

            }
        })
    }

    // 添加广播监听事件
    addBroadCase(path,fn){
        if(typeof path!=='string' || this.empty(path) || typeof fn!=='function'){
            return
        }
        this.broadcastEventList[path] = fn
        // console.log(this.broadcastEventList)
    }

}
ws.prototype.broadcastEventList = {}
export default ws