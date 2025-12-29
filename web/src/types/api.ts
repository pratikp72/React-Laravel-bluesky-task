export type BlueskyAccount = {
  id: number
  label: string | null
  handle: string
  status: string
  service: string
  did: string | null
  last_authenticated_at: string | null
  created_at: string | null
  updated_at: string | null
}

export type ScheduledPost = {
  id: number
  content: string
  status: string
  publish_at: string
  queued_at: string | null
  remote_uri: string | null
  failure_reason: string | null
  account: Pick<BlueskyAccount, 'id' | 'label' | 'handle'> | null
}

export type ApiResponse<T> = {
  data: T
  message?: string
  context?: unknown
}
