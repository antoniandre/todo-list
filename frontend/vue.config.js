const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    proxy: {
      '/api': {
        target: 'http://localhost:80/todolist/api',
        pathRewrite: { '^/api': '' }
      }
    }
  },

  css: {
    loaderOptions: {
      scss: {
        additionalData: '@import "@/scss/_variables.scss";'
      }
    }
  }
})
