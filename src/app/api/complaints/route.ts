import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
  const complaints = await prisma.complaint.findMany({
    include: {
      user: { select: { id: true, memberId: true, name: true, email: true } },
      book: true,
    },
    orderBy: { createdAt: 'desc' },
  })
  return NextResponse.json(complaints)
}

export async function POST(req: Request) {
  const { userId, bookId, type, description } = await req.json()

  const book = await prisma.book.findFirst({ where: { bookId } })
  if (!book) return NextResponse.json({ error: 'Buku tidak ditemukan' }, { status: 404 })

  const complaint = await prisma.complaint.create({
    data: { userId, bookId: book.id, type, description },
    include: { user: true, book: true },
  })

  if (type === 'lost') {
    await prisma.fine.create({
      data: {
        userId,
        borrowingId: (await prisma.borrowing.findFirst({ where: { userId, bookId: book.id, status: { in: ['active', 'overdue'] } } }))?.id || '',
        daysLate: 0,
        amount: 0,
        bookPrice: 75000,
        status: 'loss',
        type: 'lost',
        notes: 'Buku dinyatakan hilang',
      },
    })
  }

  return NextResponse.json(complaint)
}
