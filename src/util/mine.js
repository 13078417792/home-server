import axios from './axios'
import {Message} from 'element-ui'
import SparkMD5 from 'spark-md5'
import wsClient from './ws'
var ws = new wsClient('ws://127.0.0.1:8903')

const isEmpty = val=>{
    if(typeof val==='boolean'){
        return false
    }
    if(Array.isArray(val)){
        return !val.length
    }
    if(toString.call(val) === '[object Object]'){
        for(let i in val){
            return false
        }
        return true
    }
    if([null,undefined,'',NaN].includes(val)){
        return true
    }
    return false
}

export default{
    http:axios,
    ws:ws,
    promise:function(fn){
        return new Promise((resolve,reject)=>{
            fn(resolve,reject)
        })
    },
    empty:isEmpty,
    isEmpty:isEmpty,
    fmtDate:function(obj){
        const date =  new Date(obj*1000);
        const y = date.getFullYear();
        const m = "0"+(date.getMonth()+1);
        const d = "0"+date.getDate();
        return y+"-"+m.substring(m.length-2,m.length)+"-"+d.substring(d.length-2,d.length);
    },
    success:function(msg) {
        return new Promise(function(resolve,reject){
            Message({
                message: msg,
                type: 'success',
                onClose:resolve
            });
        });
    },
    tips:async function(msg,type='success'){
        return new Promise(function(resolve){
            Message({
                message:msg,
                type:type,
                onClose:resolve
            });
        })
    },
    handleAccessArray(data){
        if(this.$empty(data) || !Array.isArray(data)){
            return {}
        }
        let result = {}
        data.forEach(function(el){
            let val = el.split('-');
            if(!result.hasOwnProperty(val[0])){
                result[val[0]] = []
            }
            result[val[0]].push(val[1]);
        })
        return result
    },
    md5_file(file){
        return new Promise(function(resolve,reject){
            let spark = new SparkMD5.ArrayBuffer()
            let reader = new FileReader()
            reader.addEventListener('load',()=>{
                spark.append(reader.result)
                resolve(spark.end())
            })
            reader.addEventListener('error',err=>{
                reject(err)
            })

            reader.readAsArrayBuffer(file)
        })
    },

    // 计算分片文件的MD5
    md5_chunk(chunk){
        return new Promise(function(resolve,reject){
            try{

                if(!Array.isArray(chunk)){
                    chunk = Object.values(chunk)
                }
                let reader = new FileReader(),
                    spark = new SparkMD5.ArrayBuffer()
                const md5 = function(){
                    const data = chunk.shift()
                    reader.readAsArrayBuffer(data)
                }
                reader.addEventListener('load',function(){
                    spark.append(reader.result)
                    if(chunk.length){
                        md5()
                    }else{
                        resolve(spark.end())
                    }
                })
                reader.addEventListener('error',err=>{
                    reject(err)
                })
                md5()
            }catch(e){
                reject(e)
            }

        })
    },
    workerMsg(worker){
        return new Promise(function(resolve,reject){
            try{
                worker.addEventListener('message',data=>{
                    resolve(data)
                })

                // worker.onmessage = function(data){
                //     resolve(data)
                // }
            }catch(err){
                reject(toString.call(err)==='[object String]'?err:String(err))
            }
        })
    },
    cut(file,size){
        if(!file){
            return Promise.reject('没有文件');
        }

        if(!file instanceof File){
            return Promise.reject('不是文件类型');
        }

        if(!size){
            size = 1024
        }
        size *= 1024
        let totals = file.size / size,
            files = []
        return new Promise(function(resolve,reject){
            try{
                for(let i=0;i<file.size;i+=size){
                    files.push(new File([file.slice(i,i+size,file.type)],file.name,{
                        type:file.type
                    }))
                }
                resolve(files)
            }catch(e){
                reject(e)
            }
        })
    },
}