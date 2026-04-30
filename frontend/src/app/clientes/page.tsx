"use client"

import type React from "react"

import { DashboardLayout } from "@/components/dashboard-layout"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Plus, UserCircle, Edit, Trash2, Phone, Mail, MapPin, FileText } from "lucide-react"
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

interface Cliente {
  id: number
  nombre: string
  email: string
  telefono: string
  documento: string
  direccion: string
  createdAt: Date
}

export default function ClientesPage() {
  const [clientes, setClientes] = useState<Cliente[]>([])
  const [isOpen, setIsOpen] = useState(false)
  const [editingCliente, setEditingCliente] = useState<Cliente | null>(null)
  const [formData, setFormData] = useState({
    nombre: "",
    email: "",
    telefono: "",
    documento: "",
    direccion: "",
  })

  useEffect(() => {
    loadClientes()
  }, [])

  const loadClientes = async () => {
    const res = await fetch("/api/clientes")
    const data = await res.json()
    setClientes(data)
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (editingCliente) {
      await fetch(`/api/clientes/${editingCliente.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      })
    } else {
      await fetch("/api/clientes", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      })
    }

    setIsOpen(false)
    setEditingCliente(null)
    setFormData({
      nombre: "",
      email: "",
      telefono: "",
      documento: "",
      direccion: "",
    })
    loadClientes()
  }

  const handleEdit = (cliente: Cliente) => {
    setEditingCliente(cliente)
    setFormData({
      nombre: cliente.nombre,
      email: cliente.email,
      telefono: cliente.telefono,
      documento: cliente.documento,
      direccion: cliente.direccion,
    })
    setIsOpen(true)
  }

  const handleDelete = async (id: number) => {
    if (confirm("¿Está seguro de eliminar este cliente?")) {
      await fetch(`/api/clientes/${id}`, { method: "DELETE" })
      loadClientes()
    }
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Clientes</h1>
            <p className="text-muted-foreground mt-2">Gestiona los clientes del hotel</p>
          </div>
          <Dialog open={isOpen} onOpenChange={setIsOpen}>
            <DialogTrigger asChild>
              <Button
                onClick={() => {
                  setEditingCliente(null)
                  setFormData({
                    nombre: "",
                    email: "",
                    telefono: "",
                    documento: "",
                    direccion: "",
                  })
                }}
              >
                <Plus className="w-4 h-4 mr-2" />
                Nuevo Cliente
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>{editingCliente ? "Editar" : "Nuevo"} Cliente</DialogTitle>
                <DialogDescription>Complete los datos del cliente</DialogDescription>
              </DialogHeader>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="nombre">Nombre</Label>
                  <Input
                    id="nombre"
                    value={formData.nombre}
                    onChange={(e) => setFormData({ ...formData, nombre: e.target.value })}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    type="email"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    required
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="telefono">Teléfono</Label>
                    <Input
                      id="telefono"
                      value={formData.telefono}
                      onChange={(e) => setFormData({ ...formData, telefono: e.target.value })}
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="documento">Documento</Label>
                    <Input
                      id="documento"
                      value={formData.documento}
                      onChange={(e) => setFormData({ ...formData, documento: e.target.value })}
                      required
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="direccion">Dirección</Label>
                  <Input
                    id="direccion"
                    value={formData.direccion}
                    onChange={(e) => setFormData({ ...formData, direccion: e.target.value })}
                    required
                  />
                </div>

                <DialogFooter>
                  <Button type="submit">{editingCliente ? "Actualizar" : "Crear"}</Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {clientes.map((cliente) => (
            <Card key={cliente.id}>
              <CardHeader>
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <UserCircle className="w-6 h-6 text-primary" />
                  </div>
                  <CardTitle className="text-lg">{cliente.nombre}</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <Mail className="w-4 h-4" />
                  <span className="truncate">{cliente.email}</span>
                </div>
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <Phone className="w-4 h-4" />
                  <span>{cliente.telefono}</span>
                </div>
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <FileText className="w-4 h-4" />
                  <span>{cliente.documento}</span>
                </div>
                <div className="flex items-start gap-2 text-sm text-muted-foreground">
                  <MapPin className="w-4 h-4 mt-0.5" />
                  <span className="flex-1">{cliente.direccion}</span>
                </div>

                <div className="flex gap-2 pt-3 border-t">
                  <Button
                    variant="outline"
                    size="sm"
                    className="flex-1 bg-transparent"
                    onClick={() => handleEdit(cliente)}
                  >
                    <Edit className="w-4 h-4 mr-1" />
                    Editar
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => handleDelete(cliente.id)}>
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
