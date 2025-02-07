<template>
<div class="main-content main-content--todo-list">
  <h1 class="main-content__title">To Do List</h1>
  <div v-if="error" class="message message--error">Oops. Something went wrong.</div>
  <div class="tasks-container">
    <div v-for="(tasks, status) in todoColumns" :key="status" class="tasks-column">
      <h2 class="task__title">{{ status }}</h2>
      <ul class="tasks">
        <li
          v-for="task in tasks"
          :key="task.id"
          @click="saveTask(task)"
          @contextmenu.prevent="openContextMenu(task)"
          :class="{ [`task task--${task.id}`]: true, 'task--completed': task.completed , 'task--focused': task.focused }">
          <i :class="`task__icon ${task.completed ? 'i-checkbox-checked' : 'i-checkbox-unchecked'}`"></i>
          <label class="task__label">{{ task.label }}</label>
          <router-link :to="`/task/${task.id}`" class="task__open-link arrow i-arrow-right" @click.stop></router-link>
          <button class="task__delete i-cross" @click.stop="deleteTask(task.id)"></button>
        </li>
      </ul>
      <!-- New task. -->
      <ul class="tasks">
        <li
          ref="newTaskElement"
          class="task task--new"
          :class="{ checked: newTask.completed }"
          @click="newTask.completed = !newTask.completed">
          <i :class="`task__icon ${newTask.completed ? 'i-checkbox-checked' : 'i-checkbox-unchecked'}`"></i>
          <input
            v-model="newTask.label"
            @click.stop
            @keypress.enter="saveNewTask(newTask)"
            type="text"
            placeholder="New task..."
            class="input-field">
          <button @click.stop="saveNewTask(newTask)">OK</button>
        </li>
      </ul>
    </div>
  </div>
  <div v-if="contextMenu.show" ref="contextMenuElement" class="context-menu">
    <label for="assignee" class="field__label">Assign to: </label>
    <select v-model="contextMenu.task.assignee" id="assignee" class="field__input">
      <option v-for="user in users" :key="user.id" :value="user.id">
        {{ user.firstName }} {{ user.lastName }}
      </option>
    </select>
  </div>
</div>
</template>

<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount, computed } from 'vue'
import { useRouter } from 'vue-router'
import { setHeaders } from '@/helpers'

const router = useRouter()
const loading = ref(false)
const tasks = ref([])
const newTask = ref({
  id: null,
  label: '',
  completed: false
})
const newTaskElement = ref(null)
const users = ref([])
const error = ref(false)
const contextMenu = ref({
  show: false,
  task: {}
})
const contextMenuElement = ref(null)

const todoColumns = computed(() => {
  const columns = {}
  tasks.value.forEach(task => {
    if (!columns[task.status]) columns[task.status] = []
    columns[task.status].push(task)
  })
  return columns
})

const saveTask = task => {
  fetch('/api/tasks', {
    method: 'put',
    headers: setHeaders(),
    body: JSON.stringify({ id: task.id, completed: !task.completed })
  })
    .then(response => response.json())
    .then(response => {
      task.completed = response.task.completed
      loading.value = false
    }).catch(e => {
      error.value = true
      console.log(e)
    })
}

const saveNewTask = () => {
  fetch('/api/tasks', {
    method: 'post',
    headers: setHeaders(),
    body: JSON.stringify({
      label: newTask.value.label,
      completed: newTask.value.completed
    })
  })
    .then(response => response.json())
    .then(response => {
      tasks.value.push(response.task)
      newTask.value = Object.assign(newTask.value, { label: '', completed: false })
      nextTick(() => {
        newTaskElement.value.scrollIntoView({ behavior: 'smooth' })
      })
    }).catch(e => {
      error.value = true
      console.log(e)
    })
}

const deleteTask = id => {
  fetch('/api/tasks', {
    method: 'delete',
    headers: setHeaders(),
    body: JSON.stringify({ id })
  })
    .then(() => {
      tasks.value = tasks.value.filter(item => item.id !== id)
    })
    .catch(e => {
      error.value = true
      console.log(e)
    })
}

const openContextMenu = task => {
  const taskDomNode = document.querySelector(`.task--${task.id}`)
  contextMenu.value.show = true
  contextMenu.value.task = task
  task.focused = true

  nextTick(() => {
    contextMenuElement.value.style.top = taskDomNode.offsetTop + taskDomNode.offsetHeight + 'px'
  })
}

const closeContextMenu = e => {
  if (e.target.matches('.context-menu, .context-menu *')) return

  contextMenu.value.show = false
  contextMenu.value.task.focused = false
  contextMenu.value.task = null
}

fetch('/api/tasks', {
  method: 'get',
  headers: setHeaders()
})
  .then(response => {
    if (!response.ok) {
      error.value = true
      if (response.status === 403) router.push('/login')
    } else return response.json()
  })
  .then(response => {
    tasks.value = response.tasks
    users.value = response.users
  })
  .catch(() => {
    error.value = true
  })

onMounted(() => {
  document.addEventListener('click', closeContextMenu)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeContextMenu)
})
</script>

<style lang="scss">
.main-content--todo-list {
  padding-top: 0;
  padding-left: 0;
  padding-right: 0;

  .main-content__title {
    margin: 20px 0 10px;
    text-align: center;
    font-size: 2rem;
    color: #333;
  }

  .tasks-container {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    margin-top: 20px;
  }

  .tasks-column {
    flex: 1;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    min-height: 300px;

    &:first-child {
      background-color: #ffe5e5; /* Rojo claro */
    }

    &:nth-child(2) {
      background-color: #ffffe0; /* Amarillo claro */
    }

    &:last-child {
      background-color: #e5ffe5; /* Verde claro */
    }

    .task__title {
      font-size: 1.5rem;
      margin-bottom: 15px;
      color: #555;
      font-weight: bold;
      text-align: center;
      text-transform: capitalize;
    }

    .tasks {
      list-style-type: none;
      margin: 0;
      padding: 0;
      overflow-y: auto;
      max-height: 80vh;
    }

    .task {
      position: relative;
      display: flex;
      align-items: center;
      padding: 10px 20px;
      margin: 5px 0;
      border-radius: 8px;
      background-color: white;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;

      &:hover {
        background-color: rgba(0, 0, 0, 0.05);
      }

      &--focused {
        background-color: rgba(0, 0, 0, 0.1);
      }
    }

    .task__icon {
      font-size: 20px;
      color: #888;
      margin-right: 15px;
      transition: color 0.3s ease;
    }

    .task--completed .task__icon {
      color: #4caf50;
    }

    .task__label {
      flex-grow: 1;
      color: #333;
      font-size: 1rem;
      transition: color 0.3s ease;
    }

    .task--completed .task__label {
      color: #4caf50;
      text-decoration: line-through;
    }

    .task__delete {
      border: none;
      background-color: #ff5733;
      color: #fff;
      padding: 5px 10px;
      border-radius: 50%;
      font-size: 16px;
      cursor: pointer;
      opacity: 0;
      transition: opacity 0.3s ease;

      &:hover {
        background-color: #ff3b2b;
      }
    }

    &:hover .task__delete {
      opacity: 1;
    }
  }

  .task--new {
    display: flex;
    align-items: center;
    background-color: #fff;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    margin: 15px 0;
    transition: background-color 0.3s ease;

    &:hover {
      background-color: #f0f0f0;
    }

    .input-field {
      flex-grow: 1;
      padding: 10px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-right: 15px;
    }

    button {
      padding: 8px 16px;
      background-color: #4caf50;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;

      &:hover {
        background-color: #45a049;
      }
    }
  }

  .context-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 10px;
    padding: 15px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    z-index: 10;

    &:before {
      content: '';
      position: absolute;
      top: -10px;
      left: 50%;
      transform: translateX(-50%);
      border-width: 10px;
      border-style: solid;
      border-color: transparent transparent white transparent;
    }

    .field__label {
      font-size: 1rem;
      margin-bottom: 10px;
    }

    .field__input {
      width: 100%;
      padding: 8px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
  }
}
</style>
