import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(req: Request, { params }: { params: { id: string } }) {
  const book = await prisma.book.findUnique({
    where: { id: params.id },
    include: { publisher: true, borrowings: { include: { user: true } } },
  })
  if (!book) return NextResponse.json({ error: 'Not found' }, { status: 404 })
  return NextResponse.json(book)
}

export async function PUT(req: Request, { params }: { params: { id: string } }) {
  const body = await req.json()
  const { title, author, year, pages, genre, stock, publisherId } = body

  const book = await prisma.book.update({
    where: { id: params.id },
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

export async function DELETE(req: Request, { params }: { params: { id: string } }) {
  await prisma.book.delete({ where: { id: params.id } })
  return NextResponse.json({ message: 'Deleted' })
}
