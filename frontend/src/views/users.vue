<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { setHeaders } from '@/helpers'

const router = useRouter()
const users = ref([])
const newUserFirstName = ref('')

fetch('/api/users', {
  method: 'get',
  headers: setHeaders()
})
  .then(response => {
    if (!response.ok) {
      if (response.status === 403) router.push('/login')
    }
    else return response.json()
  })
  .then(response => {
    users.value = response.users
  })
  .catch(() => {
  })
  .finally(() => {
    scrollHeight.value = usersListEl.value.scrollHeight
    offsetHeight.value = usersListEl.value.offsetHeight
  })

const openNewUser = () => {
  localStorage.userFirstName = newUserFirstName.value
  router.push('/users/new')
}

const usersListEl = ref(null)
const offsetHeight = ref(0)
const scrollHeight = ref(0)
const currentScrollPercent = ref(0)
const isScrolling = ref(false)
let timeoutId = null

const scrollbarThumbStyles = computed(() => {
  if (!usersListEl.value) return {}

  let height = offsetHeight.value * 100 / scrollHeight.value
  if (isNaN(height)) height = 0

  return {
    top: `${currentScrollPercent.value}%`,
    height: `${height}%`
  }
})

const onMousewheel = e => {
  clearTimeout(timeoutId)
  isScrolling.value = true
  usersListEl.value.scrollTop += e.deltaY
  currentScrollPercent.value = usersListEl.value.scrollTop * 100 / scrollHeight.value
  timeoutId = setTimeout(() => (isScrolling.value = false), 200)
}

const onResize = e => {
  offsetHeight.value = usersListEl.value.offsetHeight
  scrollHeight.value = usersListEl.value.scrollHeight
}

onMounted(() => {
  offsetHeight.value = usersListEl.value.offsetHeight
  scrollHeight.value = usersListEl.value.scrollHeight

  window.addEventListener('resize', onResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', onResize)
})
</script>

<template>
<div class="main-content main-content--users">
  <h1>Users</h1>
  <div class="scrollable">
    <div class="scrollbar" :class="{ scrolling: isScrolling }">
      <div class="scrollbar__track"></div>
      <div class="scrollbar__thumb" :style="scrollbarThumbStyles"></div>
    </div>
    <div class="scrollable__content">
      <ul class="users scrollable" ref="usersListEl" @mousewheel="onMousewheel">
        <li v-for="user in users" :key="user.id" class="user">
          <router-link :to="`/users/${user.id}`">
            <div class="user__avatar">{{ user.firstName[0] }}{{ user.lastName[0] }}</div>
            {{ user.firstName }} {{ user.lastName }}
            <i class="user__open-link arrow i-arrow-right"></i>
          </router-link>
          <button class="user__delete i-cross" @click.stop="deleteUser(user.id)"></button>
        </li>

        <!-- New user. -->
        <li
          ref="newUserElement"
          class="user user--new">
          <div class="user__avatar user__avatar--add">+</div>
          <input
            v-model="newUserFirstName"
            @click.stop
            @keypress.enter="openNewUser"
            placeholder="New user..."
            class="input-field">
          <button @click.stop="openNewUser">OK</button>
        </li>
      </ul>
    </div>
  </div>
</div>
</template>

<style lang="scss">
.main-content--users {
  max-width: 450px;

  h1 {
    text-align: center;
    margin-bottom: 1rem;
  }

  .row {
    margin-top: 1rem;
  }

  .form-validate[disabled] {opacity: 0.5;}

  .users {
    list-style-type: none;
    overflow: hidden;
    max-height: 40vh;
  }

  .user {
    position: relative;
    display: flex;
    align-items: center;
    padding: 5px 12px;
    transition: 0.2s;
    cursor: pointer;
    border-radius: 6px;
    gap: 0.8rem;

    &:hover {background-color: rgba(255, 255, 255, 0.2);}
    &--focused {background-color: rgba(255, 255, 255, 0.2);}

    a {
      display: flex;
      flex: 1;
      align-items: center;
      color: inherit;
      font-size: 1.1rem;
      font-weight: bold;
      gap: 0.8rem;
    }

    .user__avatar {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 3rem;
      aspect-ratio: 1;
      font-size: 1.3rem;
      letter-spacing: -0.05rem;
      border-radius: 99em;
      background-color: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(20px);
      font-weight: bold;
      flex: 0 0 auto;

      &--add {
        font-size: 2rem;
        background-color: rgba(lighten($primary-color, 25), 0.2);
      }
    }
    &:nth-of-type(1) .user__avatar {background-color: rgba(0, 0, 255, 0.1);}
    &:nth-of-type(2) .user__avatar {background-color: rgba(0, 255, 0, 0.15);}
    &:nth-of-type(3) .user__avatar {background-color: rgba(0, 255, 255, 0.15);}
    &:nth-of-type(4) .user__avatar {background-color: rgba(255, 0, 255, 0.15);}
    &:nth-of-type(5) .user__avatar {background-color: rgba(255, 0, 0, 0.15);}
    &:nth-of-type(6) .user__avatar {background-color: rgba(255, 255, 0, 0.15);}
  }

  .user--completed .user__label {
    color: $primary-color;

    &:before {width: 100%;}
  }

  .arrow {
    text-decoration: none;
    color: inherit;
    padding: 0px 12px;
    border-radius: 99rem;
    background-color: rgba(255, 255, 255, 0.12);
    color: #555;
    width: 1.5rem;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
    font-size: 0.9rem;
    font-size: 1rem;
    transition: 0.3s ease-in-out;

    &:hover {
      transform: translateX(4px);
      background-color: rgba(255, 255, 255, 0.25);
    }

    &:before {padding-top: 0.2rem;}
  }

  .user__delete {
    margin-left: 8px;
    border-radius: 99em;
    background-color: rgba(255, 0, 0, 0.35);
    color: #fff;
    border: none;
    width: 1.5rem;
    aspect-ratio: 1;
    transition: 0.25s;
    cursor: pointer;
    opacity: 0;

    &:hover {background-color: rgba(255, 0, 0, 0.6);}

    &:before {padding-top: 3px;}
  }
  .user:hover .user__delete {opacity: 1;}

  .user--new {
    i, button {flex-shrink: 0;}

    button {
      margin-left: auto;
      border-radius: 99em;
      border: none;
      background-color: rgba($primary-color, 0.5);
      width: 1.5rem;
      aspect-ratio: 1;
      color: #fff;
      cursor: pointer;
      opacity: 0;
      outline: none;
      font-size: 12px;
      transition: 0.3s;

      &:hover {background-color: rgba($primary-color, 0.8);}
    }

    &:hover button {opacity: 1;}
  }
}

.scrollable {
  position: relative;

  .scrollbar {
    position: absolute;
    inset: 0 0 0 auto;
    width: 8px;
    background-color: rgba(#000, 0.15);
    border-radius: 99em;
    z-index: 100;
    opacity: 0;
    transition: 0.25s 0.2s;

    &.scrolling {
      opacity: 1;
      transition: 0.2s;
    }

    &__track {
      position: absolute;
      inset: 0;
    }
    &__thumb {
      position: absolute;
      inset: 1px;
      height: 40px;
      border-radius: 99em;
      background-color: rgba(#000, 0.4);
    }
  }
}
</style>
