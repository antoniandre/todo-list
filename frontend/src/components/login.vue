<script setup>
import { ref } from 'vue'
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
    .then(({ error, jwt }) => {
      if (!error) {
        sessionStorage.jwt = jwt
        router.push('/')
      }
    })
}
</script>

<template>
<form @submit="onSubmit" class="main-content main-content--login">
  <h1>Log In</h1>
  <div class="row d-flex">
    <input v-model="username" type="text" placeholder="Username" class="input-field">
  </div>

  <div class="row d-flex">
    <input v-model="password" type="password" class="input-field" placeholder="Password">
  </div>

  <div class="row d-flex justify-end">
    <button
      type="submit"
      :disabled="!username || !password"
      class="form-validate">Log In</button>
  </div>
</form>
</template>

<style lang="scss">
.main-content--login {
  h1 {
    text-align: center;
    margin-bottom: 1rem;
  }

  .row {
    margin-top: 1rem;
  }

  .form-validate[disabled] {opacity: 0.5;}
}
</style>
