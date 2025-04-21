<template>
  <div :class="[
    'fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg transition-all duration-500',
    typeClasses[type] || typeClasses.success
  ]">
    {{ message }}
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'success'
  }
})

const typeClasses = {
  success: 'bg-green-500 text-white',
  error: 'bg-red-500 text-white',
  warning: 'bg-yellow-500 text-white',
  info: 'bg-blue-500 text-white'
}

onMounted(() => {
  setTimeout(() => {
    document.querySelector('.flash-message').classList.add('opacity-0')
  }, 3000)
})
</script>