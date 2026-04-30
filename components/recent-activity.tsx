"use client"

import { Card, CardContent, CardHeader, CardTitle } from "./ui/card"
import { Badge } from "./ui/badge"
import { Info } from "lucide-react"
import { useEffect, useState } from "react"

interface Reserva {
  id: number
  clienteId: number
  habitacionId: number
  fechaEntrada: Date
  fechaSalida: Date
  estado: string
  total: number
  createdAt: Date
}

interface Cliente {
  id: number
  nombre: string
}

interface Habitacion {
  id: number
  numero: string
}

interface ActivityItem {
  id: number
  guest: string
  room: string
  status: string
  time: string
  amount: string
}

export function RecentActivity() {
  const [activities, setActivities] = useState<ActivityItem[]>([])

  useEffect(() => {
    Promise.all([
      fetch("/api/reservas").then((res) => res.json()),
      fetch("/api/clientes").then((res) => res.json()),
      fetch("/api/habitaciones").then((res) => res.json()),
    ]).then(([reservas, clientes, habitaciones]) => {
      const recentReservas = reservas
        .sort((a: Reserva, b: Reserva) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
        .slice(0, 5)

      const activitiesData = recentReservas.map((reserva: Reserva) => {
        const cliente = clientes.find((c: Cliente) => c.id === reserva.clienteId)
        const habitacion = habitaciones.find((h: Habitacion) => h.id === reserva.habitacionId)

        return {
          id: reserva.id,
          guest: cliente?.nombre || "Cliente desconocido",
          room: `Habitación ${habitacion?.numero || "---"}`,
          status: reserva.estado.charAt(0).toUpperCase() + reserva.estado.slice(1),
          time: getTimeAgo(new Date(reserva.createdAt)),
          amount: `$${reserva.total.toLocaleString()}.00`,
        }
      })

      setActivities(activitiesData)
    })
  }, [])

  const getTimeAgo = (date: Date) => {
    const seconds = Math.floor((new Date().getTime() - date.getTime()) / 1000)
    if (seconds < 60) return "Hace un momento"
    if (seconds < 3600) return `Hace ${Math.floor(seconds / 60)} minuto${Math.floor(seconds / 60) > 1 ? "s" : ""}`
    if (seconds < 86400) return `Hace ${Math.floor(seconds / 3600)} hora${Math.floor(seconds / 3600) > 1 ? "s" : ""}`
    return `Hace ${Math.floor(seconds / 86400)} día${Math.floor(seconds / 86400) > 1 ? "s" : ""}`
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Actividad Reciente</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        {activities.length === 0 ? (
          <p className="text-center text-muted-foreground py-8">No hay actividad reciente</p>
        ) : (
          activities.map((activity) => (
            <div
              key={activity.id}
              className="flex items-center justify-between p-4 rounded-lg border border-border hover:bg-muted/50 transition-colors"
            >
              <div className="flex items-center gap-4">
                <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                  <Info className="w-5 h-5 text-primary" />
                </div>
                <div>
                  <p className="font-medium">
                    {activity.guest} - {activity.room}
                  </p>
                  <div className="flex items-center gap-2 mt-1">
                    <Badge variant={activity.status === "Confirmada" ? "default" : "secondary"}>
                      {activity.status}
                    </Badge>
                    <span className="text-xs text-muted-foreground">{activity.time}</span>
                  </div>
                </div>
              </div>
              <div className="text-right">
                <p className="font-semibold text-lg">{activity.amount}</p>
              </div>
            </div>
          ))
        )}
      </CardContent>
    </Card>
  )
}
