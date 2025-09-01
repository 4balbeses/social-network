import { useState } from 'react'
import { Header } from '../../widgets/header'
import { AlbumList, CreateAlbumForm } from '../../features/album-management'
import { Button } from '../../shared/ui/Button'

type ViewMode = 'list' | 'create'

export function AlbumManagementPage() {
  const [viewMode, setViewMode] = useState<ViewMode>('list')

  const handleCreate = () => setViewMode('create')

  const handleSuccess = () => setViewMode('list')

  const handleCancel = () => setViewMode('list')

  const renderContent = () => {
    switch (viewMode) {
      case 'create':
        return (
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-4">Create New Album</h2>
            <CreateAlbumForm onSuccess={handleSuccess} onCancel={handleCancel} />
          </div>
        )
      default:
        return (
          <div>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-semibold text-gray-900">Album Management</h2>
              <Button onClick={handleCreate}>
                Add New Album
              </Button>
            </div>
            <AlbumList />
          </div>
        )
    }
  }

  return (
    <div>
      <Header />
      <main className="max-w-6xl mx-auto px-4 py-8">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-gray-900">Album Management</h1>
          <p className="text-gray-600 mt-2">Manage music albums</p>
        </div>
        
        {renderContent()}
      </main>
    </div>
  )
}