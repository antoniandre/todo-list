import { setHeaders } from '@/helpers'

export default {
  /**
   * Fetch all tasks and users
   * @returns {Promise<{tasks: Array, users: Array}>}
   */
  async getTasks () {
    try {
      const response = await fetch('/api/tasks', { headers: setHeaders() })
      const data = await response.json()
      return data
    } catch (error) {
      console.error('Error fetching tasks:', error)
      throw error
    }
  },

  /**
   * Create a new task
   * @param {Object} task - Task data to create
   * @returns {Promise<{task: Object}>}
   */
  async createTask (task) {
    try {
      const response = await fetch('/api/tasks', {
        method: 'POST',
        headers: setHeaders(),
        body: JSON.stringify(task)
      })
      const data = await response.json()
      return data
    } catch (error) {
      console.error('Error creating task:', error)
      throw error
    }
  },

  /**
   * Update an existing task
   * @param {Object} task - Task data to update
   * @returns {Promise<{task: Object}>}
   */
  async updateTask (task) {
    try {
      const response = await fetch('/api/tasks', {
        method: 'PUT',
        headers: setHeaders(),
        body: JSON.stringify(task)
      })
      const data = await response.json()
      return data
    } catch (error) {
      console.error('Error updating task:', error)
      throw error
    }
  },

  /**
   * Delete a task
   * @param {number} id - Task ID to delete
   * @returns {Promise<void>}
   */
  async deleteTask (id) {
    try {
      await fetch('/api/tasks', {
        method: 'DELETE',
        headers: setHeaders(),
        body: JSON.stringify({ id })
      })
    } catch (error) {
      console.error('Error deleting task:', error)
      throw error
    }
  }
}
