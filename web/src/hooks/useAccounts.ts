import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { linkAccount, listAccounts, refreshAccount, removeAccount } from '../api/accounts'
import type { LinkAccountDto } from '../api/accounts'

const queryKey = ['accounts']

export const useAccounts = () => {
  const queryClient = useQueryClient()

  const listQuery = useQuery({
    queryKey,
    queryFn: listAccounts,
  })

  const linkMutation = useMutation({
    mutationFn: (payload: LinkAccountDto) => linkAccount(payload),
    onSuccess: () => queryClient.invalidateQueries({ queryKey }),
  })

  const refreshMutation = useMutation({
    mutationFn: (accountId: number) => refreshAccount(accountId),
    onSuccess: () => queryClient.invalidateQueries({ queryKey }),
  })

  const deleteMutation = useMutation({
    mutationFn: (accountId: number) => removeAccount(accountId),
    onSuccess: () => queryClient.invalidateQueries({ queryKey }),
  })

  return {
    accounts: listQuery.data ?? [],
    isLoading: listQuery.isLoading,
    linkAccount: linkMutation.mutateAsync,
    refreshAccount: refreshMutation.mutateAsync,
    removeAccount: deleteMutation.mutateAsync,
    isProcessing:
      linkMutation.isPending || refreshMutation.isPending || deleteMutation.isPending,
  }
}
