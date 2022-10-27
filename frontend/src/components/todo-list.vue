<template>
<div class="todo-list">
  <ul>
    <li v-for="task in tasks" :key="task.id">
      <input
        v-model="task.completed"
        :id="`checkbox-${task.id}`"
        type="checkbox"
        :checked="task.completed === 1"
        @change="saveTask(task)">
      <label :for="`checkbox-${task.id}`">{{ task.label }}</label>
      <router-link :to="`/task/${task.id}`">&#10132;</router-link>
    </li>
    <li>
      <input
        v-model="newTask.completed"
        id="checkbox-new"
        type="checkbox"
        :checked="newTask.completed === 1"
        @change="saveTask(newTask)">
      <label :for="`checkbox-new`">
        <input type="text" placeholder="New task...">
      </label>
    </li>
  </ul>
</div>
</template>

<script>
export default {
  data: () => ({
    tasks: [],
    newTask: {
      id: 'new',
      completed: 0,
      label: ''
    }
  }),

  methods: {
    saveTask (task) {
      fetch('/api/', {
        method: 'put',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(task)
      })
        .then(response => response.json())
        .then(response => {
        })
    }
  },

  created () {
    fetch('/api/', { method: 'get' })
      .then(response => response.json())
      .then(response => {
        this.tasks = response
      })
  }
}
</script>

<style lang="scss">
.todo-list {
  padding: 40px;
  max-width: 300px;
  margin: auto;
  align-self: center;
  flex: 1 1 auto;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.3);
  box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);

  ul {
  list-style-type: none;
  text-align: left;
  }

  li {
    display: flex;
    align-items: center;
  }

  label {padding-left: 10px;}
  :checked + label {
    text-decoration: line-through;
    color: #40b883;
  }

  a {margin-left: 6px;}
}
</style>
