import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { differenceInDays } from 'date-fns'

// POST /api/returns — Process book return
export async function POST(req: Request) {
  const { memberId, bookId: bookBarcode } = await req.json()

  const user = await prisma.user.findUnique({ where: { memberId } })
  if (!user) return NextResponse.json({ error: 'Anggota tidak ditemukan' }, { status: 404 })

  const book = await prisma.book.findUnique({ where: { bookId: bookBarcode } })
  if (!book) return NextResponse.json({ error: 'Buku tidak ditemukan' }, { status: 404 })

  const borrowing = await prisma.borrowing.findFirst({
    where: { userId: user.id, bookId: book.id, status: { in: ['active', 'overdue'] } },
    orderBy: { borrowedAt: 'desc' },
  })
  if (!borrowing) return NextResponse.json({ error: 'Tidak ada peminjaman aktif untuk buku ini' }, { status: 404 })

  const now = new Date()
  const daysLate = Math.max(0, differenceInDays(now, borrowing.dueDate))
  const fineAmount = daysLate * 1000

  // Update borrowing
  await prisma.borrowing.update({
    where: { id: borrowing.id },
    data: { returnedAt: now, status: 'returned' },
  })

  // Increase book availability
  await prisma.book.update({
    where: { id: book.id },
    data: { available: { increment: 1 } },
  })

  // Create fine if late
  let fine = null
  if (daysLate > 0) {
    fine = await prisma.fine.create({
      data: {
        userId: user.id,
        borrowingId: borrowing.id,
        daysLate,
        amount: fineAmount,
        status: 'unpaid',
        type: 'late',
      },
    })
  }

  return NextResponse.json({
    message: 'Pengembalian berhasil',
    daysLate,
    fineAmount,
    fine,
    borrowing,
  })
}
