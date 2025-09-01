import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { Layout } from './Layout';
import { HomePage } from '@/pages/home';
import { UsersPage } from '@/pages/users';
import { ArtistsPage } from '@/pages/artists';
import { AlbumsPage } from '@/pages/albums';
import { TracksPage } from '@/pages/tracks';
import { PlaylistsPage } from '@/pages/playlists';

function App() {
  return (
    <Router>
      <Layout>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/users" element={<UsersPage />} />
          <Route path="/artists" element={<ArtistsPage />} />
          <Route path="/albums" element={<AlbumsPage />} />
          <Route path="/tracks" element={<TracksPage />} />
          <Route path="/playlists" element={<PlaylistsPage />} />
        </Routes>
      </Layout>
    </Router>
  );
}

export default App;