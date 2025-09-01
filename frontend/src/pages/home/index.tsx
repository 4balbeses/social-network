import { Card, CardContent, CardHeader, CardTitle } from '@/shared/ui';

export const HomePage = () => {
  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Welcome to Social Network</h1>
        <p className="mt-2 text-gray-600">
          Manage your music collection, artists, albums, tracks, and playlists.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Users</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600">
              Manage user accounts and profiles in your social network.
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Artists</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600">
              Create and manage artist profiles with descriptions and discography.
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Albums</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600">
              Organize music into albums linked to artists.
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Tracks</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600">
              Upload and manage individual tracks with media files and genres.
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Playlists</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600">
              Create curated playlists and share them with the community.
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Genres</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600">
              Categorize music with genre tags and organize your collection.
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};