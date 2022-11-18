<template>
<div class="main-content todo-list">
  <h1>To Do List</h1>
  <ul>
    <li
      v-for="task in tasks"
      :key="task.id"
      @click="saveTask(task)"
      :class="{ checked: task.completed }">
      <i :class=" task.completed ? 'i-checkbox-checked' : 'i-checkbox-unchecked'"></i>
      <label>{{ task.label }}</label>
      <router-link :to="`/task/${task.id}`" class="arrow i-arrow-right"></router-link>
    </li>
    <li @click="newTask.completed = !newTask.completed">
      <i :class="newTask.completed ? 'i-checkbox-checked' : 'i-checkbox-unchecked'"></i>
      <input
        v-model="newTask.label"
        @click.stop
        @keypress.enter="saveNewTask(newTask)"
        type="text"
        placeholder="New task...">
      <button @click="saveNewTask(newTask)">OK</button>
    </li>
  </ul>
</div>
</template>

<script>
export default {
  data: () => ({
    tasks: [],
    newTask: {
      id: null,
      label: '',
      completed: false
    }
  }),

  methods: {
    saveTask (task) {
      task.completed = !task.completed

      fetch('/api/', {
        method: 'put',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: task.id, completed: task.completed })
      })
        .then(response => {
          console.log(response)
        })
    },

    saveNewTask () {
      fetch('/api/', {
        method: 'post',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          label: this.newTask.label,
          completed: this.newTask.completed
        })
      })
        .then(response => {
          this.tasks.push({ ...this.newTask })
          this.newTask = Object.assign(this.newTask, { label: '', completed: false })
        })
    }
  },

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
  overflow: hidden;

  h1 {
    margin: 20px 0 10px;
    text-align: center;
  }

  ul {
    list-style-type: none;
    overflow: auto;
    max-height: 40vh;
  }

  li {
    display: flex;
    align-items: center;
    padding: 5px 30px;
    transition: 0.2s;

    &:hover {background-color: rgba(255, 255, 255, 0.2);}
  }

  i {
    font-size: 20px;
    padding-top: 3px;
  }
  .checked i {color: #009688;}

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

  .checked label {
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
    width: 1.5rem;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
    font-size: 0.9rem;
    font-size: 1rem;
    transition: 0.3s ease-in-out;
    transition: 0.3s ease-in-out;

    &:hover {
      transform: translateX(4px);
      background-color: rgba(255, 255, 255, 0.25);
    }

    &:before {padding-top: 0.2rem;}
  }
}
</style>
