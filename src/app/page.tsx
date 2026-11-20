'use client'
import { useState, useEffect } from 'react'
import Link from 'next/link'
import { BookOpen, Users, MapPin, Phone, Mail, Clock, Search, Star, ChevronRight, Library, Shield } from 'lucide-react'

interface Book {
  id: string
  bookId: string
  title: string
  author: string
  genre: string
  year: number
  available: number
  stock: number
  publisher?: { name: string }
}

export default function LandingPage() {
  const [books, setBooks] = useState<Book[]>([])
  const [search, setSearch] = useState('')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetch('/api/books')
      .then(r => r.json())
      .then(data => { setBooks(data); setLoading(false) })
      .catch(() => setLoading(false))
  }, [])

  const filtered = books.filter(b =>
    b.title.toLowerCase().includes(search.toLowerCase()) ||
    b.author.toLowerCase().includes(search.toLowerCase()) ||
    (b.genre || '').toLowerCase().includes(search.toLowerCase())
  )

  const stats = [
    { label: 'Koleksi Buku', value: books.length + '+', icon: BookOpen, color: '#6366f1' },
    { label: 'Anggota Aktif', value: '250+', icon: Users, color: '#a855f7' },
    { label: 'Tahun Berdiri', value: '2020', icon: Library, color: '#10b981' },
    { label: 'Buku Tersedia', value: books.filter(b => b.available > 0).length + '+', icon: Star, color: '#f59e0b' },
  ]

  return (
    <div className="hero-gradient min-h-screen">
      {/* Navbar */}
      <nav className="glass-dark sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl gradient-bg flex items-center justify-center">
              <BookOpen size={20} color="white" />
            </div>
            <span className="text-xl font-bold gradient-text">Bookify</span>
          </div>
          <div className="flex items-center gap-3">
            <Link href="/login" className="btn-secondary text-sm py-2 px-4">
              Login Anggota
            </Link>
            <Link href="/admin/login" className="btn-primary text-sm py-2 px-4">
              <Shield size={16} />
              Admin
            </Link>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="max-w-7xl mx-auto px-6 py-20 text-center relative overflow-hidden">
        {/* Decorative blobs */}
        <div className="absolute top-0 left-1/4 w-96 h-96 rounded-full blur-3xl opacity-10" style={{ background: 'radial-gradient(circle, #6366f1, transparent)' }} />
        <div className="absolute bottom-0 right-1/4 w-96 h-96 rounded-full blur-3xl opacity-10" style={{ background: 'radial-gradient(circle, #a855f7, transparent)' }} />

        <div className="relative z-10">
          <div className="inline-flex items-center gap-2 glass px-4 py-2 rounded-full mb-6 text-sm text-indigo-400">
            <span className="w-2 h-2 bg-green-400 rounded-full animate-pulse" />
            Perpustakaan Digital Modern
          </div>
          <h1 className="text-5xl md:text-7xl font-black mb-6 leading-tight">
            Selamat Datang di <br />
            <span className="gradient-text">Bookify</span>
          </h1>
          <p className="text-lg text-slate-400 max-w-2xl mx-auto mb-10">
            Sistem perpustakaan digital terpadu. Jelajahi ribuan koleksi buku, pinjam dengan mudah, dan kelola semua aktivitas perpustakaan dalam satu platform.
          </p>
          <div className="flex flex-wrap gap-4 justify-center">
            <Link href="/login" className="btn-primary text-base px-8 py-3">
              Mulai Meminjam <ChevronRight size={18} />
            </Link>
            <a href="#catalog" className="btn-secondary text-base px-8 py-3">
              Lihat Katalog
            </a>
          </div>
        </div>
      </section>

      {/* Stats */}
      <section className="max-w-7xl mx-auto px-6 pb-16">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {stats.map((stat, i) => (
            <div key={i} className="card text-center group">
              <div className="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center transition-transform group-hover:scale-110" style={{ background: `${stat.color}20` }}>
                <stat.icon size={24} style={{ color: stat.color }} />
              </div>
              <div className="text-3xl font-black mb-1" style={{ color: stat.color }}>{stat.value}</div>
              <div className="text-slate-400 text-sm">{stat.label}</div>
            </div>
          ))}
        </div>
      </section>

      {/* Catalog Section */}
      <section id="catalog" className="max-w-7xl mx-auto px-6 pb-20">
        <div className="text-center mb-10">
          <h2 className="text-3xl font-black mb-3">Katalog <span className="gradient-text">Buku</span></h2>
          <p className="text-slate-400">Temukan buku yang kamu cari dari koleksi kami</p>
        </div>

        {/* Search */}
        <div className="relative max-w-xl mx-auto mb-10">
          <Search size={18} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500" />
          <input
            type="text"
            placeholder="Cari judul, pengarang, atau genre..."
            value={search}
            onChange={e => setSearch(e.target.value)}
            className="input pl-12"
            id="search-books"
          />
        </div>

        {loading ? (
          <div className="text-center py-20 text-slate-400">
            <div className="w-12 h-12 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-4" />
            Memuat katalog...
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            {filtered.map(book => (
              <div key={book.id} className="card group hover:border-indigo-500/50 cursor-default">
                <div className="w-full h-40 rounded-12 mb-4 flex items-center justify-center rounded-xl" style={{ background: 'linear-gradient(135deg, rgba(99,102,241,0.15), rgba(168,85,247,0.15))' }}>
                  <BookOpen size={40} className="text-indigo-400 opacity-60 group-hover:opacity-100 group-hover:scale-110 transition-all" />
                </div>
                <div className="space-y-2">
                  <span className="genre-badge">{book.genre || 'Umum'}</span>
                  <h3 className="font-bold text-sm leading-tight line-clamp-2">{book.title}</h3>
                  <p className="text-slate-400 text-xs">{book.author}</p>
                  {book.publisher && <p className="text-slate-500 text-xs">{book.publisher.name}</p>}
                  <div className="flex items-center justify-between pt-2">
                    <span className={`text-xs font-semibold ${book.available > 0 ? 'text-green-400' : 'text-red-400'}`}>
                      {book.available > 0 ? `${book.available} tersedia` : 'Tidak tersedia'}
                    </span>
                    <span className="text-xs text-slate-500">ID: {book.bookId}</span>
                  </div>
                </div>
              </div>
            ))}
            {filtered.length === 0 && (
              <div className="col-span-full text-center py-16 text-slate-400">
                <BookOpen size={48} className="mx-auto mb-4 opacity-30" />
                <p>Tidak ada buku ditemukan untuk "{search}"</p>
              </div>
            )}
          </div>
        )}
      </section>

      {/* Library Info */}
      <section className="max-w-7xl mx-auto px-6 pb-20">
        <div className="grid md:grid-cols-2 gap-8 items-center">
          <div>
            <h2 className="text-3xl font-black mb-4">Tentang <span className="gradient-text">Perpustakaan Kami</span></h2>
            <p className="text-slate-400 mb-6 leading-relaxed">
              Bookify adalah perpustakaan digital modern yang hadir untuk memberikan pengalaman terbaik dalam membaca dan meminjam buku. 
              Kami berkomitmen untuk menyediakan koleksi buku berkualitas dan layanan yang mudah diakses oleh seluruh anggota.
            </p>
            <div className="space-y-4">
              {[
                { icon: MapPin, label: 'Lokasi', value: 'Jl. Pawiyatan Luhur No.1, Semarang, Jawa Tengah' },
                { icon: Phone, label: 'Telepon', value: '+62 24 8441555' },
                { icon: Mail, label: 'Email', value: 'perpustakaan@bookify.id' },
                { icon: Clock, label: 'Jam Buka', value: 'Senin–Jumat 08.00–17.00 | Sabtu 08.00–13.00' },
              ].map((info, i) => (
                <div key={i} className="flex items-start gap-3">
                  <div className="w-9 h-9 rounded-lg bg-indigo-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <info.icon size={16} className="text-indigo-400" />
                  </div>
                  <div>
                    <div className="text-xs text-slate-500 uppercase tracking-wider">{info.label}</div>
                    <div className="text-sm font-medium">{info.value}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>
          <div className="glass rounded-2xl p-8">
            <h3 className="text-xl font-bold mb-6">Cara Menggunakan Bookify</h3>
            <div className="space-y-5">
              {[
                { step: '01', title: 'Daftar Keanggotaan', desc: 'Daftar langsung di perpustakaan dengan membawa identitas diri.' },
                { step: '02', title: 'Login ke Sistem', desc: 'Gunakan ID anggota dan password yang diberikan untuk login.' },
                { step: '03', title: 'Jelajahi Katalog', desc: 'Cari dan temukan buku yang ingin kamu baca.' },
                { step: '04', title: 'Pinjam via Barcode', desc: 'Scan barcode ID dan barcode buku untuk proses peminjaman.' },
              ].map((step, i) => (
                <div key={i} className="flex gap-4">
                  <div className="text-2xl font-black gradient-text flex-shrink-0 w-10">{step.step}</div>
                  <div>
                    <div className="font-semibold mb-1">{step.title}</div>
                    <div className="text-sm text-slate-400">{step.desc}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="max-w-7xl mx-auto px-6 pb-20">
        <div className="rounded-2xl p-12 text-center relative overflow-hidden gradient-bg">
          <div className="absolute inset-0 opacity-20" style={{ backgroundImage: 'radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 50%, white 1px, transparent 1px)', backgroundSize: '40px 40px' }} />
          <div className="relative z-10">
            <h2 className="text-4xl font-black text-white mb-4">Siap Mulai Membaca?</h2>
            <p className="text-indigo-200 mb-8 text-lg">Login sekarang dan nikmati ribuan koleksi buku kami</p>
            <Link href="/login" className="inline-flex items-center gap-2 bg-white text-indigo-700 font-bold px-8 py-3 rounded-xl hover:scale-105 transition-transform">
              Login Sekarang <ChevronRight size={18} />
            </Link>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-slate-800 py-8">
        <div className="max-w-7xl mx-auto px-6 text-center text-slate-500 text-sm">
          <div className="flex items-center justify-center gap-2 mb-2">
            <BookOpen size={16} className="text-indigo-400" />
            <span className="font-bold text-indigo-400">Bookify</span>
          </div>
          <p>© 2026 Bookify — Sistem Perpustakaan Digital | Universitas Katolik Soegijapranata</p>
          <p className="mt-1">Dibuat oleh Jolanda · Theodora · Alexandrina</p>
        </div>
      </footer>
    </div>
  )
}
