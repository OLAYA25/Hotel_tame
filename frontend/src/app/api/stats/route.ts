import { NextResponse } from "next/server"
import { db } from "@/lib/database"

export async function GET() {
  const stats = db.stats.getDashboard()
  return NextResponse.json(stats)
}
