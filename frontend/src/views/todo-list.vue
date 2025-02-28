<template>
  <div class="main-content main-content--todo-list">
    <h1 class="main-content__title">To Do List</h1>
    <div v-if="error" class="message message--error">Oops. Something went wrong.</div>

    <div class="w-flex gap4">
      <div
        v-for="(status, i) in todoStatuses"
        :key="i"
        :class="[
          'task-list',
          `task-list--${status.value}`,
          { 'task-list--drag-over': draggingOverColumn === status.value }
        ]"
        @dragover.prevent="onTaskDragOver(status.value)"
        @dragleave="onTaskDragLeave"
        @drop="onTaskDrop($event, status)"
      >
        <h2 class="text-center lh1">{{ status.label }}</h2>

        <ul>
          <li
            v-for="task in tasksPerStatus[status.value]"
            :key="task.id"
            draggable="true"
            @dragstart="onTaskDragStart($event, task)"
            @dragend="onTaskDragEnd(task)"
            @contextmenu.prevent="openContextMenu(task)"
            :class="['task', `task--${task.id}`, `task--${task.status}`, { 'task--dragging': task.dragging }]"
          >
            <label class="task__label">{{ task.label }}</label>
            <router-link :to="`/task/${task.id}`" class="task__open-link arrow i-arrow-right" @click.stop></router-link>
            <button class="task__delete i-cross" @click.stop="deleteTask(task.id)"></button>
          </li>

          <li ref="newTaskElement" class="task task--new">
            <input
              v-model="newTasks[status.value].label"
              @keypress.enter="saveNewTask(newTasks[status.value], i)"
              type="text"
              placeholder="New task..."
              class="input-field"
            />
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
import { ref, reactive, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { setHeaders } from '@/helpers'

const router = useRouter()
const tasks = ref([])
const users = ref([])
const error = ref(false)

const todoStatuses = [
  { value: 'todo', label: 'To Do' },
  { value: 'doing', label: 'Doing' },
  { value: 'done', label: 'Done' }
]

const newTasks = reactive(todoStatuses.reduce((acc, status) => {
  acc[status.value] = { label: '' }
  return acc
}, {}))

const draggingOverColumn = ref(null)

const contextMenu = ref({ show: false, task: {} })
const contextMenuElement = ref(null)
const newTaskElement = ref([])

const tasksPerStatus = computed(() => {
  const map = {}
  tasks.value.forEach(task => {
    if (!map[task.status]) map[task.status] = []
    map[task.status].push(task)
  })
  return map
})

const copyComputedStyles = (source, target) => {
  const computedStyle = getComputedStyle(source)
  for (const property of computedStyle) {
    target.style[property] = computedStyle.getPropertyValue(property)
  }
}

const onTaskDragStart = (e, task) => {
  task.dragging = true
  const original = e.target.closest('.task')
  const clone = original.cloneNode(true)

  copyComputedStyles(original, clone)

  Object.assign(clone.style, {
    position: 'absolute',
    top: '-9999px',
    left: '-9999px',
    zIndex: '9999',
    opacity: '0.85',
    transform: 'rotate(-3deg) scale(1.05)',
    pointerEvents: 'none',
    boxShadow: '0 8px 16px rgba(0,0,0,0.15)'
  })

  document.body.appendChild(clone)
  e.dataTransfer.setDragImage(clone, 10, 10)
  setTimeout(() => document.body.removeChild(clone), 0)

  e.dataTransfer.setData('taskId', task.id)
}

const onTaskDragOver = (status) => {
  draggingOverColumn.value = status
}

const onTaskDragLeave = () => {
  draggingOverColumn.value = null
}

const onTaskDragEnd = (task) => {
  task.dragging = false
}

const onTaskDrop = (e, status) => {
  e.preventDefault()
  const taskId = parseInt(e.dataTransfer.getData('taskId'))
  const task = tasks.value.find(t => t.id === taskId)

  if (task) {
    task.status = status.value
    saveTask(task)
  }

  draggingOverColumn.value = null
}

const saveTask = (task) => {
  fetch('/api/tasks', {
    method: 'PUT',
    headers: setHeaders(),
    body: JSON.stringify(task)
  }).catch(() => error.value = true)
}

const saveNewTask = (task, index) => {
  fetch('/api/tasks', {
    method: 'POST',
    headers: setHeaders(),
    body: JSON.stringify({ label: task.label, status: todoStatuses[index].value })
  }).then(res => res.json()).then(data => {
    tasks.value.push(data.task)
    task.label = ''
    nextTick(() => newTaskElement.value[index]?.scrollIntoView({ behavior: 'smooth' }))
  }).catch(() => error.value = true)
}

const deleteTask = (id) => {
  fetch('/api/tasks', {
    method: 'DELETE',
    headers: setHeaders(),
    body: JSON.stringify({ id })
  }).then(() => {
    tasks.value = tasks.value.filter(t => t.id !== id)
  }).catch(() => error.value = true)
}

const openContextMenu = (task) => {
  contextMenu.value = { show: true, task }
  nextTick(() => {
    const rect = document.querySelector(`.task--${task.id}`).getBoundingClientRect()
    contextMenuElement.value.style.left = `${rect.left}px`
    contextMenuElement.value.style.top = `${rect.bottom}px`
    contextMenuElement.value.style.width = `${rect.width}px`
  })
}

onMounted(() => {
  fetch('/api/tasks', { headers: setHeaders() })
    .then(res => res.json())
    .then(data => { tasks.value = data.tasks; users.value = data.users })
    .catch(() => error.value = true)

  document.addEventListener('click', closeContextMenu)
})

onBeforeUnmount(() => document.removeEventListener('click', closeContextMenu))

const closeContextMenu = (e) => {
  if (!contextMenu.value.show || e.target.closest('.context-menu')) return
  contextMenu.value.show = false
}
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
      transition: background-color 0.3s ease;

      &--drag-over {
        background-color: rgba(255, 255, 255, 0.25) !important;
        outline: 2px dashed rgba(255, 255, 255, 0.5);
      }

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

      &.task-list--drag-over {
        background-color: rgba(255, 255, 255, 0.15);
        transition: background-color 0.3s ease;
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
      transition: transform 0.2s ease, opacity 0.2s ease;

      &:hover {background-color: rgba(255, 255, 255, 0.2);}
      &--focused {background-color: rgba(255, 255, 255, 0.2);}
      &--dragging {
        opacity: 0.5;
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
      }

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
