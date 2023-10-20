<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const username = ref('')
const password = ref('')

const onSubmit = e => {
  e.preventDefault()

  fetch('/api/login', {
    method: 'post',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username: username.value, password: password.value })
  })
    .then(response => response.json())
    .then(({ error }) => {
      if (!error) router.push('/')
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
