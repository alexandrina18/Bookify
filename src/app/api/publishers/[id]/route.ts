import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function PUT(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const { name, location } = await req.json()
  const publisher = await prisma.publisher.update({
    where: { id },
    data: { name, location },
  })
  return NextResponse.json(publisher)
}

export async function DELETE(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  await prisma.publisher.delete({ where: { id } })
  return NextResponse.json({ message: 'Deleted' })
}
