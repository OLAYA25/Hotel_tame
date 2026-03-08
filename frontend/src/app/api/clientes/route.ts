import { NextResponse } from "next/server"
import { db } from "@/lib/database"

export async function GET() {
  const clientes = db.clientes.getAll()
  return NextResponse.json(clientes)
}

export async function POST(request: Request) {
  const body = await request.json()
  const newCliente = db.clientes.create(body)
  return NextResponse.json(newCliente, { status: 201 })
}
