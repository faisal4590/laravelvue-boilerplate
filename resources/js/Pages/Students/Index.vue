<template>
  <div class="min-h-screen bg-gray-100 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <!-- Flash Messages -->
      <FlashMessage v-if="$page.props.flash?.message"
                   :message="$page.props.flash.message"
                   :type="$page.props.flash.type" />

      <!-- Search and Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
          <div class="flex-1 max-w-sm">
            <input
              v-model="search"
              type="text"
              placeholder="Search students..."
              class="w-full rounded-md border-gray-300"
              @input="debouncedSearch"
            >
          </div>
          <div class="flex items-center space-x-4">
            <select v-model="sortField" @change="updateSort" class="rounded-md border-gray-300">
              <option value="name">Name</option>
              <option value="roll">Roll Number</option>
              <option value="created_at">Date Added</option>
            </select>
            <button @click="toggleSortDirection" class="p-2">
              <icon :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'" />
            </button>
          </div>
        </div>
      </div>

      <!-- Form Section -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Add New Student</h2>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input
              type="text"
              id="name"
              v-model="form.name"
              :class="{ 'border-red-500': form.errors.name }"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              required
            >
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>
          <div>
            <label for="roll" class="block text-sm font-medium text-gray-700">
              Roll Number (Format: XX0000)
            </label>
            <input
              type="text"
              id="roll"
              v-model="form.roll"
              :class="{ 'border-red-500': form.errors.roll }"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              required
            >
            <p v-if="form.errors.roll" class="mt-1 text-sm text-red-600">{{ form.errors.roll }}</p>
          </div>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <icon name="spinner" class="animate-spin mr-2" v-if="form.processing" />
            Add Student
          </button>
        </form>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium">Students List</h3>
            <button
              v-if="selectedStudents.length > 0"
              @click="confirmBulkDelete"
              class="text-red-600 hover:text-red-900"
            >
              Delete Selected ({{ selectedStudents.length }})
            </button>
          </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="w-4 px-6 py-3">
                <input
                  type="checkbox"
                  v-model="selectAll"
                  @change="toggleSelectAll"
                  class="rounded border-gray-300"
                >
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Roll
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="student in students.data" :key="student.id"
                :class="{ 'bg-gray-50': student.editing }">
              <td class="px-6 py-4">
                <input
                  type="checkbox"
                  v-model="selectedStudents"
                  :value="student.id"
                  class="rounded border-gray-300"
                >
              </td>
              <td class="px-6 py-4 whitespace-nowrap" v-if="!student.editing">
                {{ student.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap" v-if="!student.editing">
                {{ student.roll }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap" v-if="student.editing">
                <input
                  type="text"
                  v-model="student.name"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
              </td>
              <td class="px-6 py-4 whitespace-nowrap" v-if="student.editing">
                <input
                  type="text"
                  v-model="student.roll"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <template v-if="!student.editing">
                  <button
                    v-if="can.edit"
                    @click="startEditing(student)"
                    class="text-indigo-600 hover:text-indigo-900 mr-2"
                  >
                    Edit
                  </button>
                  <button
                    v-if="can.delete"
                    @click="deleteStudent(student)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </template>
                <template v-else>
                  <button
                    @click="saveEdit(student)"
                    class="text-green-600 hover:text-green-900 mr-2"
                  >
                    Save
                  </button>
                  <button
                    @click="cancelEdit(student)"
                    class="text-gray-600 hover:text-gray-900"
                  >
                    Cancel
                  </button>
                </template>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
          <Pagination :links="students.links" />
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <Modal v-if="showDeleteModal" @close="showDeleteModal = false">
      <template #title>Confirm Deletion</template>
      <template #content>
        Are you sure you want to delete {{ selectedStudents.length }} student(s)?
        This action cannot be undone.
      </template>
      <template #footer>
        <button
          @click="showDeleteModal = false"
          class="mr-2 px-4 py-2 text-gray-600"
        >
          Cancel
        </button>
        <button
          @click="executeBulkDelete"
          class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
        >
          Delete
        </button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import {ref, computed, watch, onMounted} from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'
import FlashMessage from '@/Components/FlashMessage.vue'
import Pagination from '@/Components/Pagination.vue'
import Modal from '@/Components/Modal.vue'
import Icon from '@/Components/Icon.vue'

const props = defineProps({
  students: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    required: true
  },
  can: {
    type: Object,
    required: true
  }
})

const form = useForm({
  name: '',
  roll: ''
})

const search = ref(props.filters.search)
const sortField = ref(props.filters.sort || 'created_at')
const sortDirection = ref(props.filters.direction || 'desc')
const selectedStudents = ref([])
const selectAll = ref(false)
const showDeleteModal = ref(false)

const debouncedSearch = debounce(() => {
  router.get('/students', {
    search: search.value,
    sort: sortField.value,
    direction: sortDirection.value
  }, { preserveState: true })
}, 300)

const toggleSortDirection = () => {
  sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  updateSort()
}

const updateSort = () => {
  router.get('/students', {
    search: search.value,
    sort: sortField.value,
    direction: sortDirection.value
  }, { preserveState: true })
}

const submitForm = () => {
  form.post('/students', {
    onSuccess: () => {
      form.reset()
    }
  })
}

const startEditing = (student) => {
  student.editing = true
  student.originalName = student.name
  student.originalRoll = student.roll
}

const cancelEdit = (student) => {
  student.editing = false
  student.name = student.originalName
  student.roll = student.originalRoll
}

const saveEdit = (student) => {
  router.put(`/students/${student.id}`, {
    name: student.name,
    roll: student.roll
  }, {
    onSuccess: () => {
      student.editing = false
    }
  })
}

const deleteStudent = (student) => {
  if (confirm('Are you sure you want to delete this student?')) {
    router.delete(`/students/${student.id}`)
  }
}

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedStudents.value = props.students.data.map(s => s.id)
  } else {
    selectedStudents.value = []
  }
}

const confirmBulkDelete = () => {
  showDeleteModal.value = true
}

const executeBulkDelete = () => {
  router.post('/students/bulk-delete', {
    ids: selectedStudents.value
  }, {
    onSuccess: () => {
      showDeleteModal.value = false
      selectedStudents.value = []
      selectAll.value = false
    }
  })
}

watch(selectedStudents, (newVal) => {
  selectAll.value = newVal.length === props.students.data.length
})

onMounted(() => {
  props.students.data.forEach(student => {
    student.editing = false
    student.originalName = student.name
    student.originalRoll = student.roll
  })
})
</script>