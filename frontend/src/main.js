import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import WaveUI from 'wave-ui'
import App from './app.vue'
import Login from './views/login.vue'
import TodoList from './views/todo-list.vue'
import TodoTask from './views/todo-task.vue'
import Users from './views/users.vue'
import User from './views/user.vue'
import 'wave-ui/dist/wave-ui.css'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', component: Login },
    { path: '/', component: TodoList },
    { path: '/users/:id', component: User, props: true },
    { path: '/users', component: Users, props: true },
    { path: '/task/:id', component: TodoTask, props: true }
  ]
})

const app = createApp(App)
app.use(router)
app.use(WaveUI, {
  colors: {
    primary: '#009688'
  }
})
app.mount('#app')
