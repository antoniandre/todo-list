<template>
<div class="main-content todo-list">
  <h1>To Do List</h1>
  <ul>
    <li v-for="task in tasks" :key="task.id">
      <input :id="`checkbox-${task.id}`" type="checkbox" :checked="task.completed">
      <label :for="`checkbox-${task.id}`">{{ task.label }}</label>
      <router-link :to="`/task/${task.id}`" class="arrow">&#10132;</router-link>
    </li>
  </ul>
</div>
</template>

<script>
export default {
  data: () => ({
    tasks: []
  }),

  created () {
    fetch('/api/', { method: 'get' })
      .then(response => response.json())
      .then(response => {
        this.tasks = response
      })

    // fetch('/api/', {
    //   method: 'post',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify({ label: 'hello', completed: 1 })
    // })
    //   .then(response => {
    //     console.log(response)
    //   })
  }
}
</script>

<style lang="scss">
.todo-list {
  padding-top: 0;

  h1 {
    margin: 20px 0 10px;
    text-align: center;
  }

  ul {
    list-style-type: none;
  }

  li {
    display: flex;
    align-items: center;
    padding: 5px 30px;
    transition: 0.2s;

    &:hover {background-color: rgba(255, 255, 255, 0.2);}
  }

  label {
    position: relative;
    margin-left: 8px;

    &:before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      width: 0;
      transform: translateY(-50%);
      border-top: 1px solid #009688;
      transition: 0.2s ease-in-out;
    }
  }

  :checked ~ label {
    color: #009688;

    &:before {width: 100%;}
  }

  .arrow {
    text-decoration: none;
    color: inherit;
    padding: 0px 12px;
    border-radius: 99rem;
    background-color: rgba(255, 255, 255, 0.12);
    color: #555;
    width: 1.8em;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
    font-size: 0.9rem;
    transition: 0.3s ease-in-out;

    &:hover {
      transform: translateX(4px);
      background-color: rgba(255, 255, 255, 0.25);
    }
  }
}
</style>
