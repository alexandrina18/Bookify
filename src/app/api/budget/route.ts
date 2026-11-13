import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
  const budget = await prisma.budget.findMany({ orderBy: { date: 'desc' } })
  return NextResponse.json(budget)
}

export async function POST(req: Request) {
  const { category, description, amount, type, date } = await req.json()
  const item = await prisma.budget.create({
    data: { category, description, amount: Number(amount), type, date: date ? new Date(date) : new Date() },
  })
  return NextResponse.json(item)
}
