"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import { LayoutDashboard, Hotel, Users, UserCircle, Calendar, Settings, HelpCircle } from "lucide-react"
import { cn } from "@/lib/utils"

const navigation = [
  { name: "Dashboard", href: "/", icon: LayoutDashboard },
  { name: "Habitaciones", href: "/habitaciones", icon: Hotel },
  { name: "Usuarios", href: "/usuarios", icon: Users },
  { name: "Clientes", href: "/clientes", icon: UserCircle },
  { name: "Reservas", href: "/reservas", icon: Calendar },
]

export function Sidebar() {
  const pathname = usePathname()

  return (
    <aside className="w-64 bg-sidebar text-sidebar-foreground flex flex-col">
      <div className="p-6 flex items-center gap-3">
        <Hotel className="w-8 h-8 text-sidebar-foreground" />
        <h1 className="text-xl font-bold">Hotel Management</h1>
      </div>

      <nav className="flex-1 px-3 py-4 space-y-1">
        {navigation.map((item) => {
          const isActive = pathname === item.href
          const Icon = item.icon

          return (
            <Link
              key={item.name}
              href={item.href}
              className={cn(
                "flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors",
                isActive
                  ? "bg-sidebar-accent text-sidebar-accent-foreground"
                  : "text-sidebar-foreground/80 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground",
              )}
            >
              <Icon className="w-5 h-5" />
              {item.name}
            </Link>
          )
        })}
      </nav>

      <div className="p-3 border-t border-sidebar-border">
        <Link
          href="/documentacion"
          className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-sidebar-foreground/80 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground transition-colors"
        >
          <HelpCircle className="w-5 h-5" />
          Documentación
        </Link>
        <Link
          href="/configuracion"
          className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-sidebar-foreground/80 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground transition-colors"
        >
          <Settings className="w-5 h-5" />
          Configuración
        </Link>
      </div>
    </aside>
  )
}
