<template>
  <Teleport to="body">
    <transition name="scale">
      <div v-if="visible" ref="menuElement" class="context-menu" :style="position">
        <div class="context-menu__header">
          <h3>Assign Task</h3>
          <button class="context-menu__close" @click="$emit('close')">
            <i class="i-x"></i>
          </button>
        </div>
        <div class="context-menu__content">
          <div class="field">
            <label for="assignee" class="field__label">Assign to:</label>
            <div class="field__input-container">
              <select v-model="selectedAssignee" id="assignee" class="field__input">
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
          <button class="context-menu__button" @click="saveChanges">
            <i class="i-check"></i>
            <span>Save</span>
          </button>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  users: {
    type: Array,
    required: true
  },
  visible: {
    type: Boolean,
    default: false
  },
  triggerElement: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'save'])
const menuElement = ref(null)
const selectedAssignee = ref('')
const position = ref({
  top: '0px',
  left: '0px',
  width: '300px'
})

// Set initial assignee value when task changes
watch(() => props.task, (newTask) => {
  if (newTask && newTask.assignee) {
    selectedAssignee.value = newTask.assignee
  } else {
    selectedAssignee.value = ''
  }
}, { immediate: true })

// Update position when visible changes
watch(() => props.visible, (isVisible) => {
  if (isVisible && props.triggerElement) {
    nextTick(() => updatePosition())
  }
})

function updatePosition () {
  if (!props.triggerElement || !menuElement.value) return

  const rect = props.triggerElement.getBoundingClientRect()
  position.value = {
    left: `${rect.left}px`,
    top: `${rect.bottom + 10}px`,
    width: `${Math.max(rect.width, 300)}px`
  }
}

function saveChanges () {
  emit('save', {
    id: props.task.id,
    assignee: selectedAssignee.value
  })
  emit('close')
}

// Close on outside click
function handleClickOutside (e) {
  if (
    props.visible &&
    menuElement.value &&
    !menuElement.value.contains(e.target) &&
    (!props.triggerElement?.contains(e.target))
  ) {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
