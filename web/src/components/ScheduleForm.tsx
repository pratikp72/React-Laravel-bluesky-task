import { z } from 'zod'
import { useEffect } from 'react'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import type { BlueskyAccount } from '../types/api'
import type { PublishNowDto, SchedulePostDto } from '../api/schedules'

const schema = z.object({
  account_id: z.number().int().positive(),
  content: z.string().min(10).max(300),
  publish_at: z.string().min(1),
})

type FormValues = z.infer<typeof schema>

type Props = {
  accounts: BlueskyAccount[]
  onSubmit: (payload: SchedulePostDto) => Promise<void>
  onPublishNow: (payload: PublishNowDto) => Promise<void>
  isSubmitting: boolean
  resetSignal: number
}

const buildDefaultValues = (accounts: BlueskyAccount[]): FormValues => ({
  account_id: accounts[0]?.id ?? 0,
  content: '',
  publish_at: new Date(Date.now() + 60 * 60 * 1000).toISOString().slice(0, 16),
})

export const ScheduleForm = ({
  accounts,
  onSubmit,
  onPublishNow,
  isSubmitting,
  resetSignal,
}: Props) => {
  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
    setValue,
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: buildDefaultValues(accounts),
  })

  useEffect(() => {
    if (accounts[0]) {
      setValue('account_id', accounts[0].id)
    }
  }, [accounts, setValue])

  const submit = async (values: FormValues) => {
    await onSubmit({
      account_id: values.account_id,
      content: values.content.trim(),
      publish_at: new Date(values.publish_at).toISOString(),
    })
  }

  const submitNow = async (values: FormValues) => {
    await onPublishNow({
      account_id: values.account_id,
      content: values.content.trim(),
    })
  }

  useEffect(() => {
    if (!resetSignal) {
      return
    }

    reset(buildDefaultValues(accounts))
  }, [resetSignal, reset, accounts])

  return (
    <form className="space-y-4" onSubmit={handleSubmit(submit)}>
      <div>
        <label className="text-sm uppercase tracking-widest text-sand/80">Account</label>
        <select
          className="mt-2 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white"
          {...register('account_id', { valueAsNumber: true })}
          disabled={!accounts.length}
        >
          {accounts.map((account) => (
            <option key={account.id} value={account.id} className="bg-slate">
              {account.label ?? account.handle}
            </option>
          ))}
        </select>
        {errors.account_id && <p className="mt-1 text-xs text-blush">Select an account</p>}
      </div>

      <div>
        <label className="text-sm uppercase tracking-widest text-sand/80">Publish at</label>
        <input
          type="datetime-local"
          className="mt-2 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white"
          {...register('publish_at')}
        />
        {errors.publish_at && <p className="mt-1 text-xs text-blush">Enter a future timestamp</p>}
      </div>

      <div>
        <label className="text-sm uppercase tracking-widest text-sand/80">Post content</label>
        <textarea
          rows={5}
          className="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white"
          placeholder="Announce your next drop…"
          {...register('content')}
        />
        <p className="mt-1 text-right text-xs text-white/50">Max 300 characters</p>
        {errors.content && <p className="text-xs text-blush">{errors.content.message}</p>}
      </div>

      <div className="flex flex-col gap-3 sm:flex-row">
        <button
          type="submit"
          disabled={isSubmitting || !accounts.length}
          className="flex-1 rounded-xl bg-gradient-to-r from-blush to-aurora px-4 py-2 font-display text-base font-semibold text-slate shadow-halo transition hover:opacity-90 disabled:opacity-40"
        >
          {isSubmitting ? 'Scheduling…' : 'Schedule Post'}
        </button>
        <button
          type="button"
          disabled={isSubmitting || !accounts.length}
          onClick={handleSubmit(submitNow)}
          className="flex-1 rounded-xl border border-white/10 bg-white/10 px-4 py-2 font-display text-base font-semibold text-white transition hover:border-aurora hover:text-aurora disabled:opacity-40"
        >
          {isSubmitting ? 'Posting…' : 'Post Now'}
        </button>
      </div>
    </form>
  )
}
