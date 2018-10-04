# service

# 文件结构

   ```
   src
    components  公共组件（可复用）
    layouts     布局组件（可复用）
    utils       公共方法
    views       视图组件
    vuex        状态管理
    workers     web-worker文件，用于大量运算的场景，如大文件上传前的文件分割，和MD5计算

    剩下的文件vue都已经定义好各自的功能
   ```

## Project setup
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```
