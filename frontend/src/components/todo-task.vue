<template>
<div class="main-content todo-task">
  <router-link to="/">&lt; Back to list</router-link>
  <h1>Task #{{ task.id }}</h1>
  <p><strong>Label: </strong>{{ task.label }}</p>
  <p><strong>completed: </strong>{{ task.completed }}</p>
</div>
</template>

<script>
export default {
  props: {
    id: { type: [String, Number], required: true }
  },

  data: () => ({
    task: {
      id: null,
      label: '',
      completed: false
    }
  }),

  created () {
    fetch(`/api/${this.id}`, {
      method: 'get'
    })
      .then(response => response.json())
      .then(response => {
        this.task = response
      })
  }
}
</script>

<style lang="scss">
.todo-task {
  padding: 40px;
}
</style>
