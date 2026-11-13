'use client'
import { useState } from 'react'
import { signIn } from 'next-auth/react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { BookOpen, Eye, EyeOff, Shield } from 'lucide-react'

export default function AdminLoginPage() {
  const router = useRouter()
  const [form, setForm] = useState({ memberId: '', password: '' })
  const [showPass, setShowPass] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setError('')

    const res = await signIn('credentials', {
      memberId: form.memberId,
      password: form.password,
      redirect: false,
    })

    if (res?.ok) {
      router.push('/admin/dashboard')
    } else {
      setError('ID Admin atau password salah')
      setLoading(false)
    }
  }

  return (
    <div className="hero-gradient min-h-screen flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <Link href="/" className="inline-flex items-center gap-3 mb-6">
            <div className="w-12 h-12 rounded-2xl gradient-bg flex items-center justify-center">
              <BookOpen size={24} color="white" />
            </div>
            <span className="text-2xl font-black gradient-text">Bookify</span>
          </Link>
          <div className="inline-flex items-center gap-2 glass px-4 py-2 rounded-full mb-4 text-sm text-amber-400">
            <Shield size={14} />
            Portal Administrator
          </div>
          <h1 className="text-3xl font-bold mb-2">Admin Login</h1>
          <p className="text-slate-400">Akses panel administrasi perpustakaan</p>
        </div>

        <div className="card border-amber-500/20">
          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label className="form-label">ID Admin</label>
              <input id="admin-id" type="text" placeholder="Contoh: ADMIN001"
                value={form.memberId} onChange={e => setForm(f => ({ ...f, memberId: e.target.value }))}
                className="input" required />
            </div>
            <div>
              <label className="form-label">Password</label>
              <div className="relative">
                <input id="admin-password" type={showPass ? 'text' : 'password'} placeholder="Masukkan password admin"
                  value={form.password} onChange={e => setForm(f => ({ ...f, password: e.target.value }))}
                  className="input pr-12" required />
                <button type="button" onClick={() => setShowPass(!showPass)}
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                  {showPass ? <EyeOff size={18} /> : <Eye size={18} />}
                </button>
              </div>
            </div>

            {error && <div className="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">{error}</div>}

            <button id="admin-login-submit" type="submit" disabled={loading}
              className="w-full py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all"
              style={{ background: 'linear-gradient(135deg, #f59e0b, #d97706)', color: 'white' }}>
              {loading ? <span className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" /> : <><Shield size={18} /> Masuk sebagai Admin</>}
            </button>
          </form>
          <div className="mt-6 pt-6 border-t border-slate-800 text-center text-sm text-slate-400">
            <p>Demo: <code className="text-amber-400">ADMIN001</code> / <code className="text-amber-400">admin123</code></p>
          </div>
        </div>

        <div className="mt-4 text-center">
          <Link href="/login" className="text-sm text-slate-500 hover:text-indigo-400 transition-colors">← Login sebagai Anggota</Link>
        </div>
      </div>
    </div>
  )
}
