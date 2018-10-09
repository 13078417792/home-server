const path = require('path')
module.exports = {
    configureWebpack:{
        devServer:{
            disableHostCheck: true,
            // proxy: 'http://192.168.51.181:8998/'
            proxy:'http://api.server.php/'
        },
    },
    chainWebpack:config=>{
        config.module.rule('js').exclude.add(/\.worker\.js$/).end()
        config.module.rule('worker').test(/\.worker\.js$/).use('worker-loader').loader('worker-loader')/*.options({inline:true,fallback:true})*/.end()
    }
}
