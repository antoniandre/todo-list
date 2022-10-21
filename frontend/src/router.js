import Vue from 'vue'
import VueRouter from 'vue-router'

import TodoHome from './components/todo-home.vue'
import TodoList from './components/todo-list.vue'
import TodoTask from './components/todo-task.vue'

Vue.use(VueRouter)

export default new VueRouter({
  mode: 'history',
  routes: [
    { path: '/home', component: TodoHome },
    { path: '/todo-list', component: TodoList },
    { path: '/todo-task', component: TodoTask }
  ]
})
