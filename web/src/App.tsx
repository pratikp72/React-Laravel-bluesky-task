import { useState } from 'react'
import './App.css'
import { AccountForm } from './components/AccountForm'
import { AccountCard } from './components/AccountCard'
import { ScheduleForm } from './components/ScheduleForm'
import { ScheduleTable } from './components/ScheduleTable'
import { useAccounts } from './hooks/useAccounts'
import { useSchedules } from './hooks/useSchedules'

function App() {
  const accounts = useAccounts()
  const schedules = useSchedules()
  const [banner, setBanner] = useState<{ message: string; tone: 'success' | 'error' } | null>(null)
  const [accountFormResetSignal, setAccountFormResetSignal] = useState(0)
  const [scheduleFormResetSignal, setScheduleFormResetSignal] = useState(0)

  const guard = async (callback: () => Promise<unknown>) => {
    try {
      await callback()
      setBanner({ message: 'Synced successfully ✨', tone: 'success' })
      setTimeout(() => setBanner(null), 4500)
      return true
    } catch (error) {
      setBanner({ message: (error as Error).message ?? 'Something went wrong', tone: 'error' })
      return false
    }
  }

  const handleLinkAccount = async (
    payload: Parameters<typeof accounts.linkAccount>[0],
  ) => {
    const succeeded = await guard(() => accounts.linkAccount(payload))
    if (succeeded) {
      setAccountFormResetSignal((signal) => signal + 1)
    }
  }

  const handleSchedulePost = async (
    payload: Parameters<typeof schedules.createSchedule>[0],
  ) => {
    const succeeded = await guard(() => schedules.createSchedule(payload))
    if (succeeded) {
      setScheduleFormResetSignal((signal) => signal + 1)
    }
  }

  const handlePublishNow = async (
    payload: Parameters<typeof schedules.publishNow>[0],
  ) => {
    const succeeded = await guard(() => schedules.publishNow(payload))
    if (succeeded) {
      setScheduleFormResetSignal((signal) => signal + 1)
    }
  }

  return (
    <div className="min-h-screen px-4 py-10 sm:px-8">
      <div className="mx-auto max-w-6xl space-y-10">
        <header className="grid-accent relative rounded-3xl border border-white/5 bg-gradient-to-br from-midnight via-slate to-slate/80 p-8 text-white shadow-halo">
          <p className="font-display text-sm tracking-[0.4em] text-white/60">BLUESKY OPS</p>
          <h1 className="mt-3 font-display text-4xl text-white">
            Schedule Bluesky drops with production-grade reliability.
          </h1>
          <p className="mt-3 max-w-2xl text-base text-white/70">
            Link multiple studios, draft content collaboratively, and let the job runner publish
            exactly on time. Everything ships through the Laravel API you can self-host.
          </p>
          {banner && (
            <div
              className={`mt-6 inline-flex items-center gap-3 rounded-2xl px-4 py-2 text-sm font-medium ${banner.tone === 'success' ? 'bg-aurora/15 text-aurora' : 'bg-blush/15 text-blush'}`}
            >
              {banner.message}
            </div>
          )}
        </header>

        <section className="grid gap-6 lg:grid-cols-[1fr,1.2fr]">
          <div className="space-y-6">
            <div className="glass-panel rounded-3xl p-6">
              <p className="text-sm uppercase tracking-[0.3em] text-white/60">Link account</p>
              <h2 className="mt-2 font-display text-2xl text-white">Authenticate Bluesky</h2>
              <AccountForm
                isSubmitting={accounts.isProcessing}
                onSubmit={handleLinkAccount}
                resetSignal={accountFormResetSignal}
              />
            </div>

            <div className="space-y-3">
              {accounts.isLoading && <p className="text-white/60">Loading accounts…</p>}
              {!accounts.isLoading && accounts.accounts.length === 0 && (
                <p className="text-sm text-white/60">No accounts linked yet.</p>
              )}
              {accounts.accounts.map((account) => (
                <AccountCard
                  key={account.id}
                  account={account}
                  isBusy={accounts.isProcessing}
                  onRefresh={(id) => guard(() => accounts.refreshAccount(id))}
                  onRemove={(id) => guard(() => accounts.removeAccount(id))}
                />
              ))}
            </div>
          </div>

          <div className="space-y-6">
            <div className="glass-panel rounded-3xl p-6">
              <p className="text-sm uppercase tracking-[0.3em] text-white/60">Schedule</p>
              <h2 className="mt-2 font-display text-2xl text-white">Compose drop</h2>
              <ScheduleForm
                accounts={accounts.accounts}
                isSubmitting={schedules.isProcessing}
                onSubmit={handleSchedulePost}
                onPublishNow={handlePublishNow}
                resetSignal={scheduleFormResetSignal}
              />
            </div>

            <div className="glass-panel rounded-3xl p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm uppercase tracking-[0.3em] text-white/60">Queue</p>
                  <h2 className="mt-2 font-display text-2xl text-white">Upcoming posts</h2>
                </div>
              </div>
              <div className="mt-4">
                {schedules.isLoading ? (
                  <p className="text-white/60">Loading schedule…</p>
                ) : (
                  <ScheduleTable
                    schedules={schedules.schedules}
                    isProcessing={schedules.isProcessing}
                    onSendNow={(id) => guard(() => schedules.sendNow(id))}
                    onCancel={(id) => guard(() => schedules.cancelSchedule(id))}
                  />
                )}
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  )
}

export default App
