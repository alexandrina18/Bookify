'use client'
import { useEffect, useState } from 'react'
import { BookOpen, Users, Building2, AlertTriangle, TrendingUp, UserCheck, Clock, DollarSign, ArrowRight, RefreshCw } from 'lucide-react'
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts'
import Link from 'next/link'
import { format } from 'date-fns'
import { id } from 'date-fns/locale'

interface DashboardData {
  stats: {
    totalBooks: number
    totalMembers: number
    totalPublishers: number
    activeBorrowings: number
    overdueBorrowings: number
    todayAttendance: number
    activeVisitors: number
    pendingExtensions: number
    unpaidFinesTotal: number
  }
  borrowingChart: { date: string; peminjaman: number; pengembalian: number }[]
  recentBorrowings: any[]
}

export default function AdminDashboard() {
  const [data, setData] = useState<DashboardData | null>(null)
  const [loading, setLoading] = useState(true)

  async function load() {
    setLoading(true)
    const res = await fetch('/api/dashboard')
    const json = await res.json()
    setData(json)
    setLoading(false)
  }

  useEffect(() => { load() }, [])

  if (loading) return (
    <div className="flex items-center justify-center h-64">
      <div className="w-10 h-10 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin" />
    </div>
  )

  if (!data) return null
  const { stats, borrowingChart, recentBorrowings } = data

  const statCards = [
    { label: 'Total Buku', value: stats.totalBooks, icon: BookOpen, color: '#6366f1', bg: 'rgba(99,102,241,0.15)', href: '/admin/books' },
    { label: 'Total Anggota', value: stats.totalMembers, icon: Users, color: '#a855f7', bg: 'rgba(168,85,247,0.15)', href: '/admin/members' },
    { label: 'Total Penerbit', value: stats.totalPublishers, icon: Building2, color: '#10b981', bg: 'rgba(16,185,129,0.15)', href: '/admin/publishers' },
    { label: 'Pengunjung Aktif', value: stats.activeVisitors, icon: UserCheck, color: '#f59e0b', bg: 'rgba(245,158,11,0.15)', href: '/admin/attendance' },
    { label: 'Dipinjam (Aktif)', value: stats.activeBorrowings, icon: BookOpen, color: '#3b82f6', bg: 'rgba(59,130,246,0.15)', href: '/admin/borrowing' },
    { label: 'Terlambat', value: stats.overdueBorrowings, icon: AlertTriangle, color: '#ef4444', bg: 'rgba(239,68,68,0.15)', href: '/admin/fines' },
    { label: 'Perpanjangan Pending', value: stats.pendingExtensions, icon: Clock, color: '#f97316', bg: 'rgba(249,115,22,0.15)', href: '/admin/extensions' },
    { label: 'Total Denda (Rp)', value: stats.unpaidFinesTotal.toLocaleString('id-ID'), icon: DollarSign, color: '#ec4899', bg: 'rgba(236,72,153,0.15)', href: '/admin/fines' },
  ]

  return (
    <div className="fade-in">
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="page-title">Dashboard</h1>
          <p className="page-subtitle">Ringkasan aktivitas perpustakaan hari ini</p>
        </div>
        <button onClick={load} className="btn-secondary text-sm py-2 px-4">
          <RefreshCw size={16} /> Refresh
        </button>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        {statCards.map((card, i) => (
          <Link key={i} href={card.href} className="stat-card group cursor-pointer no-underline">
            <div className="stat-icon" style={{ background: card.bg }}>
              <card.icon size={24} style={{ color: card.color }} />
            </div>
            <div className="flex-1 min-w-0">
              <div className="text-2xl font-black" style={{ color: card.color }}>{card.value}</div>
              <div className="text-xs text-slate-500 leading-tight">{card.label}</div>
            </div>
            <ArrowRight size={16} className="text-slate-700 group-hover:text-slate-400 transition-colors" />
          </Link>
        ))}
      </div>

      {/* Chart */}
      <div className="card mb-8">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h2 className="font-bold text-lg">Grafik Peminjaman & Pengembalian</h2>
            <p className="text-sm text-slate-500">7 hari terakhir</p>
          </div>
          <TrendingUp size={20} className="text-indigo-400" />
        </div>
        <ResponsiveContainer width="100%" height={260}>
          <AreaChart data={borrowingChart}>
            <defs>
              <linearGradient id="colorPinjam" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#6366f1" stopOpacity={0.3}/>
                <stop offset="95%" stopColor="#6366f1" stopOpacity={0}/>
              </linearGradient>
              <linearGradient id="colorKembali" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#10b981" stopOpacity={0.3}/>
                <stop offset="95%" stopColor="#10b981" stopOpacity={0}/>
              </linearGradient>
            </defs>
            <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" />
            <XAxis dataKey="date" tick={{ fill: '#94a3b8', fontSize: 12 }} axisLine={false} tickLine={false} />
            <YAxis tick={{ fill: '#94a3b8', fontSize: 12 }} axisLine={false} tickLine={false} />
            <Tooltip contentStyle={{ background: '#1a1a2e', border: '1px solid #2d2d4e', borderRadius: '12px', color: '#f1f5f9' }} />
            <Legend />
            <Area type="monotone" dataKey="peminjaman" stroke="#6366f1" strokeWidth={2} fill="url(#colorPinjam)" name="Peminjaman" />
            <Area type="monotone" dataKey="pengembalian" stroke="#10b981" strokeWidth={2} fill="url(#colorKembali)" name="Pengembalian" />
          </AreaChart>
        </ResponsiveContainer>
      </div>

      {/* Recent Borrowings */}
      <div className="card">
        <div className="flex items-center justify-between mb-6">
          <h2 className="font-bold text-lg">Transaksi Terbaru</h2>
          <Link href="/admin/borrowing" className="text-indigo-400 text-sm flex items-center gap-1 hover:gap-2 transition-all">
            Lihat semua <ArrowRight size={14} />
          </Link>
        </div>
        <div className="table-container">
          <table>
            <thead>
              <tr>
                <th>Anggota</th>
                <th>Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              {recentBorrowings.map((b: any) => (
                <tr key={b.id}>
                  <td>
                    <div className="font-medium">{b.user.name}</div>
                    <div className="text-xs text-slate-500">{b.user.memberId}</div>
                  </td>
                  <td>
                    <div className="font-medium text-sm">{b.book.title}</div>
                    <div className="text-xs text-slate-500">{b.book.bookId}</div>
                  </td>
                  <td className="text-sm">{format(new Date(b.borrowedAt), 'dd MMM yyyy', { locale: id })}</td>
                  <td className="text-sm">{format(new Date(b.dueDate), 'dd MMM yyyy', { locale: id })}</td>
                  <td><span className={`badge badge-${b.status}`}>{b.status}</span></td>
                </tr>
              ))}
              {recentBorrowings.length === 0 && (
                <tr><td colSpan={5} className="text-center text-slate-500 py-8">Belum ada transaksi</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
