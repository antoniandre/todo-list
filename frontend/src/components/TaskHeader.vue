<template lang="pug">
.todo-header
  .todo-header__title-container
    h1.todo-header__title TaskFlow
    p.todo-header__subtitle Organize your workflow
  .todo-header__stats
    .stat.stat--total
      .stat__icon
        i.i-clipboard
      .stat__info
        span.stat__number {{ totalTasks }}
        span.stat__label Total Tasks
    .stat.stat--completed
      .stat__icon
        i.i-check
      .stat__info
        span.stat__number {{ completedTasks }}
        span.stat__label Completed
    .stat.stat--progress
      .stat__icon
        i.i-chart
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
