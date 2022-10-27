<template>
<div class="todo-task">
  <router-link to="/" class="back">&lt; Back</router-link>
  <h1>TASK #{{ task.id }}</h1>
  <p><strong>label:</strong> {{ task.label }}</p>
  <p><strong>completed:</strong> {{ task.completed }}</p>
</div>
</template>

<script>
export default {
  props: {
    id: { type: [String, Number] }
  },

  data: () => ({
    task: {
      id: null,
      label: '',
      completed: false
    }
  }),

  created () {
    fetch('/api/' + this.id, {
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
  position: relative;
  padding: 40px;
  max-width: 300px;
  margin: auto;
  align-self: center;
  flex: 1 1 auto;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.3);
  box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);

  .back {
    position: absolute;
    top: 10px;
    left: 10px;
  }

  h1 {margin-bottom: 25px;}
}
</style>
