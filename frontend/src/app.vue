<template>
  <div id="app">
    <header>
      <img alt="Vue logo" src="./assets/logo.png">
      <h1>To Do App</h1>
    </header>

    <main>
      <router-view></router-view>
    </main>

    <footer>
      <div class="copyright">
        &copy; To Do App {{ (new Date()).getFullYear() }}
      </div>
    </footer>
  </div>
</template>

<script>
import '@/scss/index.scss'

export default {

}
</script>
