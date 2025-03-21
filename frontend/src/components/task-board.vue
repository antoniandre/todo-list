<template lang="pug">
.task-board(
  :class="{ [`task-board--${status.value}`]: true, 'task-board--drag-over': isDragOver, 'task-board--collapsed': isCollapsed }"
  @dragover.prevent="onDragOver"
  @dragleave="onDragLeave"
  @drop="onDrop"
)
  .task-board__header
    .task-board__header-left
      .task-board__icon
        i.mdi(:class="`mdi-${status.icon}`")
      h2.task-board__title {{ status.label }}
    .task-board__count {{ tasks.length }}
    button.task-board__collapse-btn(
      v-if="isMobile"
      @click="toggleCollapse"
      :class="{ 'task-board__collapse-btn--collapsed': isCollapsed }"
      aria-label="Toggle board visibility"
    )
      i.mdi(:class="isCollapsed ? 'mdi-chevron-down' : 'mdi-chevron-up'")

  .task-board__content(v-show="!isCollapsed")
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
          i.mdi.mdi-plus
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import TaskCard from './task-card.vue'

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
const isCollapsed = ref(false)
const isMobile = ref(false)

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

function toggleCollapse () {
  isCollapsed.value = !isCollapsed.value
}

function checkScreenSize () {
  isMobile.value = window.innerWidth <= 768

  // If switching from mobile to desktop, make sure boards are expanded
  if (!isMobile.value) {
    isCollapsed.value = false
  }
}

// For mobile UI, monitor screen size
onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize)
})
</script>

<style lang="scss">
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
  min-height: 200px;

  &--todo {border-top: 5px solid var(--board-todo-color);}
  &--doing {border-top: 5px solid var(--board-doing-color);}
  &--done {border-top: 5px solid var(--board-done-color);}

  &--drag-over {
    transform: scale(1.02);
    box-shadow: var(--shadow-lg);
    border-color: rgba(0, 0, 0, 0.1);
  }

  &--collapsed {min-height: auto;}

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
    font-size: 1.8rem;
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
    max-height: calc(100vh - 260px);

    /* Custom scrollbar */
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

  &__collapse-btn {
    display: none;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    color: var(--text-light);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.25rem;
    margin-left: 0.5rem;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    transition: transform 0.3s ease, background-color 0.3s ease;

    &:hover {
      background-color: rgba(0, 0, 0, 0.05);
    }

    &--collapsed {
      transform: rotate(180deg);
    }
  }

  /* Responsive styles for tablets */
  @media (max-width: 1024px) {
    &__header {
      padding: 0.85rem 1.25rem;
    }

    &__title {
      font-size: 1rem;
    }

    &__content {
      padding: 0.85rem;
    }
  }

  /* Responsive styles for mobile devices */
  @media (max-width: 768px) {
    min-height: auto;
    max-height: none;
    margin-bottom: 1rem;

    &__header {
      padding: 0.75rem 1rem;
      position: sticky;
      top: 0;
      z-index: 2;
      border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    &__content {
      padding: 0.75rem;
      max-height: none; /* Remove height restriction on mobile */
      max-height: 350px; /* Add a reasonable height limit for each board */
    }

    &__collapse-btn {
      display: flex;
    }

    &--collapsed {
      .task-board__header {
        border-bottom: none;
      }
    }
  }
}
</style>
