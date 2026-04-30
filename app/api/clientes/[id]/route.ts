import { NextResponse } from "next/server"
import { db } from "@/lib/database"

export async function GET(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const cliente = db.clientes.getById(Number.parseInt(id))

  if (!cliente) {
    return NextResponse.json({ error: "Cliente no encontrado" }, { status: 404 })
  }

  return NextResponse.json(cliente)
}

export async function PUT(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const body = await request.json()
  const cliente = db.clientes.update(Number.parseInt(id), body)

  if (!cliente) {
    return NextResponse.json({ error: "Cliente no encontrado" }, { status: 404 })
  }

  return NextResponse.json(cliente)
}

export async function DELETE(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  db.clientes.delete(Number.parseInt(id))
  return NextResponse.json({ success: true })
}
