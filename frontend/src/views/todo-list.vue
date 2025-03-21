<template>
<div class="main-content main-content--todo-list">
  <!-- Header with improved visuals -->
  <TaskHeader :tasks="tasks" />

  <!-- Error message with animation -->
  <transition name="fade">
    <div v-if="error" class="message message--error">
      <i class="i-alert-circle"></i>
      <span>Oops! Something went wrong. Please try again.</span>
    </div>
  </transition>

  <!-- Task boards with improved layout -->
  <div class="task-boards">
    <TaskBoard
      v-for="status in todoStatuses"
      :key="status.value"
      :status="status"
      :tasks="tasksPerStatus[status.value] || []"
      :users="users"
      @delete-task="deleteTask"
      @context-menu="openContextMenu"
      @add-task="saveNewTask"
      @drop-task="onTaskDrop"
    />
  </div>

  <!-- Context menu component -->
  <ContextMenu
    :task="contextMenuTask"
    :users="users"
    :visible="showContextMenu"
    :trigger-element="contextMenuTrigger"
    @close="closeContextMenu"
    @save="saveTask"
  />
</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import TaskHeader from '@/components/TaskHeader.vue'
import TaskBoard from '@/components/TaskBoard.vue'
import ContextMenu from '@/components/ContextMenu.vue'
import TaskService from '@/services/TaskService'

// Reactive state
const tasks = ref([])
const users = ref([])
const error = ref(false)
const showContextMenu = ref(false)
const contextMenuTask = ref({})
const contextMenuTrigger = ref(null)

// Task statuses configuration
const todoStatuses = [
  { value: 'todo', label: 'To Do', icon: 'list-check' },
  { value: 'doing', label: 'In Progress', icon: 'clock' },
  { value: 'done', label: 'Completed', icon: 'check-circle' }
]

// Computed properties
const tasksPerStatus = computed(() => {
  const map = {}
  for (const status of todoStatuses) {
    map[status.value] = tasks.value.filter(task => task.status === status.value)
  }
  return map
})

// Methods
function openContextMenu (task) {
  contextMenuTask.value = task
  contextMenuTrigger.value = document.querySelector(`.task-card--${task.id}`)
  showContextMenu.value = true
}

function closeContextMenu () {
  showContextMenu.value = false
}

async function fetchTasks () {
  try {
    const data = await TaskService.getTasks()
    tasks.value = data.tasks
    users.value = data.users
  } catch (err) {
    error.value = true
  }
}

async function saveTask (task) {
  try {
    const data = await TaskService.updateTask(task)
    const index = tasks.value.findIndex(t => t.id === task.id)
    if (index !== -1) {
      tasks.value[index] = data.task
    }
    error.value = false
  } catch (err) {
    error.value = true
  }
}

async function saveNewTask (taskData) {
  if (!taskData.label.trim()) return

  try {
    const data = await TaskService.createTask(taskData)
    tasks.value.push(data.task)
    error.value = false
  } catch (err) {
    error.value = true
  }
}

async function deleteTask (id) {
  try {
    await TaskService.deleteTask(id)
    tasks.value = tasks.value.filter(t => t.id !== id)
    error.value = false
  } catch (err) {
    error.value = true
  }
}

function onTaskDrop ({ taskId, status }) {
  const task = tasks.value.find(t => t.id === taskId)
  if (!task) return

  // Store the original status for rollback
  const originalStatus = task.status

  // Optimistically update
  task.status = status

  // Save changes
  saveTask({ id: task.id, status })
    .catch(() => {
      // Rollback on error
      task.status = originalStatus
    })
}

// Lifecycle hooks
onMounted(() => {
  fetchTasks()
})
</script>

<style lang="scss">
@import '@/scss/task-components.scss';

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
}
</style>
