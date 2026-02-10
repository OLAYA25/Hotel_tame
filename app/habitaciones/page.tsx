"use client"

import type React from "react"

import { DashboardLayout } from "@/components/dashboard-layout"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Plus, Bed, Edit, Trash2, Users } from "lucide-react"
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

interface Habitacion {
  id: number
  numero: string
  tipo: string
  precio: number
  estado: string
  piso: number
  capacidad: number
  descripcion: string
}

export default function HabitacionesPage() {
  const [habitaciones, setHabitaciones] = useState<Habitacion[]>([])
  const [isOpen, setIsOpen] = useState(false)
  const [editingHabitacion, setEditingHabitacion] = useState<Habitacion | null>(null)
  const [formData, setFormData] = useState({
    numero: "",
    tipo: "simple",
    precio: "",
    estado: "disponible",
    piso: "",
    capacidad: "",
    descripcion: "",
  })

  useEffect(() => {
    loadHabitaciones()
  }, [])

  const loadHabitaciones = async () => {
    const res = await fetch("/api/habitaciones")
    const data = await res.json()
    setHabitaciones(data)
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    const payload = {
      ...formData,
      precio: Number.parseFloat(formData.precio),
      piso: Number.parseInt(formData.piso),
      capacidad: Number.parseInt(formData.capacidad),
    }

    if (editingHabitacion) {
      await fetch(`/api/habitaciones/${editingHabitacion.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })
    } else {
      await fetch("/api/habitaciones", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })
    }

    setIsOpen(false)
    setEditingHabitacion(null)
    setFormData({
      numero: "",
      tipo: "simple",
      precio: "",
      estado: "disponible",
      piso: "",
      capacidad: "",
      descripcion: "",
    })
    loadHabitaciones()
  }

  const handleEdit = (habitacion: Habitacion) => {
    setEditingHabitacion(habitacion)
    setFormData({
      numero: habitacion.numero,
      tipo: habitacion.tipo,
      precio: habitacion.precio.toString(),
      estado: habitacion.estado,
      piso: habitacion.piso.toString(),
      capacidad: habitacion.capacidad.toString(),
      descripcion: habitacion.descripcion,
    })
    setIsOpen(true)
  }

  const handleDelete = async (id: number) => {
    if (confirm("¿Está seguro de eliminar esta habitación?")) {
      await fetch(`/api/habitaciones/${id}`, { method: "DELETE" })
      loadHabitaciones()
    }
  }

  const getEstadoBadge = (estado: string) => {
    const variants: Record<string, { variant: "default" | "secondary" | "destructive" | "outline"; label: string }> = {
      disponible: { variant: "default", label: "Disponible" },
      ocupada: { variant: "destructive", label: "Ocupada" },
      mantenimiento: { variant: "secondary", label: "Mantenimiento" },
      reservada: { variant: "outline", label: "Reservada" },
    }
    const config = variants[estado] || variants.disponible
    return <Badge variant={config.variant}>{config.label}</Badge>
  }

  const getTipoLabel = (tipo: string) => {
    const tipos: Record<string, string> = {
      simple: "Simple",
      doble: "Doble",
      suite: "Suite",
      presidencial: "Presidencial",
    }
    return tipos[tipo] || tipo
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Habitaciones</h1>
            <p className="text-muted-foreground mt-2">Gestiona las habitaciones del hotel</p>
          </div>
          <Dialog open={isOpen} onOpenChange={setIsOpen}>
            <DialogTrigger asChild>
              <Button
                onClick={() => {
                  setEditingHabitacion(null)
                  setFormData({
                    numero: "",
                    tipo: "simple",
                    precio: "",
                    estado: "disponible",
                    piso: "",
                    capacidad: "",
                    descripcion: "",
                  })
                }}
              >
                <Plus className="w-4 h-4 mr-2" />
                Nueva Habitación
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-md">
              <DialogHeader>
                <DialogTitle>{editingHabitacion ? "Editar" : "Nueva"} Habitación</DialogTitle>
                <DialogDescription>Complete los datos de la habitación</DialogDescription>
              </DialogHeader>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="numero">Número</Label>
                    <Input
                      id="numero"
                      value={formData.numero}
                      onChange={(e) => setFormData({ ...formData, numero: e.target.value })}
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="piso">Piso</Label>
                    <Input
                      id="piso"
                      type="number"
                      value={formData.piso}
                      onChange={(e) => setFormData({ ...formData, piso: e.target.value })}
                      required
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="tipo">Tipo</Label>
                    <Select value={formData.tipo} onValueChange={(value) => setFormData({ ...formData, tipo: value })}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="simple">Simple</SelectItem>
                        <SelectItem value="doble">Doble</SelectItem>
                        <SelectItem value="suite">Suite</SelectItem>
                        <SelectItem value="presidencial">Presidencial</SelectItem>
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
                        <SelectItem value="disponible">Disponible</SelectItem>
                        <SelectItem value="ocupada">Ocupada</SelectItem>
                        <SelectItem value="mantenimiento">Mantenimiento</SelectItem>
                        <SelectItem value="reservada">Reservada</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="precio">Precio</Label>
                    <Input
                      id="precio"
                      type="number"
                      value={formData.precio}
                      onChange={(e) => setFormData({ ...formData, precio: e.target.value })}
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="capacidad">Capacidad</Label>
                    <Input
                      id="capacidad"
                      type="number"
                      value={formData.capacidad}
                      onChange={(e) => setFormData({ ...formData, capacidad: e.target.value })}
                      required
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="descripcion">Descripción</Label>
                  <Input
                    id="descripcion"
                    value={formData.descripcion}
                    onChange={(e) => setFormData({ ...formData, descripcion: e.target.value })}
                  />
                </div>

                <DialogFooter>
                  <Button type="submit">{editingHabitacion ? "Actualizar" : "Crear"}</Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {habitaciones.map((habitacion) => (
            <Card key={habitacion.id} className="overflow-hidden">
              <CardHeader className="pb-4">
                <div className="flex items-start justify-between">
                  <div className="flex items-center gap-3">
                    <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                      <Bed className="w-6 h-6 text-primary" />
                    </div>
                    <div>
                      <CardTitle className="text-xl">Hab. {habitacion.numero}</CardTitle>
                      <p className="text-sm text-muted-foreground">Piso {habitacion.piso}</p>
                    </div>
                  </div>
                  {getEstadoBadge(habitacion.estado)}
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-muted-foreground">Tipo:</span>
                    <span className="font-medium">{getTipoLabel(habitacion.tipo)}</span>
                  </div>
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-muted-foreground">Precio:</span>
                    <span className="font-semibold text-primary">${habitacion.precio.toLocaleString()}</span>
                  </div>
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-muted-foreground">Capacidad:</span>
                    <span className="flex items-center gap-1">
                      <Users className="w-4 h-4" />
                      {habitacion.capacidad}
                    </span>
                  </div>
                </div>

                {habitacion.descripcion && (
                  <p className="text-sm text-muted-foreground pt-2 border-t">{habitacion.descripcion}</p>
                )}

                <div className="flex gap-2 pt-2">
                  <Button
                    variant="outline"
                    size="sm"
                    className="flex-1 bg-transparent"
                    onClick={() => handleEdit(habitacion)}
                  >
                    <Edit className="w-4 h-4 mr-1" />
                    Editar
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => handleDelete(habitacion.id)}>
                    <Trash2 className="w-4 h-4" />
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </DashboardLayout>
  )
}
