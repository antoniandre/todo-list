<template>
<div class="main-content main-content--todo-task">
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
      <label for="status" class="field__label">Status</label>
      <select v-model="task.status" id="status" class="field__input">
        <option v-for="status in statuses" :key="status" :value="status">
          {{ status }}
        </option>
      </select>
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
      <w-button @click.prevent="save" class="form-validate" :loading="loading">Save</w-button>
    </div>
  </form>
</div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { setHeaders } from '@/helpers'

const router = useRouter()

const props = defineProps({
  id: { type: [String, Number], required: true }
})

const loading = ref(false)
const statuses = ['todo', 'doing', 'done']

let task = reactive({
  id: null,
  label: '',
  status: 'todo',
  description: '',
  assignee: null,
  created: ''
})

const users = ref([])
const errorMessage = ref('')

fetch(`/api/tasks/${props.id}`, { method: 'get', headers: setHeaders() })
  .then(response => {
    if (!response.ok) {
      if (response.status === 403) router.push('/login')
      else if (response.status === 404) errorMessage.value = 'Task not found.'
      else errorMessage.value = 'Oops. Something went wrong.'

      throw new Error(errorMessage.value)
    }
    return response.json()
  })
  .then(response => {
    task = Object.assign(task, response.task)
    users.value = response.users
  })
  .catch(() => {
    errorMessage.value = 'Oops. Something went wrong.'
  })

const save = () => {
  loading.value = true

  fetch('/api/tasks', {
    method: 'put',
    headers: setHeaders(),
    body: JSON.stringify(task)
  })
    .then(response => response.json())
    .then(response => {
      task = Object.assign(task, response.task)
    }).catch(e => {
      console.log(e)
    }).finally(() => {
      loading.value = false
    })
}
</script>

<style lang="scss">
.main-content--todo-task {
  position: relative;
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
}
</style>
