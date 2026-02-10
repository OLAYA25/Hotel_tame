// Sistema de base de datos simulado en memoria
export interface Usuario {
  id: number
  nombre: string
  email: string
  rol: "admin" | "staff" | "viewer"
  telefono: string
  activo: boolean
  createdAt: Date
}

export interface Cliente {
  id: number
  nombre: string
  email: string
  telefono: string
  documento: string
  direccion: string
  createdAt: Date
}

export interface Habitacion {
  id: number
  numero: string
  tipo: "simple" | "doble" | "suite" | "presidencial"
  precio: number
  estado: "disponible" | "ocupada" | "mantenimiento" | "reservada"
  piso: number
  capacidad: number
  descripcion: string
}

export interface Reserva {
  id: number
  clienteId: number
  habitacionId: number
  fechaEntrada: Date
  fechaSalida: Date
  estado: "confirmada" | "pendiente" | "cancelada" | "completada"
  total: number
  metodoPago: string
  noches: number
  createdAt: Date
}

// Datos iniciales
let usuarios: Usuario[] = [
  {
    id: 1,
    nombre: "Admin Principal",
    email: "admin@hotel.com",
    rol: "admin",
    telefono: "+1234567890",
    activo: true,
    createdAt: new Date("2024-01-01"),
  },
  {
    id: 2,
    nombre: "María González",
    email: "maria@hotel.com",
    rol: "staff",
    telefono: "+1234567891",
    activo: true,
    createdAt: new Date("2024-02-15"),
  },
  {
    id: 3,
    nombre: "Carlos Ruiz",
    email: "carlos@hotel.com",
    rol: "staff",
    telefono: "+1234567892",
    activo: true,
    createdAt: new Date("2024-03-10"),
  },
]

let clientes: Cliente[] = [
  {
    id: 1,
    nombre: "Jhuliet Tibasosa",
    email: "jhuliet@email.com",
    telefono: "+1234567893",
    documento: "12345678",
    direccion: "Calle Principal 123",
    createdAt: new Date("2024-11-01"),
  },
  {
    id: 2,
    nombre: "Juan Pérez",
    email: "juan@email.com",
    telefono: "+1234567894",
    documento: "87654321",
    direccion: "Avenida Central 456",
    createdAt: new Date("2024-11-10"),
  },
  {
    id: 3,
    nombre: "Ana López",
    email: "ana@email.com",
    telefono: "+1234567895",
    documento: "11223344",
    direccion: "Plaza Mayor 789",
    createdAt: new Date("2024-11-15"),
  },
]

let habitaciones: Habitacion[] = [
  {
    id: 1,
    numero: "101",
    tipo: "suite",
    precio: 450000,
    estado: "ocupada",
    piso: 1,
    capacidad: 4,
    descripcion: "Suite de lujo con vista al mar",
  },
  {
    id: 2,
    numero: "102",
    tipo: "simple",
    precio: 400,
    estado: "ocupada",
    piso: 1,
    capacidad: 2,
    descripcion: "Habitación simple confortable",
  },
  {
    id: 3,
    numero: "201",
    tipo: "doble",
    precio: 600,
    estado: "reservada",
    piso: 2,
    capacidad: 3,
    descripcion: "Habitación doble con balcón",
  },
  {
    id: 4,
    numero: "202",
    tipo: "simple",
    precio: 350,
    estado: "disponible",
    piso: 2,
    capacidad: 2,
    descripcion: "Habitación simple económica",
  },
  {
    id: 5,
    numero: "301",
    tipo: "presidencial",
    precio: 1200,
    estado: "disponible",
    piso: 3,
    capacidad: 6,
    descripcion: "Suite presidencial de lujo",
  },
]

let reservas: Reserva[] = [
  {
    id: 1,
    clienteId: 1,
    habitacionId: 1,
    fechaEntrada: new Date("2024-12-01"),
    fechaSalida: new Date("2024-12-05"),
    estado: "confirmada",
    total: 450000,
    metodoPago: "Tarjeta de crédito",
    noches: 1,
    createdAt: new Date(),
  },
  {
    id: 2,
    clienteId: 2,
    habitacionId: 2,
    fechaEntrada: new Date("2024-12-10"),
    fechaSalida: new Date("2024-12-12"),
    estado: "confirmada",
    total: 400,
    metodoPago: "Efectivo",
    noches: 2,
    createdAt: new Date(),
  },
  {
    id: 3,
    clienteId: 3,
    habitacionId: 3,
    fechaEntrada: new Date("2024-12-15"),
    fechaSalida: new Date("2024-12-18"),
    estado: "pendiente",
    total: 600,
    metodoPago: "Transferencia",
    noches: 1,
    createdAt: new Date(),
  },
]

// Funciones CRUD para Usuarios
export const db = {
  usuarios: {
    getAll: () => usuarios,
    getById: (id: number) => usuarios.find((u) => u.id === id),
    create: (usuario: Omit<Usuario, "id" | "createdAt">) => {
      const newUsuario = {
        ...usuario,
        id: Math.max(...usuarios.map((u) => u.id), 0) + 1,
        createdAt: new Date(),
      }
      usuarios.push(newUsuario)
      return newUsuario
    },
    update: (id: number, data: Partial<Usuario>) => {
      const index = usuarios.findIndex((u) => u.id === id)
      if (index !== -1) {
        usuarios[index] = { ...usuarios[index], ...data }
        return usuarios[index]
      }
      return null
    },
    delete: (id: number) => {
      usuarios = usuarios.filter((u) => u.id !== id)
    },
  },
  clientes: {
    getAll: () => clientes,
    getById: (id: number) => clientes.find((c) => c.id === id),
    create: (cliente: Omit<Cliente, "id" | "createdAt">) => {
      const newCliente = {
        ...cliente,
        id: Math.max(...clientes.map((c) => c.id), 0) + 1,
        createdAt: new Date(),
      }
      clientes.push(newCliente)
      return newCliente
    },
    update: (id: number, data: Partial<Cliente>) => {
      const index = clientes.findIndex((c) => c.id === id)
      if (index !== -1) {
        clientes[index] = { ...clientes[index], ...data }
        return clientes[index]
      }
      return null
    },
    delete: (id: number) => {
      clientes = clientes.filter((c) => c.id !== id)
    },
  },
  habitaciones: {
    getAll: () => habitaciones,
    getById: (id: number) => habitaciones.find((h) => h.id === id),
    getDisponibles: () => habitaciones.filter((h) => h.estado === "disponible"),
    create: (habitacion: Omit<Habitacion, "id">) => {
      const newHabitacion = {
        ...habitacion,
        id: Math.max(...habitaciones.map((h) => h.id), 0) + 1,
      }
      habitaciones.push(newHabitacion)
      return newHabitacion
    },
    update: (id: number, data: Partial<Habitacion>) => {
      const index = habitaciones.findIndex((h) => h.id === id)
      if (index !== -1) {
        habitaciones[index] = { ...habitaciones[index], ...data }
        return habitaciones[index]
      }
      return null
    },
    delete: (id: number) => {
      habitaciones = habitaciones.filter((h) => h.id !== id)
    },
  },
  reservas: {
    getAll: () => reservas,
    getById: (id: number) => reservas.find((r) => r.id === id),
    getRecent: (limit = 10) => {
      return [...reservas].sort((a, b) => b.createdAt.getTime() - a.createdAt.getTime()).slice(0, limit)
    },
    create: (reserva: Omit<Reserva, "id" | "createdAt">) => {
      const newReserva = {
        ...reserva,
        id: Math.max(...reservas.map((r) => r.id), 0) + 1,
        createdAt: new Date(),
      }
      reservas.push(newReserva)
      return newReserva
    },
    update: (id: number, data: Partial<Reserva>) => {
      const index = reservas.findIndex((r) => r.id === id)
      if (index !== -1) {
        reservas[index] = { ...reservas[index], ...data }
        return reservas[index]
      }
      return null
    },
    delete: (id: number) => {
      reservas = reservas.filter((r) => r.id !== id)
    },
  },
  stats: {
    getDashboard: () => {
      const totalHabitaciones = habitaciones.length
      const disponibles = habitaciones.filter((h) => h.estado === "disponible").length
      const reservasActivas = reservas.filter((r) => r.estado === "confirmada" || r.estado === "pendiente").length
      const totalClientes = clientes.length
      const ingresosDelMes = reservas.filter((r) => r.estado === "confirmada").reduce((sum, r) => sum + r.total, 0)

      return {
        totalHabitaciones,
        disponibles,
        reservasActivas,
        totalClientes,
        ingresosDelMes,
      }
    },
  },
}
