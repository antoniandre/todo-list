<template>
<div class="main-content main-content--todo-list">
  <h1 class="main-content__title">To Do List</h1>
  <div v-if="error" class="message message--error">Oops. Something went wrong.</div>
  <ul class="tasks">
    <li
      v-for="task in tasks"
      :key="task.id"
      @click="saveTask(task)"
      @contextmenu.prevent="openContextMenu(task)"
      :class="{ [`task task--${task.id}`]: true, 'task--completed': task.completed }">
      <i :class="`task__icon ${task.completed ? 'i-checkbox-checked' : 'i-checkbox-unchecked'}`"></i>
      <label class="task__label">{{ task.label }}</label>
      <router-link :to="`/task/${task.id}`" class="task__open-link arrow i-arrow-right"></router-link>
      <button class="task__delete i-cross" @click.stop="deleteTask(task.id)"></button>
    </li>
    <!-- New task. -->
    <li
      ref="newTask"
      class="task task--new"
      :class="{ checked: newTask.completed }"
      @click="newTask.completed = !newTask.completed">
      <i :class="`task__icon ${newTask.completed ? 'i-checkbox-checked' : 'i-checkbox-unchecked'}`"></i>
      <input
        v-model="newTask.label"
        @click.stop
        @keypress.enter="saveNewTask(newTask)"
        type="text"
        placeholder="New task...">
      <button @click.stop="saveNewTask(newTask)">OK</button>
    </li>
  </ul>
  <div v-if="contextMenu.show" class="context-menu">
    <label for="assignee" class="field__label">Assign to: </label>
    <select v-model="contextMenu.task.assignee" id="assignee" class="field__input">
      <option v-for="user in users" :key="user.id" :value="user.id">
        {{ user.firstName }} {{ user.lastName }}
      </option>
    </select>
  </div>
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
    },
    users: [],
    error: false,
    contextMenu: {
      show: false,
      task: {}
    }
  }),

  methods: {
    saveTask (task) {
      fetch('/api/', {
        method: 'put',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: task.id, completed: !task.completed })
      })
        .then(response => response.json())
        .then(response => {
          task.completed = response.task.completed
          this.loading = false
        }).catch(error => {
          this.error = true
          console.log(error)
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
        .then(response => response.json())
        .then(response => {
          this.tasks.push(response.task)
          this.newTask = Object.assign(this.newTask, { label: '', completed: false })
          this.$nextTick(() => {
            this.$refs.newTask.scrollIntoView({ behavior: 'smooth' })
          })
        }).catch(error => {
          this.error = true
          console.log(error)
        })
    },

    deleteTask (id) {
      fetch('/api/', {
        method: 'delete',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      })
        .then(() => {
          this.tasks = this.tasks.filter((item) => item.id !== id)
        })
        .catch(error => {
          this.error = true
          console.log(error)
        })
    },

    openContextMenu (task) {
      const taskDomNode = document.querySelector(`.task--${task.id}`)
      this.contextMenu.show = true
      this.contextMenu.task = task
      this.contextMenu.top = taskDomNode.offsetTop
      this.contextMenu.right = taskDomNode.offsetRight
    }
  },

  created () {
    fetch('/api/', { method: 'get' })
      .then(response => response.json())
      .then(response => {
        this.tasks = response.tasks
        this.users = response.users
      })
      .catch(error => {
        this.error = true
        console.log(error)
      })
  }
}
</script>

<style lang="scss">
.main-content--todo-list {
  padding-top: 0;

  .main-content__title {
    margin: 20px 0 10px;
    text-align: center;
  }

  .tasks {
    list-style-type: none;
    overflow: auto;
    max-height: 40vh;
  }

  .task {
    display: flex;
    align-items: center;
    padding: 5px 30px;
    transition: 0.2s;
    cursor: pointer;

    &:hover {background-color: rgba(255, 255, 255, 0.2);}
  }

  .task__icon {
    position: relative;
    font-size: 20px;
    width: 1.5rem;
    height: 1.5rem;

    &:before {padding-top: 3px;}
    &:after {
      content: '';
      position: absolute;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      border-radius: 99em;
      background-color: currentColor;
      aspect-ratio: 1;
      opacity: 0;
    }
  }

  .task--completed .task__icon {color: $completed-color;}
  .task:hover .task__icon:after {opacity: 0.25;}

  .task__label {
    position: relative;
    margin-left: 8px;
    cursor: inherit;

    &:before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      width: 0;
      transform: translateY(-50%);
      border-top: 1px solid $completed-color;
      transition: 0.2s ease-in-out;
    }
  }

  .task--completed .task__label {
    color: $completed-color;

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

  .task__delete {
    margin-left: 8px;
    border-radius: 99em;
    background-color: rgba(255, 0, 0, 0.35);
    color: #fff;
    border: none;
    width: 1.5rem;
    aspect-ratio: 1;
    transition: 0.25s;
    cursor: pointer;
    opacity: 0;

    &:hover {background-color: rgba(255, 0, 0, 0.6);}

    &:before {padding-top: 3px;}
  }
  .task:hover .task__delete {opacity: 1;}

  .task--new {
    i, button {flex-shrink: 0;}

    input {
      margin: 0 8px;
      border: none;
      background: rgb(255 255 255 / 30%);
      padding: 6px 8px;
      width: 100%;
      border-radius: 4px;
      outline: none;
    }
    &.checked input {
      color: rgba(0, 150, 136, 0.5);
      text-decoration: line-through;
    }

    button {
      margin-left: auto;
      border-radius: 99em;
      border: none;
      background-color: rgba($completed-color, 0.5);
      width: 1.5rem;
      aspect-ratio: 1;
      color: #fff;
      cursor: pointer;
      opacity: 0;
      outline: none;
      font-size: 12px;
      transition: 0.3s;

      &:hover {background-color: rgba($completed-color, 0.8);}
    }

    &:hover button {opacity: 1;}
  }

  .context-menu {
    position: absolute;
    top: 100%;
    right: 0;
  }
}
</style>
