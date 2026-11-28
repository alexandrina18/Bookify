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
