import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { cancelSchedule, createSchedule, listSchedules, publishNow, sendNow } from '../api/schedules'
import type { SchedulePostDto, PublishNowDto } from '../api/schedules'

const queryKey = ['schedules']

export const useSchedules = () => {
  const queryClient = useQueryClient()

  const listQuery = useQuery({
    queryKey,
    queryFn: listSchedules,
  })

  const createMutation = useMutation({
    mutationFn: (payload: SchedulePostDto) => createSchedule(payload),
    onSuccess: () => queryClient.invalidateQueries({ queryKey }),
  })

  const publishNowMutation = useMutation({
    mutationFn: (payload: PublishNowDto) => publishNow(payload),
    onSuccess: () => queryClient.invalidateQueries({ queryKey }),
  })

  const sendMutation = useMutation({
    mutationFn: (id: number) => sendNow(id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey }),
  })

  const cancelMutation = useMutation({
    mutationFn: (id: number) => cancelSchedule(id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey }),
  })

  return {
    schedules: listQuery.data ?? [],
    isLoading: listQuery.isLoading,
    createSchedule: createMutation.mutateAsync,
    publishNow: publishNowMutation.mutateAsync,
    sendNow: sendMutation.mutateAsync,
    cancelSchedule: cancelMutation.mutateAsync,
    isProcessing:
      createMutation.isPending ||
      publishNowMutation.isPending ||
      sendMutation.isPending ||
      cancelMutation.isPending,
  }
}
