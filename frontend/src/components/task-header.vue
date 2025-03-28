<template lang="pug">
.todo-header
  .todo-header__title-container
    h1.todo-header__title TaskFlow
    p.todo-header__subtitle Organize your workflow
  .todo-header__stats
    .stat.stat--total
      .stat__icon
        i.mdi.mdi-clipboard-text-outline
      .stat__info
        span.stat__number {{ totalTasks }}
        span.stat__label Total Tasks
    .stat.stat--completed
      .stat__icon
        i.mdi.mdi-check-bold
      .stat__info
        span.stat__number {{ completedTasks }}
        span.stat__label Completed
    .stat.stat--progress
      .stat__icon
        i.mdi.mdi-chart-donut
      .stat__info
        .progress-bar
          .progress-bar__fill(:style="{ width: `${completionPercentage}%` }")
        span.stat__label {{ completionPercentage }}% Complete
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  tasks: {
    type: Array,
    required: true
  }
})

const totalTasks = computed(() => props.tasks.length)

const completedTasks = computed(() =>
  props.tasks.filter(t => t.status === 'done').length
)

const completionPercentage = computed(() => {
  if (!totalTasks.value) return 0
  return Math.round((completedTasks.value / totalTasks.value) * 100)
})
</script>

<style lang="scss">
.todo-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  }

  &__title {
    margin: 0;
    font-size: 2.5rem;
    height: 1.5em;
    font-weight: 800;
    background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -0.5px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  }

  &__subtitle {
    margin: 0;
    color: var(--text-light);
    font-size: 1rem;
    font-style: italic;
  }

  &__stats {
    display: flex;
    gap: 1.5rem;
  }

  /* Responsive styles for tablets */
  @media (max-width: 1024px) {
    padding: 1.25rem;

    &__title {
      font-size: 2rem;
    }

    &__subtitle {
      font-size: 1rem;
    }

    &__stats {
      gap: 1rem;
    }
  }

  /* Responsive styles for mobile devices */
  @media (max-width: 768px) {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;

    &__title {
      font-size: 1.8rem;
    }

    &__subtitle {
      font-size: 0.95rem;
    }

    &__stats {
      width: 100%;
      overflow-x: auto;
      padding-bottom: 0.5rem;

      /* Custom scrollbar for horizontal scrolling */
      &::-webkit-scrollbar {
        height: 4px;
      }

      &::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.03);
        border-radius: 2px;
      }

      &::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.15);
        border-radius: 2px;
      }
    }
  }
}

// Stats component
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
    background: rgba(255, 160, 160, 0.4);
    color: var(--board-todo-color);
  }

  &--completed .stat__icon {
    background: rgba(168, 230, 168, 0.4);
    color: var(--board-done-color);
  }

  &--progress .stat__icon {
    background: rgba(255, 219, 109, 0.4);
    color: var(--board-doing-color);
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
}
</style>
