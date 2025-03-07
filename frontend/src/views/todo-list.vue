<template>
  <div class="main-content main-content--todo-list">
    <!-- Header with improved visuals -->
    <div class="todo-header">
      <div class="todo-header__title-container">
        <h1 class="todo-header__title">TaskFlow</h1>
        <p class="todo-header__subtitle">Organize your workflow</p>
      </div>
      <div class="todo-header__stats">
        <div class="stat stat--total">
          <div class="stat__icon">
            <i class="i-clipboard"></i>
          </div>
          <div class="stat__info">
            <span class="stat__number">{{ tasks.length }}</span>
            <span class="stat__label">Total Tasks</span>
          </div>
        </div>
        <div class="stat stat--completed">
          <div class="stat__icon">
            <i class="i-check"></i>
          </div>
          <div class="stat__info">
            <span class="stat__number">{{ tasks.filter(t => t.status === 'done').length }}</span>
            <span class="stat__label">Completed</span>
          </div>
        </div>
        <div class="stat stat--progress">
          <div class="stat__icon">
            <i class="i-chart"></i>
          </div>
          <div class="stat__info">
            <div class="progress-bar">
              <div class="progress-bar__fill" :style="{width: `${tasks.length ? (tasks.filter(t => t.status === 'done').length / tasks.length) * 100 : 0}%`}"></div>
            </div>
            <span class="stat__label">{{ tasks.length ? Math.round((tasks.filter(t => t.status === 'done').length / tasks.length) * 100) : 0 }}% Complete</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Error message with animation -->
    <transition name="fade">
      <div v-if="error" class="message message--error">
        <i class="i-alert-circle"></i>
        <span>Oops! Something went wrong. Please try again.</span>
      </div>
    </transition>

    <!-- Task boards with improved layout -->
    <div class="task-boards">
      <div
        v-for="(status, i) in todoStatuses"
        :key="i"
        :class="[
          'task-board',
          `task-board--${status.value}`,
          { 'task-board--drag-over': draggingOverColumn === status.value }
        ]"
        @dragover.prevent="onTaskDragOver(status.value)"
        @dragleave="onTaskDragLeave"
        @drop="onTaskDrop($event, status)"
      >
        <div class="task-board__header">
          <div class="task-board__header-left">
            <div class="task-board__icon">
              <i :class="`i-${status.icon}`"></i>
            </div>
            <h2 class="task-board__title">{{ status.label }}</h2>
          </div>
          <div class="task-board__count">{{ tasksPerStatus[status.value]?.length || 0 }}</div>
        </div>

        <div class="task-board__content">
          <div class="task-list">
            <transition-group name="task-transition">
              <div
                v-for="task in tasksPerStatus[status.value]"
                :key="task.id"
                draggable="true"
                @dragstart="onTaskDragStart($event, task)"
                @dragend="onTaskDragEnd(task)"
                @contextmenu.prevent="openContextMenu(task)"
                :class="['task-card', `task-card--${task.id}`, `task-card--${task.status}`, { 'task-card--dragging': task.dragging }]"
              >
                <div class="task-card__content">
                  <div class="task-card__header">
                    <label class="task-card__label">{{ task.label }}</label>
                    <div class="task-card__actions">
                      <router-link :to="`/task/${task.id}`" class="task-card__action task-card__action--edit" title="Edit task">
                        <i class="i-edit"></i>
                      </router-link>
                      <button class="task-card__action task-card__action--delete" @click.stop="deleteTask(task.id)" title="Delete task">
                        <i class="i-trash"></i>
                      </button>
                    </div>
                  </div>
                  <div v-if="task.description" class="task-card__description">
                    {{ task.description }}
                  </div>
                  <div class="task-card__footer">
                    <div v-if="task.assignee" class="task-card__assignee">
                      <div class="avatar">
                        {{ getUserInitials(task.assignee) }}
                      </div>
                      <span>{{ getUserName(task.assignee) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </transition-group>

            <!-- New task input with improved visuals -->
            <div class="task-card task-card--new">
              <input
                v-model="newTasks[status.value].label"
                @keypress.enter="saveNewTask(newTasks[status.value], i)"
                type="text"
                placeholder="Add new task..."
                class="task-card__input"
              />
              <button class="task-card__add" @click="saveNewTask(newTasks[status.value], i)" title="Add task">
                <i class="i-plus"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Improved context menu -->
    <transition name="scale">
      <div v-if="contextMenu.show" ref="contextMenuElement" class="context-menu">
        <div class="context-menu__header">
          <h3>Assign Task</h3>
          <button class="context-menu__close" @click="contextMenu.show = false">
            <i class="i-x"></i>
          </button>
        </div>
        <div class="context-menu__content">
          <div class="field">
            <label for="assignee" class="field__label">Assign to:</label>
            <div class="field__input-container">
              <select v-model="contextMenu.task.assignee" id="assignee" class="field__input">
                <option value="">Unassigned</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.firstName }} {{ user.lastName }}
                </option>
              </select>
              <div class="field__icon">
                <i class="i-chevron-down"></i>
              </div>
            </div>
          </div>
          <button class="context-menu__button" @click="saveTask(contextMenu.task)">
            <i class="i-check"></i>
            <span>Save</span>
          </button>
        </div>
      </div>
    </transition>
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
  { value: 'todo', label: 'To Do', icon: 'list-check' },
  { value: 'doing', label: 'In Progress', icon: 'clock' },
  { value: 'done', label: 'Completed', icon: 'check-circle' }
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

const getUserInitials = (userId) => {
  const user = users.value.find(u => u.id === userId)
  if (!user) return '?'
  return `${user.firstName.charAt(0)}${user.lastName.charAt(0)}`
}

const getUserName = (userId) => {
  const user = users.value.find(u => u.id === userId)
  if (!user) return 'Unknown'
  return `${user.firstName} ${user.lastName}`
}

const copyComputedStyles = (source, target) => {
  const computedStyle = getComputedStyle(source)
  for (const property of computedStyle) {
    target.style[property] = computedStyle.getPropertyValue(property)
  }
}

const onTaskDragStart = (e, task) => {
  task.dragging = true
  const original = e.target.closest('.task-card')
  const clone = original.cloneNode(true)

  copyComputedStyles(original, clone)

  Object.assign(clone.style, {
    position: 'absolute',
    top: `${e.clientY}px`,
    left: `${e.clientX}px`,
    zIndex: '9999',
    opacity: '0.85',
    transform: 'rotate(-3deg) scale(1.05)',
    pointerEvents: 'none',
    boxShadow: '0 8px 30px rgba(0,0,0,0.25)'
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
    // Store the original status before changing it
    task.originalStatus = task.status
    task.status = status.value

    // Only send the status field when dragging
    const updateData = { id: task.id, status: task.status }
    saveTask(updateData)
  }

  draggingOverColumn.value = null
}

const saveTask = (task) => {
  fetch('/api/tasks', {
    method: 'PUT',
    headers: setHeaders(),
    body: JSON.stringify(task)
  })
    .then(res => res.json())
    .then(data => {
      // Update the task in the local state
      const index = tasks.value.findIndex(t => t.id === task.id)
      if (index !== -1) {
        tasks.value[index] = data.task
      }
    })
    .catch(() => {
      error.value = true
      // Revert the status change if the update failed
      const task = tasks.value.find(t => t.id === task.id)
      if (task) {
        task.status = task.originalStatus
      }
    })
}

const saveNewTask = (task, index) => {
  if (!task.label.trim()) return

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
    const rect = document.querySelector(`.task-card--${task.id}`).getBoundingClientRect()
    contextMenuElement.value.style.left = `${rect.left}px`
    contextMenuElement.value.style.top = `${rect.bottom + 10}px`
    contextMenuElement.value.style.width = `${Math.max(rect.width, 300)}px`
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
:root {
  --primary-color: #3a56e4;
  --primary-light: #4895ef;
  --secondary-color: #2d28a7;
  --success-color: #0bb5e0;
  --danger-color: #e91e63;
  --warning-color: #f76707;
  --info-color: #3a56e4;
  --text-dark: #1a1c2a;
  --text-light: #5d6785;
  --text-muted: #8d99ae;
  --background-light: #f8f9fa;
  --card-bg: rgba(255, 255, 255, 0.95);
  --border-radius: 12px;
  --border-radius-sm: 8px;
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
  --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.18);
  --transition-fast: 0.2s ease;
  --transition-normal: 0.3s ease;
  --board-todo-color: #3a56e4;
  --board-doing-color: #e91e63;
  --board-done-color: #0bb5e0;
}

.main-content--todo-list {
  padding: 2rem;
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  position: relative;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(3px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border-radius: 24px;
  margin: 1rem;

  .todo-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-md);
    backdrop-filter: blur(10px);

    &__title-container {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    &__title {
      margin: 0;
      font-size: 2.5rem;
      font-weight: 800;
      background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      letter-spacing: -0.5px;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    &__subtitle {
      margin: 0;
      color: var(--text-dark);
      font-size: 1.1rem;
      font-weight: 500;
    }

    &__stats {
      display: flex;
      gap: 1.5rem;
    }
  }

  .stat {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: var(--border-radius-sm);
    box-shadow: var(--shadow-sm);
    transition: var(--transition-fast);
    min-width: 180px;
    border: 1px solid rgba(0, 0, 0, 0.05);

    &:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
    }

    &__icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 45px;
      height: 45px;
      border-radius: 50%;
      font-size: 1.3rem;
    }

    &--total .stat__icon {
      background: rgba(58, 86, 228, 0.15);
      color: var(--primary-color);
    }

    &--completed .stat__icon {
      background: rgba(11, 181, 224, 0.15);
      color: var(--success-color);
    }

    &--progress .stat__icon {
      background: rgba(233, 30, 99, 0.15);
      color: var(--danger-color);
    }

    &__info {
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
    }

    &__number {
      font-size: 1.6rem;
      font-weight: bold;
      color: var(--text-dark);
    }

    &__label {
      font-size: 0.9rem;
      color: var(--text-light);
      font-weight: 500;
    }

    .progress-bar {
      height: 8px;
      width: 100%;
      background: rgba(0, 0, 0, 0.08);
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 0.3rem;

      &__fill {
        height: 100%;
        background: linear-gradient(to right, var(--primary-color), var(--success-color));
        border-radius: 4px;
        transition: width 0.5s ease;
      }
    }
  }

  .message {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius-sm);
    margin-bottom: 1rem;

    &--error {
      background: rgba(233, 30, 99, 0.15);
      color: #d81b60;
      border-left: 4px solid #d81b60;
      font-weight: 500;
    }
  }

  .task-boards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    flex: 1;
    min-height: 0;
  }

  .task-board {
    display: flex;
    flex-direction: column;
    background: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-md);
    backdrop-filter: blur(10px);
    transition: var(--transition-normal);
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);

    &--todo {
      border-top: 5px solid var(--board-todo-color);
    }

    &--doing {
      border-top: 5px solid var(--board-doing-color);
    }

    &--done {
      border-top: 5px solid var(--board-done-color);
    }

    &--drag-over {
      transform: scale(1.02);
      box-shadow: var(--shadow-lg);
      border-color: rgba(0, 0, 0, 0.1);
    }

    &__header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      background: rgba(255, 255, 255, 0.7);
    }

    &__header-left {
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }

    &__icon {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      font-size: 1rem;
    }

    &--todo &__icon {
      background: rgba(58, 86, 228, 0.15);
      color: var(--board-todo-color);
    }

    &--doing &__icon {
      background: rgba(233, 30, 99, 0.15);
      color: var(--board-doing-color);
    }

    &--done &__icon {
      background: rgba(11, 181, 224, 0.15);
      color: var(--board-done-color);
    }

    &__title {
      margin: 0;
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-dark);
    }

    &__count {
      background: rgba(0, 0, 0, 0.08);
      padding: 0.25rem 0.75rem;
      border-radius: 1rem;
      font-size: 0.9rem;
      color: var(--text-dark);
      font-weight: 600;
    }

    &__content {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;

      &::-webkit-scrollbar {
        width: 6px;
      }

      &::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.03);
        border-radius: 3px;
      }

      &::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.15);
        border-radius: 3px;
      }
    }
  }

  .task-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .task-card {
    background: white;
    border-radius: var(--border-radius-sm);
    padding: 1.25rem;
    transition: var(--transition-fast);
    cursor: grab;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      transition: var(--transition-fast);
    }

    &--todo::before {
      background: var(--board-todo-color);
    }

    &--doing::before {
      background: var(--board-doing-color);
    }

    &--done::before {
      background: var(--board-done-color);
    }

    &:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
      border-color: rgba(0, 0, 0, 0.1);
    }

    &--dragging {
      opacity: 0.5;
      transform: scale(1.02);
      box-shadow: var(--shadow-lg);
    }

    &__content {
      display: flex;
      flex-direction: column;
      gap: 0.8rem;
    }

    &__header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1rem;
    }

    &__label {
      flex: 1;
      font-size: 1.05rem;
      font-weight: 600;
      color: var(--text-dark);
      line-height: 1.4;
    }

    &__description {
      font-size: 0.95rem;
      color: var(--text-light);
      line-height: 1.5;
    }

    &__footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 0.5rem;
      padding-top: 0.5rem;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    &__assignee {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
      color: var(--text-light);
      font-weight: 500;

      .avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: rgba(58, 86, 228, 0.15);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 600;
      }
    }

    &__actions {
      display: flex;
      gap: 0.5rem;
      visibility: hidden;
      opacity: 0;
      transition: var(--transition-fast);
    }

    &:hover &__actions {
      visibility: visible;
      opacity: 1;
    }

    &__action {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      border: none;
      background: rgba(0, 0, 0, 0.08);
      color: var(--text-dark);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition-fast);
      font-size: 0.9rem;

      &:hover {
        background: rgba(0, 0, 0, 0.12);
        transform: scale(1.1);
      }

      &--delete:hover {
        background: rgba(233, 30, 99, 0.2);
        color: var(--danger-color);
      }

      &--edit:hover {
        background: rgba(58, 86, 228, 0.2);
        color: var(--primary-color);
      }
    }

    &--new {
      background: white;
      border: 2px dashed rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      gap: 0.8rem;
      padding: 0.75rem 1rem;
      box-shadow: none;

      &:hover {
        background: white;
        transform: none;
        border-color: rgba(0, 0, 0, 0.15);
      }

      &::before {
        display: none;
      }
    }

    &__input {
      flex: 1;
      background: none;
      border: none;
      color: var(--text-dark);
      font-size: 1rem;
      outline: none;
      padding: 0.5rem 0;

      &::placeholder {
        color: var(--text-muted);
      }
    }

    &__add {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      border: none;
      background: rgba(58, 86, 228, 0.15);
      color: var(--primary-color);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition-fast);
      font-size: 1rem;

      &:hover {
        background: var(--primary-color);
        color: white;
        transform: scale(1.1);
      }
    }
  }

  .context-menu {
    position: fixed;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    min-width: 300px;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.1);

    &__header {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: rgba(0, 0, 0, 0.02);

      h3 {
        margin: 0;
        color: var(--text-dark);
        font-size: 1.1rem;
        font-weight: 600;
      }
    }

    &__close {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      border: none;
      background: rgba(0, 0, 0, 0.08);
      color: var(--text-dark);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition-fast);

      &:hover {
        background: rgba(0, 0, 0, 0.12);
      }
    }

    &__content {
      padding: 1.5rem;
    }

    .field {
      margin-bottom: 1.5rem;

      &__label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        color: var(--text-dark);
        font-weight: 500;
      }

      &__input-container {
        position: relative;
      }

      &__input {
        width: 100%;
        padding: 0.9rem 1rem;
        border-radius: var(--border-radius-sm);
        border: 1px solid rgba(0, 0, 0, 0.15);
        background: white;
        font-size: 1rem;
        color: var(--text-dark);
        outline: none;
        appearance: none;
        transition: var(--transition-fast);

        &:focus {
          border-color: var(--primary-color);
          box-shadow: 0 0 0 3px rgba(58, 86, 228, 0.15);
        }
      }

      &__icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-dark);
        pointer-events: none;
      }
    }

    &__button {
      width: 100%;
      padding: 1rem;
      border: none;
      background: var(--primary-color);
      color: white;
      border-radius: var(--border-radius-sm);
      cursor: pointer;
      transition: var(--transition-fast);
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-size: 1rem;

      &:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
      }
    }
  }

  // Animation classes
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
  }

  .fade-enter-from, .fade-leave-to {
    opacity: 0;
  }

  .scale-enter-active, .scale-leave-active {
    transition: all 0.3s ease;
  }

  .scale-enter-from, .scale-leave-to {
    opacity: 0;
    transform: scale(0.9);
  }

  .task-transition-move {
    transition: transform 0.5s ease;
  }

  .task-transition-enter-active {
    transition: all 0.3s ease-out;
  }

  .task-transition-leave-active {
    transition: all 0.3s ease-in;
    position: absolute;
  }

  .task-transition-enter-from {
    opacity: 0;
    transform: translateY(20px);
  }

  .task-transition-leave-to {
    opacity: 0;
    transform: translateY(-20px);
  }
}
</style>
