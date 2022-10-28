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

<style lang="scss">
* {
  padding: 0;
  margin: 0;
}

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  display: flex;
  flex-direction: column;
  height: 100vh;
}

header {
  display: flex;
  background-color: #f9f9f9;
  padding: 12px;

  img {
    width: 25px;
    margin-right: 8px;
  }

  h1 {font-size: 20px;}
}

main {
  padding: 40px;
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

footer {
  background-color: #f9f9f9;
  padding: 12px;
}
</style>
