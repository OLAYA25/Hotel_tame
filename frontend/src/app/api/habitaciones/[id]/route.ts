import { NextResponse } from "next/server"
import { db } from "@/lib/database"

export async function GET(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const habitacion = db.habitaciones.getById(Number.parseInt(id))

  if (!habitacion) {
    return NextResponse.json({ error: "Habitación no encontrada" }, { status: 404 })
  }

  return NextResponse.json(habitacion)
}

export async function PUT(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const body = await request.json()
  const habitacion = db.habitaciones.update(Number.parseInt(id), body)

  if (!habitacion) {
    return NextResponse.json({ error: "Habitación no encontrada" }, { status: 404 })
  }

  return NextResponse.json(habitacion)
}

export async function DELETE(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  db.habitaciones.delete(Number.parseInt(id))
  return NextResponse.json({ success: true })
}
