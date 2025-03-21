<template lang="pug">
Teleport(to="body")
  transition(name="scale")
    .context-menu(v-if="visible" ref="menuElement" :style="position")
      .context-menu__header
        h3 Assign Task
        button.context-menu__close(@click="$emit('close')")
          i.i-x
      .context-menu__content
        .field
          label.field__label(for="assignee") Assign to:
          .field__input-container
            select.field__input#assignee(v-model="selectedAssignee")
              option(value="") Unassigned
              option(v-for="user in users" :key="user.id" :value="user.id")
                | {{ user.firstName }} {{ user.lastName }}
            .field__icon
              i.i-chevron-down
        button.context-menu__button(@click="saveChanges")
          i.i-check
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
