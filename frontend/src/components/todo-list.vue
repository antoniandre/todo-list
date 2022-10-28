<template>
<div class="todo-list">
  <ul>
    <li v-for="task in tasks" :key="task.id">
      <input :id="`checkbox-${task.id}`" type="checkbox" :checked="task.completed">
      <label :for="`checkbox-${task.id}`">{{ task.label }}</label>
      <router-link :to="`/task/${task.id}`">&#10132;</router-link>
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

<style lang="scss">
.todo-list ul {
  list-style-type: none;
  text-align: left;
  max-width: 300px;
  margin: auto;
  box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
  padding: 40px;
  background-color: #f4f4f4;

  label {padding: 0 8px;}
  :checked ~ label {
    color: green;
    text-decoration: line-through;
  }

  a {
    text-decoration: none;
    color: inherit;
  }
}
</style>
