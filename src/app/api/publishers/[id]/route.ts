import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function PUT(req: Request, { params }: { params: { id: string } }) {
  const { name, location } = await req.json()
  const publisher = await prisma.publisher.update({
    where: { id: params.id },
    data: { name, location },
  })
  return NextResponse.json(publisher)
}

export async function DELETE(req: Request, { params }: { params: { id: string } }) {
  await prisma.publisher.delete({ where: { id: params.id } })
  return NextResponse.json({ message: 'Deleted' })
}
