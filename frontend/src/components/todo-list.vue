<template>
<div class="todo-list">
  <h1>TODO LIST</h1>

  <ul>
    <li v-for="task in tasks" :key="task.id">
      <input type="checkbox" :checked="task.completed === 1">
      <span>{{ task.label }}</span>
    </li>
  </ul>
</div>
</template>

<script>
export default {
  data: () => ({
    tasks: []
  }),

  created () {
    fetch('/api/', { method: 'get' })
      .then(response => response.json())
      .then(response => {
        this.tasks = response
      })

    // fetch('/api/', {
    //   method: 'post',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify({label: 'hello', completed: 1})
    // })
    //   .then(response => {
    //     console.log(response)
    //   })
  }
}
</script>

<style>
.todo-list ul {
  list-style-type: none;
  text-align: left;
}
</style>
