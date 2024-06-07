<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { setHeaders } from '@/helpers'

const router = useRouter()
const users = ref([])

fetch('/api/users', {
  method: 'get',
  headers: setHeaders()
})
  .then(response => {
    if (!response.ok) {
      error.value = true
      if (response.status === 403) router.push('/login')
    }
    else return response.json()
  })
  .then(response => {
    users.value = response.users
  })
  .catch(() => {
    error.value = true
  })

</script>

<template>
<div class="main-content main-content--login">
  <h1>Users</h1>

  <ul>
    <li v-for="user in users">
      <router-link :to="`/users/${user.id}`">{{ user.firstName }} {{ user.lastName }}</router-link>
    </li>
  </ul>
</div>
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
