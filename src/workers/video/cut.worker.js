
import {message,cut,md5_chunk} from '../worker-helper'
// return false
let result = {
    success:true,
    files:[],
    md5:null
}

// message().then(({data})=>{
//     const file = data.file
//     return cut(file)
// }).then(files=>{
//     result.files = new Array(...files)
//     return md5_chunk(files)
// }).then(md5=>{
//     result.md5 = md5
//     self.postMessage(result)
// }).catch(err=>{
//     console.error(err)
//     self.postMessage({
//         success:false,
//         error:err.valueOf()
//     })
// })

message().then(({data})=>{
    const file = data.file
    const size = data.size || 1024*2
    return cut(file,size)
}).then(files=>{
    result.files = new Array(...files)
    // return md5_chunk(files)
    self.postMessage({
        files,
        success:true
    })
    self.close()
}).catch(err=>{
    // console.error(err)
    self.postMessage(String(err))
    self.close()
})