module.exports = {
    devServer: {
        port: 8900,
    },
    productionSourceMap: false,
    configureWebpack: {
        module: {
            rules: [{
                test: /\.xtmin$/,
                use: ["./src/assets/js/xtmin-loader.js"],
            }]
        },
    }
}
