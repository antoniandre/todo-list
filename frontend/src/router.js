import Vue from 'vue'
import VueRouter from 'vue-router'

import TodoList from './components/todo-list.vue'
import TodoTask from './components/todo-task.vue'

Vue.use(VueRouter)

export default new VueRouter({
  mode: 'history',
  routes: [
    { path: '/', component: TodoList },
    { path: '/task/:id', component: TodoTask, props: true }
  ]
})
