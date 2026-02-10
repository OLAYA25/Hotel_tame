"use client"

import type React from "react"

import { DashboardLayout } from "@/components/dashboard-layout"
import { Card, CardContent } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Plus, Calendar, Edit, Trash2, User, Bed, DollarSign } from "lucide-react"
import { useEffect, useState } from "react"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogFooter,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

interface Reserva {
  id: number
  clienteId: number
  habitacionId: number
  fechaEntrada: string
  fechaSalida: string
  estado: string
  total: number
  metodoPago: string
  noches: number
}

interface Cliente {
  id: number
  nombre: string
}

interface Habitacion {
  id: number
  numero: string
  tipo: string
}

export default function ReservasPage() {
  const [reservas, setReservas] = useState<Reserva[]>([])
  const [clientes, setClientes] = useState<Cliente[]>([])
  const [habitaciones, setHabitaciones] = useState<Habitacion[]>([])
  const [isOpen, setIsOpen] = useState(false)
  const [editingReserva, setEditingReserva] = useState<Reserva | null>(null)
  const [formData, setFormData] = useState({
    clienteId: "",
    habitacionId: "",
    fechaEntrada: "",
    fechaSalida: "",
    estado: "pendiente",
    total: "",
    metodoPago: "Efectivo",
    noches: "",
  })

  useEffect(() => {
    loadData()
  }, [])

  const loadData = async () => {
    const [reservasRes, clientesRes, habitacionesRes] = await Promise.all([
      fetch("/api/reservas").then((r) => r.json()),
      fetch("/api/clientes").then((r) => r.json()),
      fetch("/api/habitaciones").then((r) => r.json()),
    ])
    setReservas(reservasRes)
    setClientes(clientesRes)
    setHabitaciones(habitacionesRes)
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    const payload = {
      ...formData,
      clienteId: Number.parseInt(formData.clienteId),
      habitacionId: Number.parseInt(formData.habitacionId),
      total: Number.parseFloat(formData.total),
      noches: Number.parseInt(formData.noches),
    }

    if (editingReserva) {
      await fetch(`/api/reservas/${editingReserva.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })
    } else {
      await fetch("/api/reservas", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })
    }

    setIsOpen(false)
    setEditingReserva(null)
    setFormData({
      clienteId: "",
      habitacionId: "",
      fechaEntrada: "",
      fechaSalida: "",
      estado: "pendiente",
      total: "",
      metodoPago: "Efectivo",
      noches: "",
    })
    loadData()
  }

  const handleEdit = (reserva: Reserva) => {
    setEditingReserva(reserva)
    setFormData({
      clienteId: reserva.clienteId.toString(),
      habitacionId: reserva.habitacionId.toString(),
      fechaEntrada: reserva.fechaEntrada.split("T")[0],
      fechaSalida: reserva.fechaSalida.split("T")[0],
      estado: reserva.estado,
      total: reserva.total.toString(),
      metodoPago: reserva.metodoPago,
      noches: reserva.noches.toString(),
    })
    setIsOpen(true)
  }

  const handleDelete = async (id: number) => {
    if (confirm("¿Está seguro de eliminar esta reserva?")) {
      await fetch(`/api/reservas/${id}`, { method: "DELETE" })
      loadData()
    }
  }

  const getEstadoBadge = (estado: string) => {
    const variants: Record<string, { variant: "default" | "secondary" | "destructive" | "outline"; label: string }> = {
      confirmada: { variant: "default", label: "Confirmada" },
      pendiente: { variant: "secondary", label: "Pendiente" },
      cancelada: { variant: "destructive", label: "Cancelada" },
      completada: { variant: "outline", label: "Completada" },
    }
    const config = variants[estado] || variants.pendiente
    return <Badge variant={config.variant}>{config.label}</Badge>
  }

  const getClienteName = (id: number) => {
    return clientes.find((c) => c.id === id)?.nombre || "N/A"
  }

  const getHabitacionInfo = (id: number) => {
    const hab = habitaciones.find((h) => h.id === id)
    return hab ? `Hab. ${hab.numero} (${hab.tipo})` : "N/A"
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Reservas</h1>
            <p className="text-muted-foreground mt-2">Gestiona las reservas del hotel</p>
          </div>
          <Dialog open={isOpen} onOpenChange={setIsOpen}>
            <DialogTrigger asChild>
              <Button
                onClick={() => {
                  setEditingReserva(null)
                  setFormData({
                    clienteId: "",
                    habitacionId: "",
                    fechaEntrada: "",
                    fechaSalida: "",
                    estado: "pendiente",
                    total: "",
                    metodoPago: "Efectivo",
                    noches: "",
                  })
                }}
              >
                <Plus className="w-4 h-4 mr-2" />
                Nueva Reserva
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>{editingReserva ? "Editar" : "Nueva"} Reserva</DialogTitle>
                <DialogDescription>Complete los datos de la reserva</DialogDescription>
              </DialogHeader>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="clienteId">Cliente</Label>
                  <Select
                    value={formData.clienteId}
                    onValueChange={(value) => setFormData({ ...formData, clienteId: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccione un cliente" />
                    </SelectTrigger>
                    <SelectContent>
                      {clientes.map((cliente) => (
                        <SelectItem key={cliente.id} value={cliente.id.toString()}>
                          {cliente.nombre}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="habitacionId">Habitación</Label>
                  <Select
                    value={formData.habitacionId}
                    onValueChange={(value) => setFormData({ ...formData, habitacionId: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccione una habitación" />
                    </SelectTrigger>
                    <SelectContent>
                      {habitaciones.map((habitacion) => (
                        <SelectItem key={habitacion.id} value={habitacion.id.toString()}>
                          Hab. {habitacion.numero} - {habitacion.tipo}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="fechaEntrada">Fecha Entrada</Label>
                    <Input
                      id="fechaEntrada"
                      type="date"
                      value={formData.fechaEntrada}
                      onChange={(e) => setFormData({ ...formData, fechaEntrada: e.target.value })}
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="fechaSalida">Fecha Salida</Label>
                    <Input
                      id="fechaSalida"
                      type="date"
                      value={formData.fechaSalida}
                      onChange={(e) => setFormData({ ...formData, fechaSalida: e.target.value })}
                      required
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="noches">Noches</Label>
                    <Input
                      id="noches"
                      type="number"
                      value={formData.noches}
                      onChange={(e) => setFormData({ ...formData, noches: e.target.value })}
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="total">Total</Label>
                    <Input
                      id="total"
                      type="number"
                      value={formData.total}
                      onChange={(e) => setFormData({ ...formData, total: e.target.value })}
                      required
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="metodoPago">Método de Pago</Label>
                    <Select
                      value={formData.metodoPago}
                      onValueChange={(value) => setFormData({ ...formData, metodoPago: value })}
                    >
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Efectivo">Efectivo</SelectItem>
                        <SelectItem value="Tarjeta de crédito">Tarjeta de crédito</SelectItem>
                        <SelectItem value="Transferencia">Transferencia</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="estado">Estado</Label>
                    <Select
                      value={formData.estado}
                      onValueChange={(value) => setFormData({ ...formData, estado: value })}
                    >
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="pendiente">Pendiente</SelectItem>
                        <SelectItem value="confirmada">Confirmada</SelectItem>
                        <SelectItem value="completada">Completada</SelectItem>
                        <SelectItem value="cancelada">Cancelada</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <DialogFooter>
                  <Button type="submit">{editingReserva ? "Actualizar" : "Crear"}</Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {reservas.map((reserva) => (
            <Card key={reserva.id}>
              <CardContent className="p-6">
                <div className="flex items-start justify-between mb-4">
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                      <Calendar className="w-5 h-5 text-primary" />
                    </div>
                    <div>
                      <h3 className="font-semibold">Reserva #{reserva.id}</h3>
                      {getEstadoBadge(reserva.estado)}
                    </div>
                  </div>
                  <div className="flex gap-2">
                    <Button variant="outline" size="sm" onClick={() => handleEdit(reserva)}>
                      <Edit className="w-4 h-4" />
                    </Button>
                    <Button variant="destructive" size="sm" onClick={() => handleDelete(reserva.id)}>
                      <Trash2 className="w-4 h-4" />
                    </Button>
                  </div>
                </div>

                <div className="space-y-3">
                  <div className="flex items-center gap-2 text-sm">
                    <User className="w-4 h-4 text-muted-foreground" />
                    <span className="font-medium">{getClienteName(reserva.clienteId)}</span>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <Bed className="w-4 h-4 text-muted-foreground" />
                    <span>{getHabitacionInfo(reserva.habitacionId)}</span>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <Calendar className="w-4 h-4 text-muted-foreground" />
                    <span>
                      {new Date(reserva.fechaEntrada).toLocaleDateString()} -{" "}
                      {new Date(reserva.fechaSalida).toLocaleDateString()}
                    </span>
                    <Badge variant="outline" className="ml-2">
                      {reserva.noches} noche{reserva.noches > 1 ? "s" : ""}
                    </Badge>
                  </div>
                  <div className="flex items-center justify-between pt-3 border-t">
                    <div className="flex items-center gap-2">
                      <DollarSign className="w-4 h-4 text-muted-foreground" />
                      <span className="text-sm text-muted-foreground">{reserva.metodoPago}</span>
                    </div>
                    <span className="text-lg font-bold text-primary">${reserva.total.toLocaleString()}</span>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </DashboardLayout>
  )
}
