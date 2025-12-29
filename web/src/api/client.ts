import axios from 'axios'

const baseURL = (import.meta.env.VITE_API_BASE_URL as string) ?? 'http://localhost:8000/api/v1'

export const http = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
  },
  timeout: 12000,
})

http.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      const { data } = error.response
      const message = data?.message ?? 'Request failed'
      return Promise.reject(new Error(message))
    }

    return Promise.reject(error)
  },
)
