import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
  const attendance = await prisma.attendance.findMany({
    include: { user: { select: { id: true, memberId: true, name: true } } },
    orderBy: { checkIn: 'desc' },
    take: 100,
  })
  return NextResponse.json(attendance)
}

export async function POST(req: Request) {
  const { memberId } = await req.json()

  const user = await prisma.user.findUnique({ where: { memberId } })
  if (!user) return NextResponse.json({ error: 'Anggota tidak ditemukan' }, { status: 404 })
  if (user.status === 'blocked') return NextResponse.json({ error: 'Akun diblokir' }, { status: 403 })

  // Check active attendance (no checkout yet)
  const active = await prisma.attendance.findFirst({
    where: { userId: user.id, checkOut: null },
    orderBy: { checkIn: 'desc' },
  })

  if (active) {
    // Check out
    const now = new Date()
    const durationMs = now.getTime() - active.checkIn.getTime()
    const durationMin = Math.floor(durationMs / 60000)

    const attendance = await prisma.attendance.update({
      where: { id: active.id },
      data: { checkOut: now, duration: durationMin },
      include: { user: { select: { id: true, memberId: true, name: true } } },
    })

    return NextResponse.json({ type: 'checkout', attendance })
  } else {
    // Check in
    const attendance = await prisma.attendance.create({
      data: { userId: user.id },
      include: { user: { select: { id: true, memberId: true, name: true } } },
    })

    return NextResponse.json({ type: 'checkin', attendance })
  }
}
