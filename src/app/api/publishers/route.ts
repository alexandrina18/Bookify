import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
  const publishers = await prisma.publisher.findMany({
    include: { _count: { select: { books: true } } },
    orderBy: { name: 'asc' },
  })
  return NextResponse.json(publishers)
}

export async function POST(req: Request) {
  const { name, location } = await req.json()
  const publisher = await prisma.publisher.create({ data: { name, location } })
  return NextResponse.json(publisher)
}
