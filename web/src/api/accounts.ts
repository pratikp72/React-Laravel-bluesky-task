import { http } from './client'
import type { ApiResponse, BlueskyAccount } from '../types/api'

export type LinkAccountDto = {
  handle: string
  app_password: string
  label?: string | null
}

export const listAccounts = async () => {
  const response = await http.get<ApiResponse<BlueskyAccount[]>>('/accounts')
  return response.data.data
}

export const linkAccount = async (payload: LinkAccountDto) => {
  const response = await http.post<ApiResponse<BlueskyAccount>>('/accounts', payload)
  return response.data.data
}

export const refreshAccount = async (accountId: number) => {
  const response = await http.post<ApiResponse<BlueskyAccount>>(`/accounts/${accountId}/refresh`)
  return response.data.data
}

export const removeAccount = async (accountId: number) => {
  await http.delete(`/accounts/${accountId}`)
}
