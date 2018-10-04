import {message,md5_chunk,result} from '../worker-helper'

message().then(({data})=>{
    const files = data.files

    return md5_chunk(files)

}).then(md5=>{
    result.md5 = md5
    self.postMessage(result)
    self.close()
}).catch(err=>{
    self.postMessage(String(err))
    self.close()
})