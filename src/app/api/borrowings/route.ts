import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { addDays } from 'date-fns'

export async function GET(req: Request) {
  const { searchParams } = new URL(req.url)
  const userId = searchParams.get('userId')
  const status = searchParams.get('status')

  const borrowings = await prisma.borrowing.findMany({
    where: {
      ...(userId ? { userId } : {}),
      ...(status ? { status } : {}),
    },
    include: {
      user: { select: { id: true, memberId: true, name: true, email: true } },
      book: { include: { publisher: true } },
      fine: true,
      extensions: { orderBy: { requestedAt: 'desc' } },
    },
    orderBy: { borrowedAt: 'desc' },
  })

  // Auto-mark overdue
  const now = new Date()
  for (const b of borrowings) {
    if (b.status === 'active' && b.dueDate < now) {
      await prisma.borrowing.update({
        where: { id: b.id },
        data: { status: 'overdue' },
      })
    }
  }

  return NextResponse.json(borrowings)
}

export async function POST(req: Request) {
  const { memberId, bookId: bookBarcode } = await req.json()

  // Find user by memberId
  const user = await prisma.user.findUnique({ where: { memberId } })
  if (!user) return NextResponse.json({ error: 'Anggota tidak ditemukan' }, { status: 404 })
  if (user.status === 'blocked') return NextResponse.json({ error: 'Akun anggota diblokir' }, { status: 403 })

  // Check unpaid fines
  const unpaidFine = await prisma.fine.findFirst({
    where: { userId: user.id, status: 'unpaid' }
  })
  if (unpaidFine) return NextResponse.json({ error: 'Anggota memiliki denda belum lunas' }, { status: 403 })

  // Find book by bookId (barcode)
  const book = await prisma.book.findUnique({ where: { bookId: bookBarcode } })
  if (!book) return NextResponse.json({ error: 'Buku tidak ditemukan' }, { status: 404 })
  if (book.available <= 0) return NextResponse.json({ error: 'Buku tidak tersedia' }, { status: 400 })

  // Check active borrowings limit (max 3)
  const activeBorrowings = await prisma.borrowing.count({
    where: { userId: user.id, status: { in: ['active', 'overdue'] } }
  })
  if (activeBorrowings >= 3) return NextResponse.json({ error: 'Batas peminjaman maksimal 3 buku' }, { status: 400 })

  const dueDate = addDays(new Date(), 7)

  const borrowing = await prisma.borrowing.create({
    data: {
      userId: user.id,
      bookId: book.id,
      dueDate,
      status: 'active',
    },
    include: { user: true, book: true },
  })

  await prisma.book.update({
    where: { id: book.id },
    data: { available: { decrement: 1 } },
  })

  return NextResponse.json(borrowing)
}
