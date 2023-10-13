<script setup>
import { ref, onMounted } from 'vue'

const username = ref('')
const password = ref('')

const onSubmit = e => {
  e.preventDefault()

  fetch('/api/tasks', {
    method: 'post',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username: username.value, password: password.value })
  })
    .then(response => response.json())
    .then(response => {
      console.log(response)
      debugger
    })
}
onMounted(() => {
})
</script>

<template>
<form @submit="onSubmit">
  <h1>Log In</h1>
  <div>
    <input v-model="username" type="text" placeholder="username">
  </div>

  <div>
    <input v-model="password" type="password">
  </div>

  <div class="d-flex justify-end">
    <button type="submit">OK</button>
  </div>
</form>
</template>
