<template>
<div class="main-content main-content--todo-list">
  <h1 class="main-content__title">To Do List</h1>
  <div v-if="error" class="message message--error">Oops. Something went wrong.</div>
  <div class="w-flex gap4">
    <div
      v-for="(status, i) in todoStatuses"
      :key="i"
      :class="`task-list task-list--${status.value} w-flex column`">
      <h2 class="text-center lh1">{{ status.label }}</h2>
      <ul
        class="grow"
        @drop="onTaskDrop($event, status.value)"
        @dragover.prevent>
        <li
          v-for="task in tasksPerStatus[status.value]"
          :key="task.id"
          @contextmenu.prevent="openContextMenu(task)"
          draggable="true"
          @dragstart="onTaskDragStart($event, task)"
          :class="{ [`task task--${task.id} task--${status.value}`]: true, 'task--focused': task.focused }">
          <label class="task__label">{{ task.label }}</label>
          <router-link :to="`/task/${task.id}`" class="task__open-link arrow i-arrow-right" @click.stop></router-link>
          <button class="task__delete i-cross" @click.stop="deleteTask(task.id)"></button>
        </li>
        <!-- New task. -->
        <li
          ref="newTaskElement"
          class="task task--new">
          <input
            v-model="newTasks[status.value].label"
            @keypress.enter="saveNewTask(newTasks[status.value], i)"
            type="text"
            placeholder="New task..."
            class="input-field">
          <button @click="saveNewTask(newTasks[status.value], i)">OK</button>
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
    <button @click="saveTask(contextMenu.task)">OK</button>
  </div>
</div>
</template>

<script setup>
import { computed, reactive, ref, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { setHeaders } from '@/helpers'

const router = useRouter()
const loading = ref(false)
const tasks = ref([])
const todoStatuses = [
  { value: 'todo', label: 'To Do' },
  { value: 'doing', label: 'Doing' },
  { value: 'done', label: 'Done' }
]
const newTasks = reactive(todoStatuses.reduce((obj, status) => {
  obj[status.value] = { id: null, label: '' }
  return obj
}, {}))

const newTaskElement = ref(null)
const users = ref([])
const error = ref(false)
const contextMenu = ref({
  show: false,
  task: {}
})
const contextMenuElement = ref(null)


// Group tasks by status in a single computed for performance.
const tasksPerStatus = computed(() => {
  const tasksPerStatus = {}
  tasks.value.forEach(task => {
    if (!tasksPerStatus[task.status]) tasksPerStatus[task.status] = []
    tasksPerStatus[task.status].push(task)
  })

  return tasksPerStatus
})

const saveTask = task => {
  fetch('/api/tasks', {
    method: 'put',
    headers: setHeaders(),
    body: JSON.stringify({ id: task.id, status: task.status })
  })
    .then(response => response.json())
    .then(response => {
      task.status = response.task.status
      loading.value = false
    }).catch(e => {
      error.value = true
      console.log(e)
    })
}

const saveNewTask = (task, columnIndex) => {
  fetch('/api/tasks', {
    method: 'post',
    headers: setHeaders(),
    body: JSON.stringify({ label: task.label, status: todoStatuses[columnIndex].value })
  })
    .then(response => response.json())
    .then(response => {
      tasks.value.push(response.task)
      task.label = ''
      nextTick(() => {
        newTaskElement.value[columnIndex].scrollIntoView({ behavior: 'smooth' })
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
    const { left, top, width, height } = taskDomNode.getBoundingClientRect()
    contextMenuElement.value.style.left = left + 'px'
    contextMenuElement.value.style.width = width + 'px'
    contextMenuElement.value.style.top = top + height + 'px'
  })
}

const closeContextMenu = e => {
  if (!contextMenu.value.show || e.target.closest('.context-menu')) return

  contextMenu.value.show = false
  contextMenu.value.task.focused = false
  contextMenu.value.task = null
}

const onTaskDragStart = (e, task) => {
  task.focused = true
  e.dataTransfer.setData('taskId', task.id)
}

const onTaskDrop = (e, status) => {
  e.preventDefault()
  const taskId = ~~e.dataTransfer.getData('taskId')
  const task = tasks.value.find(task => task.id === taskId)
  task.status = status
  saveTask(task)
}

fetch('/api/tasks', {
  method: 'get',
  headers: setHeaders()
})
  .then(response => {
    if (!response.ok) {
      error.value = true
      if (response.status === 403) router.push('/login')
    }
    else return response.json()
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

  &:before {display: none;}

  .main-content__title {
    margin: 20px 0 10px;
    text-align: center;
  }

  .task-list {
    position: relative;
    list-style-type: none;
    overflow: auto;
    max-height: 40vh;
    flex-grow: 1;
    padding-top: 20px;
    padding-bottom: 20px;

    &:before {
      content: "";
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      right: 0;
      background-color: rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(2px);
      border-radius: 8px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
      z-index: -1;
    }

    h2 {margin-bottom: 0.3em;}
  }

  .task {
    position: relative;
    display: flex;
    align-items: center;
    padding: 5px 20px;
    transition: 0.2s;
    cursor: pointer;

    &:hover {background-color: rgba(255, 255, 255, 0.2);}
    &--focused {background-color: rgba(255, 255, 255, 0.2);}
  }

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
      border-top: 1px solid $primary-color;
      transition: 0.2s ease-in-out;
    }
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

    &:hover {
      transform: translateX(4px);
      background-color: rgba(255, 255, 255, 0.25);
    }

    &:before {padding-top: 0.2rem;}
  }

  .task__delete {
    flex-shrink: 0;
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

    .input-field {margin: 0 8px;}

    button {
      margin-left: auto;
      border-radius: 99em;
      border: none;
      background-color: rgba($primary-color, 0.5);
      width: 1.5rem;
      aspect-ratio: 1;
      color: #fff;
      cursor: pointer;
      opacity: 0;
      outline: none;
      font-size: 12px;
      transition: 0.3s;

      &:hover {background-color: rgba($primary-color, 0.8);}
    }

    &:hover button {opacity: 1;}
  }

  .context-menu {
    position: fixed;
    margin-top: -1px;
    padding: 20px;
    flex-grow: 1;
    z-index: 20;

    &:before {
      content: "";
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      right: 0;
      background-color: rgba(255, 255, 255, 0.12);
      box-shadow: 0 12px 12px rgba(0, 0, 0, 0.05);
      border-radius: 0 0 8px 8px;
      backdrop-filter: blur(25px);
      z-index: -1;
    }
  }
}
</style>
