import { format, formatDistanceToNow } from 'date-fns'
import type { ScheduledPost } from '../types/api'
import clsx from 'clsx'

type Props = {
  schedules: ScheduledPost[]
  onSendNow: (id: number) => Promise<void>
  onCancel: (id: number) => Promise<void>
  isProcessing: boolean
}

export const ScheduleTable = ({ schedules, onSendNow, onCancel, isProcessing }: Props) => {
  if (!schedules.length) {
    return <p className="text-sm text-white/60">No posts scheduled yet. Your queue will render here.</p>
  }

  return (
    <div className="space-y-3">
      {schedules.map((schedule) => {
        const publishAt = new Date(schedule.publish_at)
        const isFuture = publishAt.getTime() > Date.now()
        const statusColor = {
          scheduled: 'text-aurora border-aurora/30',
          queued: 'text-yellow-300 border-yellow-300/30',
          sent: 'text-sand border-sand/30',
          failed: 'text-blush border-blush/40',
          cancelled: 'text-white/60 border-white/30',
        }[schedule.status] ?? 'text-white/80 border-white/20'

        return (
          <div key={schedule.id} className="glass-panel rounded-2xl border border-white/5 p-4">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <p className="font-display text-lg leading-tight text-white/90">
                  {schedule.account?.label ?? schedule.account?.handle ?? 'Unlinked account'}
                </p>
                <p className="text-sm text-white/60">
                  {format(publishAt, 'MMM d, HH:mm')} ·{' '}
                  {isFuture ? formatDistanceToNow(publishAt, { addSuffix: true }) : 'Ready to send'}
                </p>
              </div>
              <span className={clsx('rounded-full border px-3 py-1 text-xs uppercase tracking-widest', statusColor)}>
                {schedule.status}
              </span>
            </div>
            <p className="mt-4 whitespace-pre-line text-sm text-white/80">{schedule.content}</p>
            <div className="mt-4 flex gap-3">
              <button
                type="button"
                disabled={isProcessing || schedule.status !== 'queued' && schedule.status !== 'scheduled'}
                className="flex flex-1 items-center justify-center rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white/90 transition hover:border-aurora disabled:opacity-30"
                onClick={() => onSendNow(schedule.id)}
              >
                Send now
              </button>
              <button
                type="button"
                disabled={isProcessing || schedule.status === 'cancelled'}
                className="flex flex-1 items-center justify-center rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white/90 transition hover:border-blush disabled:opacity-30"
                onClick={() => onCancel(schedule.id)}
              >
                Cancel
              </button>
            </div>
            {schedule.failure_reason && (
              <p className="mt-3 text-xs text-blush">{schedule.failure_reason}</p>
            )}
          </div>
        )
      })}
    </div>
  )
}
