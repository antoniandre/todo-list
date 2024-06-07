<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { setHeaders } from '@/helpers'

const router = useRouter()
const props = defineProps({
  id: { type: Number, required: true }
})

const errorMessage = ref('')

let user = reactive({
  id: ref(null),
  firstName: ref(''),
  lastName: ref(''),
  username: ref(''),
  email: ref(''),
  phone: ref('')
})

fetch(`/api/users/${props.id}`, {
  method: 'get',
  headers: setHeaders()
})
  .then(response => {
    if (!response.ok) {
      if (response.status === 403) router.push('/login')
      else if (response.status === 404) errorMessage.value = 'Task not found.'
      else errorMessage.value = 'Oops. Something went wrong.'
    }
    else return response.json()
  })
  .then(response => {
    user = Object.assign(user, response.user)
  })
  .catch(() => {
    errorMessage.value = 'Oops. Something went wrong.'
  })

</script>

<template>
<div class="main-content main-content--user">
  <router-link to="/" class="back-arrow i-arrow-left" title="Back to list"></router-link>
  <h1 v-if="!user.id" class="main-content__title d-flex align-center">
    Not Found
  </h1>
  <div v-if="errorMessage" class="message message--error">{{ errorMessage }}</div>

  <form v-else>
    <h1>{{ user.firstName }} {{ user.lastName }}</h1>

    <p>{{ user.email }}</p>
    <p>{{ user.phone }}</p>
  </form>
</div>
</template>

<style lang="scss">
.main-content--user {
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
