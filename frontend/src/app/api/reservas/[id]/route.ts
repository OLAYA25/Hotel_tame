import { NextResponse } from "next/server"
import { db } from "@/lib/database"

export async function GET(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const reserva = db.reservas.getById(Number.parseInt(id))

  if (!reserva) {
    return NextResponse.json({ error: "Reserva no encontrada" }, { status: 404 })
  }

  return NextResponse.json(reserva)
}

export async function PUT(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const body = await request.json()
  const reserva = db.reservas.update(Number.parseInt(id), body)

  if (!reserva) {
    return NextResponse.json({ error: "Reserva no encontrada" }, { status: 404 })
  }

  return NextResponse.json(reserva)
}

export async function DELETE(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const reserva = db.reservas.getById(Number.parseInt(id))

  if (reserva) {
    // Liberar la habitación
    db.habitaciones.update(reserva.habitacionId, { estado: "disponible" })
  }

  db.reservas.delete(Number.parseInt(id))
  return NextResponse.json({ success: true })
}
