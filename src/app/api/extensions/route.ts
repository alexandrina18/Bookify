import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { addDays } from 'date-fns'

export async function GET() {
  const extensions = await prisma.extension.findMany({
    include: {
      user: { select: { id: true, memberId: true, name: true, email: true } },
      borrowing: { include: { book: true } },
    },
    orderBy: { requestedAt: 'desc' },
  })
  return NextResponse.json(extensions)
}

export async function POST(req: Request) {
  const { borrowingId, userId } = await req.json()

  const borrowing = await prisma.borrowing.findUnique({ where: { id: borrowingId } })
  if (!borrowing) return NextResponse.json({ error: 'Peminjaman tidak ditemukan' }, { status: 404 })
  if (borrowing.renewCount >= 6) return NextResponse.json({ error: 'Batas perpanjangan maksimal 6x' }, { status: 400 })

  // Check H-2 rule
  const now = new Date()
  const daysUntilDue = Math.ceil((borrowing.dueDate.getTime() - now.getTime()) / 86400000)
  if (daysUntilDue > 2) return NextResponse.json({ error: 'Perpanjangan hanya bisa dilakukan H-2 sebelum jatuh tempo' }, { status: 400 })
  if (daysUntilDue < 0) return NextResponse.json({ error: 'Buku sudah melewati jatuh tempo, tidak dapat diperpanjang' }, { status: 400 })

  const extension = await prisma.extension.create({
    data: {
      userId,
      borrowingId,
      status: 'pending',
    },
    include: { user: true, borrowing: { include: { book: true } } },
  })

  return NextResponse.json(extension)
}
