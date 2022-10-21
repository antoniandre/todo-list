<template>
  <div id="app">
    <header>
      <img alt="Vue logo" src="./assets/logo.png">
      <nav>
        <ul>
          <li><router-link to="/home">Home</router-link></li>
          <li><router-link to="/todo-list">Todo List</router-link></li>
          <li><router-link to="/todo-task">Todo Task</router-link></li>
        </ul>
      </nav>
    </header>

    <main>
      <router-view></router-view>
    </main>

    <footer>FOOTER</footer>
  </div>
</template>

<script>
export default {
  created () {
    fetch('/api/', { method: 'get' })
      .then(response => {
        console.log(response)
      })

    // fetch('/api/', {
    //   method: 'post',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify({label: 'hello', completed: 1})
    // })
    //   .then(response => {
    //     console.log(response)
    //   })
  }
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
</style>
