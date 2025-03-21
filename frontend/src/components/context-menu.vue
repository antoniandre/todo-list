<template lang="pug">
Teleport(to="body")
  transition(name="scale")
    .context-menu(v-if="visible" ref="menuElement" :style="position")
      .context-menu__header
        h3 Assign Task
        button.context-menu__close(@click="$emit('close')")
          i.mdi.mdi-close
      .context-menu__content
        .field
          label.field__label(for="assignee") Assign to:
          .field__input-container
            select.field__input#assignee(v-model="selectedAssignee")
              option(value="") Unassigned
              option(v-for="user in users" :key="user.id" :value="user.id")
                | {{ user.firstName }} {{ user.lastName }}
            .field__icon
              i.mdi.mdi-chevron-down
        button.context-menu__button(@click="saveChanges")
          i.mdi.mdi-check
          span Save
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
  if (newTask?.assignee) {
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

<style lang="scss">
.context-menu {
  position: fixed;
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  z-index: 1000;
  min-width: 300px;
  max-width: 95vw;
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

  /* Field styles */
  .field {
    margin-bottom: 1.5rem;

    &__label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--text-dark);
      font-size: 0.95rem;
    }

    &__input-container {
      position: relative;
      display: flex;
      align-items: center;
    }

    &__input {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid rgba(0, 0, 0, 0.15);
      border-radius: var(--border-radius-sm);
      font-size: 1rem;
      color: var(--text-dark);
      background: white;
      transition: var(--transition-fast);
      appearance: none;

      &:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(58, 86, 228, 0.1);
      }

      &::placeholder {
        color: var(--text-muted);
      }
    }

    &__icon {
      position: absolute;
      right: 1rem;
      color: var(--text-light);
      pointer-events: none;
    }
  }

  /* Responsive styles for mobile devices */
  @media (max-width: 768px) {
    width: 90vw;
    max-height: 80vh;
    overflow-y: auto;
    position: fixed;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;

    &__header {
      padding: 0.85rem 1.25rem;
      position: sticky;
      top: 0;
      z-index: 2;

      h3 {
        font-size: 1rem;
      }
    }

    &__content {
      padding: 1.25rem;
    }

    &__button {
      padding: 0.85rem;
      font-size: 0.95rem;
    }

    .field {
      margin-bottom: 1.25rem;

      &__input {
        padding: 0.7rem 0.9rem;
        font-size: 16px; /* Prevent zoom on iOS */
      }
    }

    /* Custom scrollbar for context menu */
    &::-webkit-scrollbar {
      width: 4px;
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
</style>
