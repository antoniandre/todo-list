<template lang="pug">
div(
  :class="['task-card', `task-card--${task.id}`, `task-card--${task.status}`, { 'task-card--dragging': isDragging }]"
  draggable="true"
  @dragstart="onDragStart"
  @dragend="onDragEnd"
  @contextmenu.prevent="$emit('context-menu', task)"
)
  .task-card__content
    .task-card__header
      label.task-card__label {{ task.label }}
      .task-card__actions
        router-link(:to="`/task/${task.id}`" class="task-card__action task-card__action--edit" title="Edit task")
          i.i-edit
        button.task-card__action.task-card__action--delete(@click.stop="$emit('delete', task.id)" title="Delete task")
          i.i-trash
    .task-card__description(v-if="task.description")
      | {{ task.description }}
    .task-card__footer
      .task-card__assignee(v-if="task.assignee")
        .avatar
          | {{ getUserInitials(task.assignee) }}
        span {{ getUserName(task.assignee) }}
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  users: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['delete', 'context-menu', 'drag-start', 'drag-end'])

const isDragging = ref(false)

const getUserInitials = (userId) => {
  const user = props.users.find(u => u.id === userId)
  if (!user) return '?'
  return `${user.firstName.charAt(0)}${user.lastName.charAt(0)}`
}

const getUserName = (userId) => {
  const user = props.users.find(u => u.id === userId)
  if (!user) return 'Unknown'
  return `${user.firstName} ${user.lastName}`
}

const onDragStart = (e) => {
  isDragging.value = true
  e.dataTransfer.setData('taskId', props.task.id)

  // Create a ghost image for dragging
  const original = e.target.closest('.task-card')
  const clone = original.cloneNode(true)

  // Copy computed styles
  const computedStyle = getComputedStyle(original)
  for (const property of computedStyle) {
    clone.style[property] = computedStyle.getPropertyValue(property)
  }

  // Set clone styles
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

  // Emit event
  emit('drag-start', props.task)
}

const onDragEnd = () => {
  isDragging.value = false
  emit('drag-end', props.task)
}
</script>
