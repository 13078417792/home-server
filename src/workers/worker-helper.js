import SparkMD5 from 'spark-md5'

function message(){
    return event('message')
}

function event(name){
    if(!name){
        throw new Error('参数空')
    }

    return new Promise(function(resolve,reject){

        try{
            self.addEventListener(name,function(event){
                resolve(event)
            })
        }catch(err){
            reject(err)
        }

    })
}

function error(){
    return event('error')
}

function cut(file,size){
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
}

function md5_file(file){
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
}

// 计算分片文件的MD5
function md5_chunk(chunk){
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
}

let result = {
    success:true
}
export {
    message,
    event,
    error,
    cut,
    md5_file,
    md5_chunk,
    result
}
