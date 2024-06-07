<template>
  <header>
    <i class="app-logo i-done-all"></i>
    <span class="app-name">To Do App</span>
  </header>

  <main>
    <router-view></router-view>
  </main>

  <footer>
    <div class="copyright">
      &copy; <span>To Do App</span> {{ (new Date()).getFullYear() }}
    </div>
  </footer>
</template>

<script>
import '@/scss/index.scss'

export default {

}
</script>
