import { useState } from 'react'
import { Header } from '../../widgets/header'
import { TrackList, CreateTrackForm, EditTrackForm } from '../../features/track-management'
import { Button } from '../../shared/ui/Button'

type ViewMode = 'list' | 'create' | 'edit'

export function TrackManagementPage() {
  const [viewMode, setViewMode] = useState<ViewMode>('list')
  const [selectedTrackId, setSelectedTrackId] = useState<number | null>(null)

  const handleCreate = () => {
    setViewMode('create')
    setSelectedTrackId(null)
  }

  const handleEdit = (trackId: number) => {
    setSelectedTrackId(trackId)
    setViewMode('edit')
  }

  const handleSuccess = () => {
    setViewMode('list')
    setSelectedTrackId(null)
  }

  const handleCancel = () => {
    setViewMode('list')
    setSelectedTrackId(null)
  }

  const renderContent = () => {
    switch (viewMode) {
      case 'create':
        return (
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-4">Create New Track</h2>
            <CreateTrackForm onSuccess={handleSuccess} onCancel={handleCancel} />
          </div>
        )
      case 'edit':
        return selectedTrackId ? (
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-4">Edit Track</h2>
            <EditTrackForm 
              trackId={selectedTrackId} 
              onSuccess={handleSuccess} 
              onCancel={handleCancel} 
            />
          </div>
        ) : null
      default:
        return (
          <div>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-semibold text-gray-900">Track Management</h2>
              <Button onClick={handleCreate}>
                Add New Track
              </Button>
            </div>
            <TrackList onEdit={handleEdit} />
          </div>
        )
    }
  }

  return (
    <div>
      <Header />
      <main className="max-w-6xl mx-auto px-4 py-8">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-gray-900">Track Management</h1>
          <p className="text-gray-600 mt-2">Manage your music tracks</p>
        </div>
        
        {renderContent()}
      </main>
    </div>
  )
}