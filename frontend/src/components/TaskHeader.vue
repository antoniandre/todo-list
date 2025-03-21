<template>
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
          <span class="stat__number">{{ totalTasks }}</span>
          <span class="stat__label">Total Tasks</span>
        </div>
      </div>
      <div class="stat stat--completed">
        <div class="stat__icon">
          <i class="i-check"></i>
        </div>
        <div class="stat__info">
          <span class="stat__number">{{ completedTasks }}</span>
          <span class="stat__label">Completed</span>
        </div>
      </div>
      <div class="stat stat--progress">
        <div class="stat__icon">
          <i class="i-chart"></i>
        </div>
        <div class="stat__info">
          <div class="progress-bar">
            <div class="progress-bar__fill" :style="{ width: `${completionPercentage}%` }"></div>
          </div>
          <span class="stat__label">{{ completionPercentage }}% Complete</span>
        </div>
      </div>
    </div>
  </div>
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
