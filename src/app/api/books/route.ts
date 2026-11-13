import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(req: Request) {
  const { searchParams } = new URL(req.url)
  const search = searchParams.get('search') || ''
  const genre = searchParams.get('genre') || ''

  const books = await prisma.book.findMany({
    where: {
      AND: [
        search ? {
          OR: [
            { title: { contains: search } },
            { author: { contains: search } },
            { bookId: { contains: search } },
          ]
        } : {},
        genre ? { genre: { contains: genre } } : {},
      ]
    },
    include: { publisher: true },
    orderBy: { title: 'asc' },
  })

  return NextResponse.json(books)
}

export async function POST(req: Request) {
  const body = await req.json()
  const { bookId, title, author, year, pages, genre, stock, publisherId } = body

  const book = await prisma.book.create({
    data: {
      bookId,
      title,
      author,
      year: year ? Number(year) : null,
      pages: pages ? Number(pages) : null,
      genre,
      stock: Number(stock),
      available: Number(stock),
      publisherId: publisherId || null,
    },
    include: { publisher: true },
  })

  return NextResponse.json(book)
}
