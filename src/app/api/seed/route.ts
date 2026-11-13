import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { seedDatabase } from '@/lib/seed'

export async function GET() {
  try {
    await seedDatabase()
    return NextResponse.json({ message: 'Database seeded successfully' })
  } catch (error) {
    console.error(error)
    return NextResponse.json({ error: 'Seed failed' }, { status: 500 })
  }
}
