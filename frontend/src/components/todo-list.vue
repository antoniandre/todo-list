<template>
<div class="main-content todo-list">
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
  ul {
    list-style-type: none;
  }

  li {
    display: flex;
    padding: 5px 30px;
    transition: 0.2s;

    &:hover {background-color: rgba(255, 255, 255, 0.2);}
  }

  label {
    position: relative;
    padding: 0 8px;
    flex-grow: 1;

    &:before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      width: 0;
      transform: translateY(-50%);
      border-top: 1px solid green;
      transition: 0.2s ease-in-out;
    }
  }

  :checked ~ label {
    color: green;
    // text-decoration: line-through;

    &:before {
      width: 100%;
    }
  }

  .arrow {
    text-decoration: none;
    color: inherit;
    padding: 0px 12px;
    transition: 0.3s ease-in-out;

    &:hover {
      transform: translateX(4px);
    }
  }

}
</style>
