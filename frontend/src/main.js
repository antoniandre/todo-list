import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './app.vue'
import TodoList from './components/todo-list.vue'
import TodoTask from './components/todo-task.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', component: TodoList },
    { path: '/task/:id', component: TodoTask, props: true }
  ]
})

const app = createApp(App)
app.use(router)
app.mount('#app')
