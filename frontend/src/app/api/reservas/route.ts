import { NextResponse } from "next/server"
import { db } from "@/lib/database"

export async function GET() {
  const reservas = db.reservas.getAll()
  return NextResponse.json(reservas)
}

export async function POST(request: Request) {
  const body = await request.json()
  const newReserva = db.reservas.create(body)

  // Actualizar estado de la habitación
  db.habitaciones.update(body.habitacionId, { estado: "reservada" })

  return NextResponse.json(newReserva, { status: 201 })
}
