"use client"

import { createContext, useContext, type ReactNode } from "react"

interface HelpContextType {
  showHelp: (topic: string) => void
  getUserRole: () => "admin" | "staff" | "viewer"
}

const HelpContext = createContext<HelpContextType | undefined>(undefined)

export function HelpProvider({ children }: { children: ReactNode }) {
  const showHelp = (topic: string) => {
    console.log("[v0] Mostrando ayuda para:", topic)
    // Aquí se implementaría la lógica para mostrar ayuda específica
  }

  const getUserRole = () => {
    // En producción, esto vendría del sistema de autenticación
    return "admin" as const
  }

  return <HelpContext.Provider value={{ showHelp, getUserRole }}>{children}</HelpContext.Provider>
}

export function useHelp() {
  const context = useContext(HelpContext)
  if (!context) {
    throw new Error("useHelp debe usarse dentro de HelpProvider")
  }
  return context
}
