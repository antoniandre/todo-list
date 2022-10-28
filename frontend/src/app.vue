<template>
  <div id="app">
    <header>
      <img alt="Vue logo" src="./assets/logo.png">
      <h1>To Do App</h1>
    </header>

    <main>
      <router-view></router-view>
    </main>

    <footer>FOOTER</footer>
  </div>
</template>

<script>
export default {

}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}

header ul {
  list-style-type: none;
}
</style>
