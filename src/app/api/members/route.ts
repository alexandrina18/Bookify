import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import bcrypt from 'bcryptjs'

export async function GET(req: Request) {
  const { searchParams } = new URL(req.url)
  const search = searchParams.get('search') || ''
  const role = searchParams.get('role') || 'member'

  const members = await prisma.user.findMany({
    where: {
      role,
      OR: search ? [
        { name: { contains: search } },
        { memberId: { contains: search } },
        { email: { contains: search } },
      ] : undefined,
    },
    select: {
      id: true, memberId: true, name: true, email: true,
      phone: true, gender: true, address: true, role: true,
      status: true, createdAt: true,
      _count: { select: { borrowings: true } }
    },
    orderBy: { memberId: 'asc' },
  })

  return NextResponse.json(members)
}

export async function POST(req: Request) {
  const body = await req.json()
  const { memberId, name, email, phone, gender, address, password } = body

  const hashedPassword = await bcrypt.hash(password || 'member123', 10)

  const member = await prisma.user.create({
    data: {
      memberId,
      name,
      email,
      phone,
      gender,
      address,
      password: hashedPassword,
      role: 'member',
      status: 'active',
    },
  })

  return NextResponse.json({ ...member, password: undefined })
}
