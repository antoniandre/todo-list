<template>
<div class="main-content todo-task">
  <div class="title-wrap d-flex align-center">
    <router-link to="/" class="back-arrow i-arrow-left" title="Back to list"></router-link>
    <h1>Task #{{ task.id }}</h1>
  </div>
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

  .title-wrap {margin-bottom: 1rem;}

  .back-arrow {
    text-decoration: none;
    color: #000;
    background-color: rgba(255, 255, 255, 0.25);
    border-radius: 99em;
    width: 1.8rem;
    aspect-ratio: 1;
    margin-right: 1.5rem;

    &:before {padding-top: 0.2rem;}
  }
}
</style>
