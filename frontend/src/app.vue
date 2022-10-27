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
  min-height: 100vh;
  background-image:
    url('~@/assets/pattern.svg'),
    linear-gradient( 102.4deg, rgba(253,189,85,1) 7.8%, rgba(249,131,255,1) 100.3%);
  background-size: 300px, cover;
}

a {
  text-decoration: none;
  color: inherit;
}

header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  background-color: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  padding: 12px;
  z-index: 10;

  img {
    width: 20px;
    display: block;
  }

  h1 {
    margin-left: 8px;
    font-size: 20px;
  }
}

main {
  display: flex;
  padding: 100px 40px 40px;
  flex-grow: 1;
}

footer {
  padding: 20px;
  background-color: rgba(0, 0, 0, 0.1);
  color: #fff;
}
</style>
