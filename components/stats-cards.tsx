"use client"

import { Hotel, Calendar, Users, DollarSign } from "lucide-react"
import { Card, CardContent } from "./ui/card"
import { useEffect, useState } from "react"

interface Stats {
  totalHabitaciones: number
  disponibles: number
  reservasActivas: number
  totalClientes: number
  ingresosDelMes: number
}

export function StatsCards() {
  const [stats, setStats] = useState<Stats>({
    totalHabitaciones: 0,
    disponibles: 0,
    reservasActivas: 0,
    totalClientes: 0,
    ingresosDelMes: 0,
  })

  useEffect(() => {
    fetch("/api/stats")
      .then((res) => res.json())
      .then((data) => setStats(data))
  }, [])

  const statsData = [
    {
      title: "Total Habitaciones",
      value: stats.totalHabitaciones.toString(),
      subtitle: `${stats.disponibles} disponibles`,
      icon: Hotel,
      iconColor: "text-blue-500",
    },
    {
      title: "Reservas Activas",
      value: stats.reservasActivas.toString(),
      subtitle: "Confirmadas y pendientes",
      icon: Calendar,
      iconColor: "text-green-500",
    },
    {
      title: "Total Clientes",
      value: stats.totalClientes.toString(),
      subtitle: "Clientes registrados",
      icon: Users,
      iconColor: "text-purple-500",
    },
  ]

  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
      {statsData.map((stat) => {
        const Icon = stat.icon
        return (
          <Card key={stat.title} className="overflow-hidden">
            <CardContent className="p-6">
              <div className="flex items-start justify-between">
                <div className="space-y-2">
                  <p className="text-sm text-muted-foreground">{stat.title}</p>
                  <p className="text-3xl font-bold">{stat.value}</p>
                  <p className="text-xs text-muted-foreground">{stat.subtitle}</p>
                </div>
                <div className={`p-3 rounded-lg bg-secondary ${stat.iconColor}`}>
                  <Icon className="w-6 h-6" />
                </div>
              </div>
            </CardContent>
          </Card>
        )
      })}

      <Card className="md:col-span-3">
        <CardContent className="p-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div className="p-3 rounded-lg bg-secondary text-green-600">
                <DollarSign className="w-6 h-6" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Ingresos del Mes</p>
                <p className="text-3xl font-bold mt-1">${stats.ingresosDelMes.toLocaleString()}.00</p>
              </div>
            </div>
            <div className="text-right">
              <p className="text-sm text-muted-foreground">Total del mes actual</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
