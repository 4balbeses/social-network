import { GenreManagement } from '../../features/genre-management'

export function GenreManagementPage() {
  return (
    <div>
      <main className="max-w-6xl mx-auto px-4 py-8">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-card-foreground">Genre Management</h1>
          <p className="text-muted-foreground mt-2">Manage music genres</p>
        </div>
        <GenreManagement />
      </main>
    </div>
  )
}