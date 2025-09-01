import { Header } from '../../widgets/header'

export function HomePage() {
  return (
    <div>
      <Header />
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="text-center">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            Welcome to SocialMusic
          </h1>
          <p className="text-xl text-gray-600 mb-8">
            Discover, share, and enjoy music with your friends
          </p>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
            <div className="bg-white p-6 rounded-lg shadow-md">
              <h3 className="text-lg font-semibold mb-3">Discover Music</h3>
              <p className="text-gray-600">
                Find new tracks and artists that match your taste
              </p>
            </div>
            <div className="bg-white p-6 rounded-lg shadow-md">
              <h3 className="text-lg font-semibold mb-3">Create Playlists</h3>
              <p className="text-gray-600">
                Organize your favorite songs into custom playlists
              </p>
            </div>
            <div className="bg-white p-6 rounded-lg shadow-md">
              <h3 className="text-lg font-semibold mb-3">Share & Rate</h3>
              <p className="text-gray-600">
                Share music with friends and rate tracks and albums
              </p>
            </div>
          </div>
        </div>
      </main>
    </div>
  )
}