import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const book = await prisma.book.findUnique({
    where: { id },
    include: { publisher: true, borrowings: { include: { user: true } } },
  })
  if (!book) return NextResponse.json({ error: 'Not found' }, { status: 404 })
  return NextResponse.json(book)
}

export async function PUT(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const body = await req.json()
  const { title, author, year, pages, genre, stock, publisherId } = body

  const book = await prisma.book.update({
    where: { id },
    data: {
      title,
      author,
      year: year ? Number(year) : null,
      pages: pages ? Number(pages) : null,
      genre,
      stock: Number(stock),
      publisherId: publisherId || null,
    },
    include: { publisher: true },
  })

  return NextResponse.json(book)
}

export async function DELETE(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  await prisma.book.delete({ where: { id } })
  return NextResponse.json({ message: 'Deleted' })
}
