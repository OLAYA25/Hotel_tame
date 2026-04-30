import { DashboardLayout } from "@/components/dashboard-layout"
import { StatsCards } from "@/components/stats-cards"
import { RecentActivity } from "@/components/recent-activity"

export default function HomePage() {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Dashboard</h1>
          <p className="text-muted-foreground mt-2">Bienvenido al sistema de gestión hotelera</p>
        </div>

        <StatsCards />

        <RecentActivity />
      </div>
    </DashboardLayout>
  )
}
