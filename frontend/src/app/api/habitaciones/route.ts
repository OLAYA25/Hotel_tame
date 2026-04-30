import { NextResponse } from "next/server"
import { db } from "@/lib/database"

export async function GET() {
  const habitaciones = db.habitaciones.getAll()
  return NextResponse.json(habitaciones)
}

export async function POST(request: Request) {
  const body = await request.json()
  const newHabitacion = db.habitaciones.create(body)
  return NextResponse.json(newHabitacion, { status: 201 })
}
