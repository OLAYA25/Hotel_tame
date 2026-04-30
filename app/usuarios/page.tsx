"use client"

import type React from "react"

import { DashboardLayout } from "@/components/dashboard-layout"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Plus, User, Edit, Trash2, Phone, Mail } from "lucide-react"
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

interface Usuario {
  id: number
  nombre: string
  email: string
  rol: string
  telefono: string
  activo: boolean
  createdAt: Date
}

export default function UsuariosPage() {
  const [usuarios, setUsuarios] = useState<Usuario[]>([])
  const [isOpen, setIsOpen] = useState(false)
  const [editingUsuario, setEditingUsuario] = useState<Usuario | null>(null)
  const [formData, setFormData] = useState({
    nombre: "",
    email: "",
    rol: "staff",
    telefono: "",
    activo: true,
  })

  useEffect(() => {
    loadUsuarios()
  }, [])

  const loadUsuarios = async () => {
    const res = await fetch("/api/usuarios")
    const data = await res.json()
    setUsuarios(data)
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (editingUsuario) {
      await fetch(`/api/usuarios/${editingUsuario.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      })
    } else {
      await fetch("/api/usuarios", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      })
    }

    setIsOpen(false)
    setEditingUsuario(null)
    setFormData({
      nombre: "",
      email: "",
      rol: "staff",
      telefono: "",
      activo: true,
    })
    loadUsuarios()
  }

  const handleEdit = (usuario: Usuario) => {
    setEditingUsuario(usuario)
    setFormData({
      nombre: usuario.nombre,
      email: usuario.email,
      rol: usuario.rol,
      telefono: usuario.telefono,
      activo: usuario.activo,
    })
    setIsOpen(true)
  }

  const handleDelete = async (id: number) => {
    if (confirm("¿Está seguro de eliminar este usuario?")) {
      await fetch(`/api/usuarios/${id}`, { method: "DELETE" })
      loadUsuarios()
    }
  }

  const getRolBadge = (rol: string) => {
    const variants: Record<string, { variant: "default" | "secondary" | "destructive"; label: string }> = {
      admin: { variant: "destructive", label: "Admin" },
      staff: { variant: "default", label: "Staff" },
      viewer: { variant: "secondary", label: "Viewer" },
    }
    const config = variants[rol] || variants.viewer
    return <Badge variant={config.variant}>{config.label}</Badge>
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Usuarios</h1>
            <p className="text-muted-foreground mt-2">Gestiona los usuarios del sistema</p>
          </div>
          <Dialog open={isOpen} onOpenChange={setIsOpen}>
            <DialogTrigger asChild>
              <Button
                onClick={() => {
                  setEditingUsuario(null)
                  setFormData({
                    nombre: "",
                    email: "",
                    rol: "staff",
                    telefono: "",
                    activo: true,
                  })
                }}
              >
                <Plus className="w-4 h-4 mr-2" />
                Nuevo Usuario
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>{editingUsuario ? "Editar" : "Nuevo"} Usuario</DialogTitle>
                <DialogDescription>Complete los datos del usuario</DialogDescription>
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
                  <Label htmlFor="rol">Rol</Label>
                  <Select value={formData.rol} onValueChange={(value) => setFormData({ ...formData, rol: value })}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="admin">Admin</SelectItem>
                      <SelectItem value="staff">Staff</SelectItem>
                      <SelectItem value="viewer">Viewer</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <DialogFooter>
                  <Button type="submit">{editingUsuario ? "Actualizar" : "Crear"}</Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {usuarios.map((usuario) => (
            <Card key={usuario.id}>
              <CardHeader>
                <div className="flex items-start justify-between">
                  <div className="flex items-center gap-3">
                    <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                      <User className="w-6 h-6 text-primary" />
                    </div>
                    <div>
                      <CardTitle className="text-lg">{usuario.nombre}</CardTitle>
                      {getRolBadge(usuario.rol)}
                    </div>
                  </div>
                </div>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <Mail className="w-4 h-4" />
                  <span className="truncate">{usuario.email}</span>
                </div>
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <Phone className="w-4 h-4" />
                  <span>{usuario.telefono}</span>
                </div>

                <div className="flex gap-2 pt-3 border-t">
                  <Button
                    variant="outline"
                    size="sm"
                    className="flex-1 bg-transparent"
                    onClick={() => handleEdit(usuario)}
                  >
                    <Edit className="w-4 h-4 mr-1" />
                    Editar
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => handleDelete(usuario.id)}>
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
