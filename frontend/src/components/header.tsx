"use client"

import { Bell, Search, HelpCircle } from "lucide-react"
import { Button } from "./ui/button"
import { Input } from "./ui/input"

interface HeaderProps {
  onToggleHelp: () => void
}

export function Header({ onToggleHelp }: HeaderProps) {
  return (
    <header className="h-16 border-b border-border bg-card px-6 flex items-center justify-between">
      <div className="flex items-center gap-4 flex-1 max-w-xl">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input type="search" placeholder="Buscar..." className="pl-9" />
        </div>
      </div>

      <div className="flex items-center gap-2">
        <Button variant="ghost" size="icon" onClick={onToggleHelp} title="Ayuda contextual">
          <HelpCircle className="w-5 h-5" />
        </Button>
        <Button variant="ghost" size="icon">
          <Bell className="w-5 h-5" />
        </Button>
        <div className="w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-sm font-medium">
          AD
        </div>
      </div>
    </header>
  )
}
