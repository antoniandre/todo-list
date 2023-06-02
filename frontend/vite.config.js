// module.exports = defineConfig({
//   transpileDependencies: true,
//   devServer: {
//     proxy: {
//       '/api': {
//         target: 'http://localhost:80/todolist/api',
//         pathRewrite: { '^/api': '' }
//       }
//     }
//   },

import { resolve } from 'path'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue2'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/api': 'http://localhost:80/todolist/api'
    }
  },

  resolve: {
    alias: {
      '@': resolve(__dirname, '/src')
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: '@import "@/scss/_variables.scss";'
      }
    }
  }
})
