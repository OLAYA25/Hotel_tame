"use client"

import { X, BookOpen, Video, FileText } from "lucide-react"
import { Button } from "./ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "./ui/tabs"
import { useHelp } from "./help-provider"

interface ContextualHelpProps {
  onClose: () => void
}

export function ContextualHelp({ onClose }: ContextualHelpProps) {
  const { getUserRole } = useHelp()
  const userRole = getUserRole()

  const helpContent = {
    admin: {
      tutorials: [
        "Gestión avanzada de habitaciones",
        "Configuración de precios dinámicos",
        "Reportes y análisis de ocupación",
        "Administración de usuarios y permisos",
      ],
      quickGuides: [
        "Cómo crear una nueva reserva",
        "Gestionar check-in/check-out",
        "Configurar tarifas especiales",
        "Exportar informes mensuales",
      ],
    },
    staff: {
      tutorials: ["Proceso de check-in", "Gestión de reservas diarias", "Atención al cliente"],
      quickGuides: ["Registrar un nuevo cliente", "Buscar disponibilidad", "Modificar una reserva"],
    },
    viewer: {
      tutorials: ["Navegación del dashboard", "Consultar reservas"],
      quickGuides: ["Ver estadísticas", "Buscar información"],
    },
  }

  const content = helpContent[userRole]

  return (
    <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <Card className="w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col">
        <CardHeader className="flex flex-row items-center justify-between border-b">
          <CardTitle>Ayuda Contextual - Rol: {userRole.toUpperCase()}</CardTitle>
          <Button variant="ghost" size="icon" onClick={onClose}>
            <X className="w-5 h-5" />
          </Button>
        </CardHeader>

        <CardContent className="flex-1 overflow-y-auto p-6">
          <Tabs defaultValue="tutorials" className="w-full">
            <TabsList className="grid w-full grid-cols-3">
              <TabsTrigger value="tutorials">Tutoriales</TabsTrigger>
              <TabsTrigger value="guides">Guías Rápidas</TabsTrigger>
              <TabsTrigger value="docs">Documentación</TabsTrigger>
            </TabsList>

            <TabsContent value="tutorials" className="space-y-4 mt-4">
              <div className="space-y-3">
                {content.tutorials.map((tutorial, index) => (
                  <div
                    key={index}
                    className="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted/50 cursor-pointer transition-colors"
                  >
                    <Video className="w-5 h-5 text-primary" />
                    <span className="flex-1">{tutorial}</span>
                  </div>
                ))}
              </div>
            </TabsContent>

            <TabsContent value="guides" className="space-y-4 mt-4">
              <div className="space-y-3">
                {content.quickGuides.map((guide, index) => (
                  <div
                    key={index}
                    className="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted/50 cursor-pointer transition-colors"
                  >
                    <BookOpen className="w-5 h-5 text-accent" />
                    <span className="flex-1">{guide}</span>
                  </div>
                ))}
              </div>
            </TabsContent>

            <TabsContent value="docs" className="space-y-4 mt-4">
              <div className="space-y-3">
                <div className="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted/50 cursor-pointer transition-colors">
                  <FileText className="w-5 h-5 text-green-500" />
                  <span className="flex-1">Manual de Usuario Completo</span>
                </div>
                <div className="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted/50 cursor-pointer transition-colors">
                  <FileText className="w-5 h-5 text-green-500" />
                  <span className="flex-1">Guía de Instalación</span>
                </div>
                <div className="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted/50 cursor-pointer transition-colors">
                  <FileText className="w-5 h-5 text-green-500" />
                  <span className="flex-1">Documentación Técnica</span>
                </div>
                <div className="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted/50 cursor-pointer transition-colors">
                  <FileText className="w-5 h-5 text-green-500" />
                  <span className="flex-1">API Reference</span>
                </div>
              </div>
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  )
}
