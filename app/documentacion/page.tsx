import { DashboardLayout } from "@/components/dashboard-layout"
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card"
import { BookOpen, Download, FileText, Video, Code } from "lucide-react"
import { Button } from "@/components/ui/button"

export default function DocumentacionPage() {
  const documentos = [
    {
      title: "Manual de Usuario",
      description: "Guía completa para usuarios del sistema",
      icon: BookOpen,
      file: "manual-usuario.pdf",
      size: "2.5 MB",
    },
    {
      title: "Guía de Instalación",
      description: "Instrucciones paso a paso para instalar el sistema",
      icon: FileText,
      file: "guia-instalacion.pdf",
      size: "1.8 MB",
    },
    {
      title: "Documentación Técnica",
      description: "Arquitectura, APIs y detalles técnicos",
      icon: Code,
      file: "documentacion-tecnica.pdf",
      size: "3.2 MB",
    },
    {
      title: "Tutoriales en Video",
      description: "Serie de videos explicativos del sistema",
      icon: Video,
      file: "tutoriales",
      size: "Online",
    },
  ]

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold">Documentación del Sistema</h1>
          <p className="text-muted-foreground mt-2">Accede a toda la documentación, manuales y recursos de ayuda</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {documentos.map((doc) => {
            const Icon = doc.icon
            return (
              <Card key={doc.title}>
                <CardHeader>
                  <div className="flex items-start justify-between">
                    <div className="flex items-center gap-3">
                      <div className="p-2 rounded-lg bg-primary/10">
                        <Icon className="w-6 h-6 text-primary" />
                      </div>
                      <div>
                        <CardTitle className="text-lg">{doc.title}</CardTitle>
                        <CardDescription>{doc.description}</CardDescription>
                      </div>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-muted-foreground">{doc.size}</span>
                    <Button size="sm">
                      <Download className="w-4 h-4 mr-2" />
                      Descargar
                    </Button>
                  </div>
                </CardContent>
              </Card>
            )
          })}
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Ejemplos Prácticos</CardTitle>
            <CardDescription>Casos de uso comunes y ejemplos de implementación</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-3">
              {[
                "Ejemplo 1: Registro de nueva reserva con cliente nuevo",
                "Ejemplo 2: Check-in rápido con pre-registro",
                "Ejemplo 3: Gestión de pagos y facturación",
                "Ejemplo 4: Reportes personalizados de ocupación",
                "Ejemplo 5: Integración con sistemas externos",
              ].map((ejemplo, index) => (
                <div
                  key={index}
                  className="flex items-center justify-between p-4 rounded-lg border border-border hover:bg-muted/50 transition-colors"
                >
                  <span>{ejemplo}</span>
                  <Button variant="ghost" size="sm">
                    Ver ejemplo
                  </Button>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  )
}
