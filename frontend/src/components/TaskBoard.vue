<template lang="pug">
div(
  :class="[
    'task-board',
    `task-board--${status.value}`,
    { 'task-board--drag-over': isDragOver }
  ]"
  @dragover.prevent="onDragOver"
  @dragleave="onDragLeave"
  @drop="onDrop"
)
  .task-board__header
    .task-board__header-left
      .task-board__icon
        i(:class="`i-${status.icon}`")
      h2.task-board__title {{ status.label }}
    .task-board__count {{ tasks.length }}

  .task-board__content
    .task-list
      transition-group(name="task-transition")
        TaskCard(
          v-for="task in tasks"
          :key="task.id"
          :task="task"
          :users="users"
          @delete="$emit('delete-task', $event)"
          @context-menu="$emit('context-menu', $event)"
          @drag-start="taskDragStart"
          @drag-end="taskDragEnd"
        )

      // New task input
      .task-card.task-card--new
        input.task-card__input(
          v-model="newTaskLabel"
          @keypress.enter="addTask"
          type="text"
          placeholder="Add new task..."
        )
        button.task-card__add(@click="addTask" title="Add task")
          i.i-plus
</template>

<script setup>
import { ref } from 'vue'
import TaskCard from './TaskCard.vue'

const props = defineProps({
  status: {
    type: Object,
    required: true
  },
  tasks: {
    type: Array,
    required: true,
    default: () => []
  },
  users: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['delete-task', 'context-menu', 'add-task', 'drop-task'])

const isDragOver = ref(false)
const newTaskLabel = ref('')

function onDragOver () {
  isDragOver.value = true
}

function onDragLeave () {
  isDragOver.value = false
}

function onDrop (e) {
  e.preventDefault()
  isDragOver.value = false
  const taskId = Number.parseInt(e.dataTransfer.getData('taskId'))
  emit('drop-task', { taskId, status: props.status.value })
}

function taskDragStart (task) {
  // Handled by TaskCard component
}

function taskDragEnd (task) {
  // Handled by TaskCard component
}

function addTask () {
  if (!newTaskLabel.value.trim()) return

  emit('add-task', {
    label: newTaskLabel.value,
    status: props.status.value
  })

  newTaskLabel.value = ''
}
</script>
