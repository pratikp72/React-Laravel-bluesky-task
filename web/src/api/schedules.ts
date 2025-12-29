import { http } from './client'
import type { ApiResponse, ScheduledPost } from '../types/api'

export type SchedulePostDto = {
  account_id: number
  content: string
  publish_at: string
}

export type PublishNowDto = {
  account_id: number
  content: string
}

export const listSchedules = async () => {
  const response = await http.get<ApiResponse<ScheduledPost[]>>('/schedules')
  return response.data.data
}

export const createSchedule = async (payload: SchedulePostDto) => {
  const response = await http.post<ApiResponse<ScheduledPost>>('/schedules', payload)
  return response.data.data
}

export const publishNow = async (payload: PublishNowDto) => {
  const response = await http.post<ApiResponse<ScheduledPost>>('/schedules/send-now', payload)
  return response.data.data
}

export const sendNow = async (id: number) => {
  const response = await http.post<ApiResponse<ScheduledPost>>(`/schedules/${id}/send`)
  return response.data.data
}

export const cancelSchedule = async (id: number) => {
  const response = await http.delete<ApiResponse<ScheduledPost>>(`/schedules/${id}`)
  return response.data.data
}
