import { NextResponse } from "next/server"
import { db } from "@/lib/database"

export async function GET() {
  const usuarios = db.usuarios.getAll()
  return NextResponse.json(usuarios)
}

export async function POST(request: Request) {
  const body = await request.json()
  const newUsuario = db.usuarios.create(body)
  return NextResponse.json(newUsuario, { status: 201 })
}
