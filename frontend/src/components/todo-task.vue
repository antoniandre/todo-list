<template>
<div class="main-content main-content--todo-task">
  <div class="main-content__title d-flex align-center">
    <router-link to="/" class="back-arrow i-arrow-left" title="Back to list"></router-link>
    <h1 v-html="task.id ? task.label : 'not found'"></h1>
  </div>
  <div v-if="errorMessage" class="message message--error">{{ errorMessage }}</div>
  <template v-else>
    <p><strong>Label: </strong>{{ task.label }}</p>
    <p><strong>completed: </strong>{{ task.completed }}</p>
  </template>
  <p>
    <strong>Assignee: </strong>
    <select v-model="task.assignee">
      <option v-for="user in users" :key="user.id" :value="user.id">
        {{ user.firstName }} {{ user.lastName }}
      </option>
    </select>
  </p>
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
      completed: false,
      assignee: null
    },
    users: [],
    errorMessage: ''
  }),

  created () {
    fetch(`/api/${this.id}`, {
      method: 'get'
    })
      .then(response => {
        if (!response.ok) {
          if (response.status === 404) this.errorMessage = 'Task not found.'
          else this.errorMessage = 'Oops. Something went wrong.'

          throw new Error(this.errorMessage)
        }
        return response.json()
      })
      .then(response => {
        this.task = response.task
        this.users = response.users
      })
      .catch(error => {
        console.log(error)
        this.errorMessage = 'Oops. Something went wrong.'
      })
  }
}
</script>

<style lang="scss">
.main-content--todo-task {
  padding: 40px;

  .main-content__title {margin-bottom: 1rem;}

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
