import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
  const fines = await prisma.fine.findMany({
    include: {
      user: { select: { id: true, memberId: true, name: true, email: true } },
      borrowing: { include: { book: true } },
    },
    orderBy: { createdAt: 'desc' },
  })
  return NextResponse.json(fines)
}

export async function PUT(req: Request) {
  const { id, status, notes } = await req.json()
  const fine = await prisma.fine.update({
    where: { id },
    data: { status, notes },
  })
  return NextResponse.json(fine)
}
