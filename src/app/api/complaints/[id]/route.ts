import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function PUT(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const { status, adminNote } = await req.json()
  const complaint = await prisma.complaint.update({
    where: { id },
    data: { status, adminNote },
  })
  return NextResponse.json(complaint)
}
