import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
  const [totalBooks, totalMembers, totalPublishers, activeBorrowings, overdueBorrowings, 
         todayAttendance, activeVisitors, pendingExtensions, unpaidFines] = await Promise.all([
    prisma.book.count(),
    prisma.user.count({ where: { role: 'member' } }),
    prisma.publisher.count(),
    prisma.borrowing.count({ where: { status: 'active' } }),
    prisma.borrowing.count({ where: { status: 'overdue' } }),
    prisma.attendance.count({
      where: { checkIn: { gte: new Date(new Date().setHours(0, 0, 0, 0)) } }
    }),
    prisma.attendance.count({ where: { checkOut: null } }),
    prisma.extension.count({ where: { status: 'pending' } }),
    prisma.fine.aggregate({ where: { status: 'unpaid' }, _sum: { amount: true } }),
  ])

  // Weekly borrowing chart data (last 7 days)
  const last7Days = Array.from({ length: 7 }, (_, i) => {
    const d = new Date()
    d.setDate(d.getDate() - (6 - i))
    return d
  })

  const borrowingChart = await Promise.all(
    last7Days.map(async (day) => {
      const start = new Date(day.setHours(0, 0, 0, 0))
      const end = new Date(day.setHours(23, 59, 59, 999))
      const borrowed = await prisma.borrowing.count({
        where: { borrowedAt: { gte: start, lte: end } },
      })
      const returned = await prisma.borrowing.count({
        where: { returnedAt: { gte: start, lte: end } },
      })
      return {
        date: start.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' }),
        peminjaman: borrowed,
        pengembalian: returned,
      }
    })
  )

  // Recent activity
  const recentBorrowings = await prisma.borrowing.findMany({
    take: 5,
    orderBy: { borrowedAt: 'desc' },
    include: {
      user: { select: { name: true, memberId: true } },
      book: { select: { title: true, bookId: true } },
    },
  })

  return NextResponse.json({
    stats: {
      totalBooks,
      totalMembers,
      totalPublishers,
      activeBorrowings,
      overdueBorrowings,
      todayAttendance,
      activeVisitors,
      pendingExtensions,
      unpaidFinesTotal: unpaidFines._sum.amount || 0,
    },
    borrowingChart,
    recentBorrowings,
  })
}
