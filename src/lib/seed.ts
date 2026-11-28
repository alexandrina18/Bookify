import { prisma } from './prisma'
import bcrypt from 'bcryptjs'

export async function seedDatabase() {
  console.log('🌱 Seeding database...')

  // Admin
  const adminPass = await bcrypt.hash('admin123', 10)
  await prisma.user.upsert({
    where: { memberId: 'ADMIN001' },
    update: {},
    create: {
      memberId: 'ADMIN001',
      name: 'Petugas Perpustakaan SMA GLITZ',
      email: 'admin@smaglitz.sch.id',
      phone: '081234567890',
      gender: 'male',
      address: 'Perpustakaan SMA GLITZ, Jl. Glitz No. 1, Semarang',
      password: adminPass,
      role: 'admin',
      status: 'active',
    },
  })

  // Publishers
  const pub1 = await prisma.publisher.upsert({
    where: { id: 'pub1' },
    update: {},
    create: { id: 'pub1', name: 'Gramedia Pustaka Utama', location: 'Jakarta' },
  })
  const pub2 = await prisma.publisher.upsert({
    where: { id: 'pub2' },
    update: {},
    create: { id: 'pub2', name: 'Erlangga', location: 'Jakarta' },
  })
  const pub3 = await prisma.publisher.upsert({
    where: { id: 'pub3' },
    update: {},
    create: { id: 'pub3', name: 'Mizan', location: 'Bandung' },
  })
  const pub4 = await prisma.publisher.upsert({
    where: { id: 'pub4' },
    update: {},
    create: { id: 'pub4', name: 'Kompas', location: 'Jakarta' },
  })

  // Books
  const books = [
    { bookId: 'BK001', title: 'Laskar Pelangi', author: 'Andrea Hirata', year: 2005, pages: 529, genre: 'Novel', stock: 3, available: 2, publisherId: pub3.id },
    { bookId: 'BK002', title: 'Bumi Manusia', author: 'Pramoedya Ananta Toer', year: 1980, pages: 535, genre: 'Novel', stock: 2, available: 2, publisherId: pub1.id },
    { bookId: 'BK003', title: 'Pemrograman Web dengan Next.js', author: 'Ahmad Fauzi', year: 2023, pages: 420, genre: 'Teknologi', stock: 4, available: 3, publisherId: pub2.id },
    { bookId: 'BK004', title: 'Dasar-dasar Sistem Informasi', author: 'Budi Sutedjo', year: 2022, pages: 310, genre: 'Teknologi', stock: 5, available: 5, publisherId: pub2.id },
    { bookId: 'BK005', title: 'Filosofi Teras', author: 'Henry Manampiring', year: 2019, pages: 316, genre: 'Filsafat', stock: 3, available: 1, publisherId: pub1.id },
    { bookId: 'BK006', title: 'Atomic Habits', author: 'James Clear', year: 2018, pages: 319, genre: 'Pengembangan Diri', stock: 4, available: 4, publisherId: pub1.id },
    { bookId: 'BK007', title: 'Sapiens: Riwayat Singkat Umat Manusia', author: 'Yuval Noah Harari', year: 2011, pages: 512, genre: 'Sejarah', stock: 2, available: 2, publisherId: pub1.id },
    { bookId: 'BK008', title: 'The Alchemist', author: 'Paulo Coelho', year: 1988, pages: 197, genre: 'Novel', stock: 3, available: 3, publisherId: pub3.id },
    { bookId: 'BK009', title: 'Pengantar Basis Data', author: 'Raghu Ramakrishnan', year: 2020, pages: 440, genre: 'Teknologi', stock: 3, available: 2, publisherId: pub2.id },
    { bookId: 'BK010', title: 'Rich Dad Poor Dad', author: 'Robert T. Kiyosaki', year: 1997, pages: 207, genre: 'Keuangan', stock: 2, available: 2, publisherId: pub4.id },
    { bookId: 'BK011', title: 'Negeri 5 Menara', author: 'Ahmad Fuadi', year: 2009, pages: 423, genre: 'Novel', stock: 2, available: 2, publisherId: pub1.id },
    { bookId: 'BK012', title: 'Desain UI/UX Modern', author: 'Siti Rahma', year: 2024, pages: 280, genre: 'Teknologi', stock: 3, available: 3, publisherId: pub2.id },
  ]

  for (const book of books) {
    await prisma.book.upsert({
      where: { bookId: book.bookId },
      update: {},
      create: book,
    })
  }

  // Students of SMA GLITZ
  const memberPass = await bcrypt.hash('member123', 10)
  const members = [
    { memberId: 'MBR001', name: 'Jolanda Aurelia Susanto', email: 'jolanda@smaglitz.sch.id', phone: '081122334455', gender: 'female', address: 'Jl. Merdeka No. 15, Semarang' },
    { memberId: 'MBR002', name: 'Theodora Trixie Sundari', email: 'theodora@smaglitz.sch.id', phone: '082233445566', gender: 'female', address: 'Jl. Pahlawan No. 28, Semarang' },
    { memberId: 'MBR003', name: 'Alexandrina Immanuela Sahar', email: 'alexandrina@smaglitz.sch.id', phone: '083344556677', gender: 'female', address: 'Jl. Pemuda No. 102, Semarang' },
    { memberId: 'MBR004', name: 'Bonifasius Frith Farandy', email: 'bonifasius@smaglitz.sch.id', phone: '084455667788', gender: 'male', address: 'Jl. Diponegoro No. 44, Semarang' },
    { memberId: 'MBR005', name: 'Thomas Nova Wibisono', email: 'thomas@smaglitz.sch.id', phone: '085566778899', gender: 'male', address: 'Jl. Sudirman No. 89, Semarang' },
    { memberId: 'MBR006', name: 'Aditiya Pratama', email: 'aditiya@smaglitz.sch.id', phone: '081234567801', gender: 'male', address: 'Jl. Gajah Mada No. 17, Semarang' },
    { memberId: 'MBR007', name: 'Clarissa Putri Anindya', email: 'clarissa@smaglitz.sch.id', phone: '081234567802', gender: 'female', address: 'Jl. Pandanaran No. 56, Semarang' },
    { memberId: 'MBR008', name: 'Dimas Arya Wicaksana', email: 'dimas@smaglitz.sch.id', phone: '081234567803', gender: 'male', address: 'Jl. Erlangga Tengah No. 9, Semarang' },
    { memberId: 'MBR009', name: 'Felicia Evelyn Hartanto', email: 'felicia@smaglitz.sch.id', phone: '081234567804', gender: 'female', address: 'Jl. Sumbing No. 14, Semarang' },
    { memberId: 'MBR010', name: 'Kevin Jonathan Kusumo', email: 'kevin@smaglitz.sch.id', phone: '081234567805', gender: 'male', address: 'Jl. Siranda No. 3, Semarang' },
  ]

  const createdMembers = []
  for (const member of members) {
    const created = await prisma.user.upsert({
      where: { memberId: member.memberId },
      update: {},
      create: { ...member, password: memberPass, role: 'member', status: 'active' },
    })
    createdMembers.push(created)
  }

  // Sample Borrowings
  const book1 = await prisma.book.findUnique({ where: { bookId: 'BK001' } })
  const book5 = await prisma.book.findUnique({ where: { bookId: 'BK005' } })
  const book9 = await prisma.book.findUnique({ where: { bookId: 'BK009' } })

  if (book1 && createdMembers[0]) {
    await prisma.borrowing.upsert({
      where: { id: 'borrow1' },
      update: {},
      create: {
        id: 'borrow1',
        userId: createdMembers[0].id,
        bookId: book1.id,
        borrowedAt: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000),
        dueDate: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000),
        status: 'overdue',
        renewCount: 0,
      },
    })
    await prisma.fine.upsert({
      where: { id: 'fine1' },
      update: {},
      create: {
        id: 'fine1',
        userId: createdMembers[0].id,
        borrowingId: 'borrow1',
        daysLate: 3,
        amount: 3000,
        status: 'unpaid',
        type: 'late',
      },
    })
  }

  if (book5 && createdMembers[1]) {
    await prisma.borrowing.upsert({
      where: { id: 'borrow2' },
      update: {},
      create: {
        id: 'borrow2',
        userId: createdMembers[1].id,
        bookId: book5.id,
        borrowedAt: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000),
        dueDate: new Date(Date.now() + 4 * 24 * 60 * 60 * 1000),
        status: 'active',
        renewCount: 1,
      },
    })
  }

  if (book9 && createdMembers[2]) {
    await prisma.borrowing.upsert({
      where: { id: 'borrow3' },
      update: {},
      create: {
        id: 'borrow3',
        userId: createdMembers[2].id,
        bookId: book9.id,
        borrowedAt: new Date(Date.now() - 14 * 24 * 60 * 60 * 1000),
        dueDate: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000),
        returnedAt: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000),
        status: 'returned',
        renewCount: 0,
      },
    })
  }

  // Sample Attendance
  if (createdMembers[0]) {
    await prisma.attendance.upsert({
      where: { id: 'att1' },
      update: {},
      create: {
        id: 'att1',
        userId: createdMembers[0].id,
        checkIn: new Date(Date.now() - 2 * 60 * 60 * 1000),
        checkOut: null,
        duration: null,
      },
    })
  }

  // Budget entries
  const budgetItems = [
    { category: 'salary', description: 'Gaji Project Manager (2 bulan)', amount: 16000000, type: 'expense' },
    { category: 'salary', description: 'Gaji System Analyst & Designer (2 bulan)', amount: 14000000, type: 'expense' },
    { category: 'salary', description: 'Gaji Developer Backend (2 bulan)', amount: 16000000, type: 'expense' },
    { category: 'salary', description: 'Gaji Developer Frontend (2 bulan)', amount: 14000000, type: 'expense' },
    { category: 'salary', description: 'Gaji Tester (1 bulan)', amount: 5000000, type: 'expense' },
    { category: 'infrastructure', description: 'Hosting/VPS 1 tahun, domain, email', amount: 5000000, type: 'expense' },
    { category: 'equipment', description: '2 scanner barcode, 2 webcam, printer, stiker', amount: 5500000, type: 'expense' },
    { category: 'books', description: 'Pengadaan ~200 buku awal', amount: 15000000, type: 'expense' },
    { category: 'operations', description: 'Biaya operasional 3 bulan awal', amount: 7500000, type: 'expense' },
  ]

  for (let i = 0; i < budgetItems.length; i++) {
    await prisma.budget.upsert({
      where: { id: `budget${i + 1}` },
      update: {},
      create: { id: `budget${i + 1}`, ...budgetItems[i], date: new Date() },
    })
  }

  console.log('✅ Database seeded successfully!')
}
