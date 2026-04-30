"use client"

import { type ReactNode, useState } from "react"
import { Sidebar } from "./sidebar"
import { Header } from "./header"
import { ContextualHelp } from "./contextual-help"

interface DashboardLayoutProps {
  children: ReactNode
}

export function DashboardLayout({ children }: DashboardLayoutProps) {
  const [showHelp, setShowHelp] = useState(false)

  return (
    <div className="flex h-screen overflow-hidden bg-background">
      <Sidebar />

      <div className="flex-1 flex flex-col overflow-hidden">
        <Header onToggleHelp={() => setShowHelp(!showHelp)} />

        <main className="flex-1 overflow-y-auto p-6">{children}</main>
      </div>

      {showHelp && <ContextualHelp onClose={() => setShowHelp(false)} />}
    </div>
  )
}
