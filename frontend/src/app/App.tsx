import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { ThemeProvider } from '@/shared/contexts/theme-context';
import { Layout } from './Layout';
import { StartupFeedPage } from '@/pages/startup-feed';
import { UsersPage } from '@/pages/users';
import { ArtistsPage } from '@/pages/artists';
import { AlbumsPage } from '@/pages/albums';
import { TracksPage } from '@/pages/tracks';
import { PlaylistsPage } from '@/pages/playlists';
import { HomePage } from '@/pages/home';
import { ProfilePage } from '@/pages/profile';
import { LoginPage } from '@/pages/login';

function App() {
  return (
    <ThemeProvider defaultTheme="light">
      <Router>
        <Layout>
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/feed" element={<StartupFeedPage />} />
            <Route path="/users" element={<UsersPage />} />
            <Route path="/artists" element={<ArtistsPage />} />
            <Route path="/albums" element={<AlbumsPage />} />
            <Route path="/tracks" element={<TracksPage />} />
            <Route path="/playlists" element={<PlaylistsPage />} />
            <Route path="/profile" element={<ProfilePage />} />
            <Route path="/login" element={<LoginPage />} />
          </Routes>
        </Layout>
      </Router>
    </ThemeProvider>
  );
}

export default App;