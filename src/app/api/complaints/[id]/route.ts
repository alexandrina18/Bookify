import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function PUT(req: Request, { params }: { params: { id: string } }) {
  const { status, adminNote } = await req.json()
  const complaint = await prisma.complaint.update({
    where: { id: params.id },
    data: { status, adminNote },
  })
  return NextResponse.json(complaint)
}
