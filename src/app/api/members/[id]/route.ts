import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(req: Request, { params }: { params: { id: string } }) {
  const member = await prisma.user.findUnique({
    where: { id: params.id },
    select: {
      id: true, memberId: true, name: true, email: true,
      phone: true, gender: true, address: true, role: true, status: true, createdAt: true,
      borrowings: {
        include: { book: true, fine: true },
        orderBy: { borrowedAt: 'desc' },
      },
      attendances: { orderBy: { checkIn: 'desc' }, take: 10 },
      fines: { orderBy: { createdAt: 'desc' } },
    },
  })
  if (!member) return NextResponse.json({ error: 'Not found' }, { status: 404 })
  return NextResponse.json(member)
}

export async function PUT(req: Request, { params }: { params: { id: string } }) {
  const body = await req.json()
  const { name, email, phone, gender, address, status } = body

  const member = await prisma.user.update({
    where: { id: params.id },
    data: { name, email, phone, gender, address, status },
  })

  return NextResponse.json({ ...member, password: undefined })
}

export async function DELETE(req: Request, { params }: { params: { id: string } }) {
  await prisma.user.delete({ where: { id: params.id } })
  return NextResponse.json({ message: 'Deleted' })
}
