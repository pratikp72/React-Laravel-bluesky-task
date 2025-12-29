import { z } from 'zod'
import { useEffect } from 'react'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import type { LinkAccountDto } from '../api/accounts'

const schema = z.object({
  handle: z.string().min(3).max(64),
  app_password: z.string().min(16, 'App passwords contain 16+ characters.').max(128),
  label: z
    .string()
    .max(64)
    .optional()
    .or(z.literal('').transform(() => undefined)),
})

type FormValues = z.infer<typeof schema>

const buildDefaultValues = (): FormValues => ({
  handle: '',
  app_password: '',
  label: '',
})

type Props = {
  onSubmit: (payload: LinkAccountDto) => Promise<void>
  isSubmitting: boolean
  resetSignal: number
}

export const AccountForm = ({ onSubmit, isSubmitting, resetSignal }: Props) => {
  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: buildDefaultValues(),
  })

  const submit = async (values: FormValues) => {
    await onSubmit({
      handle: values.handle.trim().toLowerCase(),
      app_password: values.app_password.trim(),
      label: values.label ?? undefined,
    })
  }

  useEffect(() => {
    if (!resetSignal) {
      return
    }

    reset(buildDefaultValues())
  }, [resetSignal, reset])

  return (
    <form className="space-y-4" onSubmit={handleSubmit(submit)}>
      <div>
        <label className="text-sm uppercase tracking-widest text-sand/80">Handle</label>
        <input
          className="mt-2 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-aurora focus:outline-none"
          placeholder="studio.bsky.social"
          {...register('handle')}
        />
        {errors.handle && <p className="mt-1 text-xs text-blush">{errors.handle.message}</p>}
      </div>

      <div>
        <label className="text-sm uppercase tracking-widest text-sand/80">App password</label>
        <input
          type="password"
          className="mt-2 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-aurora focus:outline-none"
          placeholder="xxxx-xxxx-xxxx-xxxx"
          {...register('app_password')}
        />
        {errors.app_password && <p className="mt-1 text-xs text-blush">{errors.app_password.message}</p>}
      </div>

      <div>
        <label className="text-sm uppercase tracking-widest text-sand/80">Label (optional)</label>
        <input
          className="mt-2 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-aurora focus:outline-none"
          placeholder="Studio account"
          {...register('label')}
        />
        {errors.label && <p className="mt-1 text-xs text-blush">{errors.label.message}</p>}
      </div>

      <button
        type="submit"
        disabled={isSubmitting}
        className="w-full rounded-xl bg-gradient-to-r from-aurora to-blush px-4 py-2 font-display text-base font-semibold text-slate shadow-halo transition hover:opacity-90 disabled:opacity-50"
      >
        {isSubmitting ? 'Linking…' : 'Link Bluesky Account'}
      </button>
    </form>
  )
}
