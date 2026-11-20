import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { addDays } from 'date-fns'

export async function PUT(req: Request, { params }: { params: { id: string } }) {
  const { status, adminNote } = await req.json()

  const extension = await prisma.extension.findUnique({
    where: { id: params.id },
    include: { borrowing: true },
  })
  if (!extension) return NextResponse.json({ error: 'Not found' }, { status: 404 })

  const updated = await prisma.extension.update({
    where: { id: params.id },
    data: {
      status,
      adminNote,
      reviewedAt: new Date(),
      newDueDate: status === 'approved' ? addDays(extension.borrowing.dueDate, 7) : null,
    },
  })

  if (status === 'approved') {
    await prisma.borrowing.update({
      where: { id: extension.borrowingId },
      data: {
        dueDate: addDays(extension.borrowing.dueDate, 7),
        renewCount: { increment: 1 },
        status: 'active',
      },
    })
  }

  return NextResponse.json(updated)
}
