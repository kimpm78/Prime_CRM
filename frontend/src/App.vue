<script setup>
import { onMounted, ref } from 'vue'

const customers = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const response = await fetch('/api/customers')

    if (!response.ok) {
      throw new Error(`API request failed: ${response.status}`)
    }

    const payload = await response.json()
    customers.value = payload.data ?? []
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load customers'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <main class="page">
    <section class="toolbar">
      <div>
        <p class="eyebrow">Prime CRM</p>
        <h1>Customers</h1>
      </div>
      <button type="button">New Customer</button>
    </section>

    <section class="panel">
      <p v-if="loading" class="muted">Loading customers...</p>
      <p v-else-if="error" class="error">{{ error }}</p>
      <p v-else-if="customers.length === 0" class="muted">No customers yet.</p>

      <table v-else>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Company</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="customer in customers" :key="customer.id">
            <td>{{ customer.name }}</td>
            <td>{{ customer.email }}</td>
            <td>{{ customer.company || '-' }}</td>
            <td>
              <span class="status">{{ customer.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </main>
</template>
