'use client'
import { useSession, signOut } from 'next-auth/react'
import { useRouter, usePathname } from 'next/navigation'
import { useEffect, useState } from 'react'
import Link from 'next/link'
import {
  BookOpen, LayoutDashboard, Users, Library, QrCode, RotateCcw,
  FileWarning, Clock, AlertCircle, DollarSign, LogOut, Menu, X,
  ChevronRight, Shield
} from 'lucide-react'

const navItems = [
  { label: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
  { label: 'Peminjaman', href: '/admin/borrowing', icon: BookOpen },
  { label: 'Pengembalian', href: '/admin/returns', icon: RotateCcw },
  { label: 'Absensi', href: '/admin/attendance', icon: QrCode },
  { label: 'Buku', href: '/admin/books', icon: Library },
  { label: 'Penerbit', href: '/admin/publishers', icon: FileWarning },
  { label: 'Anggota', href: '/admin/members', icon: Users },
  { label: 'Denda & Sanksi', href: '/admin/fines', icon: AlertCircle },
  { label: 'Perpanjangan', href: '/admin/extensions', icon: Clock },
  { label: 'Pengaduan', href: '/admin/complaints', icon: FileWarning },
  { label: 'Anggaran', href: '/admin/budget', icon: DollarSign },
]

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const { data: session, status } = useSession()
  const router = useRouter()
  const pathname = usePathname()
  const [sidebarOpen, setSidebarOpen] = useState(false)

  useEffect(() => {
    if (status === 'unauthenticated') router.push('/admin/login')
    if (status === 'authenticated' && session?.user?.role !== 'admin') router.push('/')
  }, [status, session, router])

  if (status === 'loading') {
    return <div className="min-h-screen hero-gradient flex items-center justify-center">
      <div className="w-12 h-12 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin" />
    </div>
  }

  if (!session || session.user.role !== 'admin') return null

  return (
    <div className="min-h-screen" style={{ background: '#0f0f1a' }}>
      {/* Sidebar */}
      <aside className={`sidebar ${sidebarOpen ? '!translate-x-0' : ''}`} style={{ overflowY: 'auto' }}>
        {/* Logo */}
        <div className="p-5 border-b border-slate-800">
          <Link href="/" className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl gradient-bg flex items-center justify-center">
              <BookOpen size={18} color="white" />
            </div>
            <div>
              <span className="text-lg font-black gradient-text">Bookify</span>
              <div className="text-xs text-amber-400 flex items-center gap-1 -mt-0.5">
                <Shield size={10} /> Admin Panel
              </div>
            </div>
          </Link>
        </div>

        {/* Nav */}
        <nav className="flex-1 p-4 space-y-1">
          {navItems.map(item => (
            <Link key={item.href} href={item.href}
              className={`sidebar-link ${pathname === item.href ? 'active' : ''}`}
              onClick={() => setSidebarOpen(false)}>
              <item.icon size={18} />
              {item.label}
            </Link>
          ))}
        </nav>

        {/* User info */}
        <div className="p-4 border-t border-slate-800">
          <div className="flex items-center gap-3 mb-3 px-2">
            <div className="w-9 h-9 rounded-full gradient-bg flex items-center justify-center text-white font-bold text-sm">
              {session.user.name?.[0]}
            </div>
            <div className="flex-1 min-w-0">
              <div className="font-semibold text-sm truncate">{session.user.name}</div>
              <div className="text-xs text-slate-500">{session.user.memberId}</div>
            </div>
          </div>
          <button onClick={() => signOut({ callbackUrl: '/admin/login' })}
            className="sidebar-link w-full text-red-400 hover:bg-red-500/10 hover:text-red-400">
            <LogOut size={16} /> Keluar
          </button>
        </div>
      </aside>

      {/* Mobile header */}
      <div className="lg:hidden fixed top-0 left-0 right-0 z-30 glass-dark px-4 py-3 flex items-center justify-between">
        <span className="font-bold gradient-text">Bookify Admin</span>
        <button onClick={() => setSidebarOpen(!sidebarOpen)} className="p-2">
          {sidebarOpen ? <X size={20} /> : <Menu size={20} />}
        </button>
      </div>

      {/* Mobile overlay */}
      {sidebarOpen && (
        <div className="fixed inset-0 bg-black/50 z-30 lg:hidden" onClick={() => setSidebarOpen(false)} />
      )}

      {/* Main content */}
      <main className="main-content lg:ml-[260px]">
        <div className="pt-14 lg:pt-0">
          {children}
        </div>
      </main>
    </div>
  )
}
