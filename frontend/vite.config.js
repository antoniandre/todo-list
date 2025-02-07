import { resolve } from 'path'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/api': 'http://localhost:80/todo-list/api'
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
        api: 'modern-compiler',
        additionalData: '@use "@/scss/_variables.scss" as *;'
      }
    }
  }
})
