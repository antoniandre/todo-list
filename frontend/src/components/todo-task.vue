<template>
<div class="main-content main-content--todo-task">
  <div v-if="loading">LOADING</div>
  <template v-else>
    <router-link to="/" class="back-arrow i-arrow-left" title="Back to list"></router-link>
    <h1 v-if="!task.id" class="main-content__title d-flex align-center">
      Not Found
    </h1>
    <div v-if="errorMessage" class="message message--error">{{ errorMessage }}</div>

    <form v-else>
      <div class="field">
        <label for="label" class="field__label">Label</label>
        <input v-model="task.label" id="label" type="text" class="field__input field__input--label">
      </div>
      <div class="field">
        <label for="description" class="field__label field__label--description">Description</label>
        <textarea v-model="task.description" id="description" rows="10" class="field__input"></textarea>
      </div>
      <div class="field">
        <label for="completed" class="field__label">completed</label>
        <input v-model="task.completed" id="completed" type="checkbox">
      </div>
      <div class="field">
        <label for="assignee" class="field__label">Assignee</label>
        <select v-model="task.assignee" id="assignee" class="field__input">
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.firstName }} {{ user.lastName }}
          </option>
        </select>
      </div>
      <div class="d-flex">
        <button @click="save" class="form-validate">Save</button>
      </div>
    </form>
  </template>
</div>
</template>

<script>
import {  onMounted, ref } from 'vue'
// defineProps({
//   id: { type: [String, Number], required: true }
// })

export default {
  props: {
    id: { type: [String, Number], required: true }
  },

  setup () {
    const loading = ref(true)

    onMounted(() => setTimeout(() => (loading.value = false), 2000))
    return { loading }
  },

  data: () => ({
    task: {
      id: null,
      label: '',
      completed: false,
      description: '',
      assignee: null,
      created: ''
    },
    users: [],
    errorMessage: ''
  }),

  methods: {
    save () {
      fetch('/api/', {
        method: 'put',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(this.task)
      })
        .then(response => response.json())
        .then(response => {
          this.loading = false
          this.task = Object.assign(this.task, response.task)
        }).catch(error => {
          this.error = true
          console.log(error)
        })
    }
  },

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
  position: relative;
  padding: 40px;
  max-width: 580px;

  .main-content__title {
    margin-bottom: 1rem;
    text-transform: capitalize;
    margin-left: 1.8rem;
    padding-left: 20px;
    line-height: 1.5;
  }

  .back-arrow {
    text-decoration: none;
    color: #000;
    background-color: rgba(255, 255, 255, 0.25);
    border-radius: 99em;
    width: 1.8rem;
    aspect-ratio: 1;
    position: absolute;
    top: 45px;
    left: 40px;

    &:before {padding-top: 0.2rem;}
  }

  .field {
    display: flex;
    margin: 10px 0;
    align-items: center;

    &:first-child {margin-top: 0;}

    &__label {
      display: inline-block;
      min-width: 130px;
      margin-right: 10px;
      text-align: right;
      font-weight: bold;
    }
    &__label--description {
      align-self: flex-start;
      margin-top: 4px;
    }

    &__input {
      font: 0.95rem $body-font;
      flex-grow: 1;
      border: none;
      background: rgba(255, 255, 255, 0.3);
      padding: 6px 8px;
      width: 100%;
      border-radius: 4px;
      outline: none;
    }
    &__input--label {
      font: bold 1.7rem $title-font;
      padding-top: 2px;
      padding-bottom: 0;
      height: 42px;
    }
  }

  .form-validate {
    margin-left: auto;
    margin-top: 16px;
    border: none;
    background: $completed-color;
    color: #fff;
    font-weight: bold;
    padding: 6px 20px;
    border-radius: 4px;
    outline: none;
  }
}
</style>
