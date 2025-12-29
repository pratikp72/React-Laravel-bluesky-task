import { RefreshCw, Trash2 } from 'lucide-react'
import type { BlueskyAccount } from '../types/api'
import { formatDistanceToNow } from 'date-fns'
import clsx from 'clsx'

type Props = {
  account: BlueskyAccount
  onRefresh: (id: number) => Promise<void>
  onRemove: (id: number) => Promise<void>
  isBusy: boolean
}

export const AccountCard = ({ account, onRefresh, onRemove, isBusy }: Props) => {
  const subtitle = account.last_authenticated_at
    ? `Refreshed ${formatDistanceToNow(new Date(account.last_authenticated_at), { addSuffix: true })}`
    : 'Awaiting first authentication'

  const badgeColor = {
    connected: 'bg-aurora/20 text-aurora',
    pending: 'bg-yellow-500/20 text-yellow-300',
    error: 'bg-blush/20 text-blush',
  }[account.status] ?? 'bg-white/10 text-white/80'

  return (
    <div className="glass-panel rounded-2xl p-4">
      <div className="flex items-start justify-between">
        <div>
          <p className="font-display text-lg text-white">{account.label ?? account.handle}</p>
          <p className="text-sm text-white/60">{subtitle}</p>
        </div>
        <span className={clsx('rounded-full px-3 py-1 text-xs font-semibold', badgeColor)}>{account.status}</span>
      </div>
      <div className="mt-4 flex gap-3">
        <button
          type="button"
          disabled={isBusy}
          className="flex flex-1 items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-white/90 transition hover:border-aurora"
          onClick={() => onRefresh(account.id)}
        >
          <RefreshCw size={16} /> Refresh tokens
        </button>
        <button
          type="button"
          disabled={isBusy}
          className="flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-white/90 transition hover:border-blush"
          onClick={() => onRemove(account.id)}
        >
          <Trash2 size={16} />
        </button>
      </div>
    </div>
  )
}
