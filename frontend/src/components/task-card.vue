<template lang="pug">
div(
  :class="['task-card', `task-card--${task.id}`, `task-card--${task.status}`, { 'task-card--dragging': isDragging }]"
  draggable="true"
  @dragstart="onDragStart"
  @dragend="onDragEnd"
  @contextmenu.prevent="$emit('context-menu', task)"
  @touchstart="onTouchStart"
  @touchmove="onTouchMove"
  @touchend="onTouchEnd"
  ref="taskCardRef"
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

  // Mobile swipe actions
  .task-card__swipe-actions(v-if="isMobile")
    button.task-card__swipe-action.task-card__swipe-action--edit(
      @click.stop="$router.push(`/task/${task.id}`)"
      title="Edit task"
    )
      i.i-edit
    button.task-card__swipe-action.task-card__swipe-action--delete(
      @click.stop="$emit('delete', task.id)"
      title="Delete task"
    )
      i.i-trash
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

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
const isMobile = ref(false)
const taskCardRef = ref(null)

// Touch gesture handling
const touchStartX = ref(0)
const touchEndX = ref(0)
const swiping = ref(false)
const swipeAmount = ref(0)
const SWIPE_THRESHOLD = 80 // Minimum swipe distance to trigger action

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
  // Don't allow drag on mobile
  if (isMobile.value) {
    e.preventDefault()
    return
  }

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

// Mobile touch gestures
const onTouchStart = (e) => {
  if (!isMobile.value) return
  touchStartX.value = e.touches[0].clientX
  swiping.value = true
  swipeAmount.value = 0

  if (taskCardRef.value) {
    // Reset any previous transform
    taskCardRef.value.style.transform = 'translateX(0)'
  }
}

const onTouchMove = (e) => {
  if (!swiping.value || !isMobile.value) return

  touchEndX.value = e.touches[0].clientX
  swipeAmount.value = touchEndX.value - touchStartX.value

  // Limit the swipe distance
  if (swipeAmount.value < -100) swipeAmount.value = -100
  if (swipeAmount.value > 0) swipeAmount.value = 0

  if (taskCardRef.value) {
    taskCardRef.value.style.transform = `translateX(${swipeAmount.value}px)`
  }
}

const onTouchEnd = () => {
  if (!swiping.value || !isMobile.value) return

  swiping.value = false

  if (Math.abs(swipeAmount.value) > SWIPE_THRESHOLD) {
    // Reveal action buttons
    if (taskCardRef.value) {
      taskCardRef.value.style.transform = 'translateX(-80px)'
    }
  } else {
    // Reset position
    if (taskCardRef.value) {
      taskCardRef.value.style.transform = 'translateX(0)'
    }
  }
}

function checkScreenSize() {
  isMobile.value = window.innerWidth <= 768
}

onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize)
})
</script>

<style lang="scss">
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
  margin-bottom: 1rem;

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
    word-break: break-word;
  }

  &__description {
    font-size: 0.95rem;
    color: var(--text-light);
    line-height: 1.5;
    word-break: break-word;
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

  /* Responsive styles for tablets */
  @media (max-width: 1024px) {
    padding: 1rem;

    &__label {
      font-size: 1rem;
    }

    &__description {
      font-size: 0.9rem;
    }
  }

  /* Responsive styles for mobile devices */
  @media (max-width: 768px) {
    padding: 0.9rem;

    /* Make actions always visible on mobile for better UX */
    &__actions {
      visibility: visible;
      opacity: 1;
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 4px;
      padding: 3px;
    }

    &:hover {
      transform: none; /* Disable hover lift effect on mobile */
    }

    &__action {
      width: 26px;
      height: 26px;
      font-size: 0.8rem;
    }

    /* Adjust new task input for mobile */
    &--new {
      padding: 0.6rem 0.9rem;
    }

    &__input {
      font-size: 0.95rem;
    }

    &__add {
      width: 30px;
      height: 30px;
    }
  }

  &__swipe-actions {
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    display: flex;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;

    .task-card[style*="translateX(-80px)"] & {
      opacity: 1;
    }
  }

  &__swipe-action {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    box-shadow: var(--shadow-sm);
    transition: var(--transition-fast);

    &--edit {
      background: rgba(58, 86, 228, 0.2);
      color: var(--primary-color);
    }

    &--delete {
      background: rgba(233, 30, 99, 0.2);
      color: var(--danger-color);
    }

    &:active {
      transform: scale(0.9);
    }
  }
}
</style>
