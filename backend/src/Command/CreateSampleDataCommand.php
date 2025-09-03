<?php

namespace App\Command;

use App\Entity\Artist;
use App\Entity\Genre;
use App\Entity\Album;
use App\Entity\Track;
use App\Entity\User;
use App\Entity\Playlist;
use App\Entity\Tag;
use App\Entity\Media;
use App\Repository\ArtistRepository;
use App\Repository\GenreRepository;
use App\Repository\AlbumRepository;
use App\Repository\TrackRepository;
use App\Repository\UserRepository;
use App\Repository\PlaylistRepository;
use App\Repository\TagRepository;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-sample-data',
    description: 'Creates sample data for all main entities'
)]
class CreateSampleDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ArtistRepository $artistRepository,
        private GenreRepository $genreRepository,
        private AlbumRepository $albumRepository,
        private TrackRepository $trackRepository,
        private UserRepository $userRepository,
        private PlaylistRepository $playlistRepository,
        private TagRepository $tagRepository,
        private MediaRepository $mediaRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating Sample Data for Social Network');

        // Create sample users
        $io->section('Creating Users');
        $users = [];
        $sampleUsers = [
            ['username' => 'alice_music', 'fullName' => 'Alice Cooper', 'password' => 'password123'],
            ['username' => 'bob_beats', 'fullName' => 'Bob Dylan', 'password' => 'password123'],
            ['username' => 'charlie_tunes', 'fullName' => 'Charlie Parker', 'password' => 'password123'],
        ];

        foreach ($sampleUsers as $userData) {
            $existingUser = $this->userRepository->findOneBy(['username' => $userData['username']]);
            if (!$existingUser) {
                $user = new User();
                $user->setUsername($userData['username'])
                    ->setFullName($userData['fullName'])
                    ->setPassword($this->passwordHasher->hashPassword($user, $userData['password']));
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $users[] = $user;
                $io->success(sprintf('Created user: %s', $user->getFullName()));
            } else {
                $users[] = $existingUser;
                $io->info(sprintf('User already exists: %s', $existingUser->getFullName()));
            }
        }

        // Create sample genres
        $io->section('Creating Genres');
        $genres = [];
        $sampleGenres = [
            ['name' => 'Rock', 'description' => 'Rock music genre with electric guitars and drums'],
            ['name' => 'Jazz', 'description' => 'Jazz music featuring improvisation and swing'],
            ['name' => 'Pop', 'description' => 'Popular music with catchy melodies'],
            ['name' => 'Electronic', 'description' => 'Electronic dance music and synthesizers'],
        ];

        foreach ($sampleGenres as $genreData) {
            $existingGenre = $this->genreRepository->findOneBy(['name' => $genreData['name']]);
            if (!$existingGenre) {
                $genre = new Genre();
                $genre->setName($genreData['name'])
                    ->setDescription($genreData['description']);
                
                $this->entityManager->persist($genre);
                $this->entityManager->flush();
                $genres[] = $genre;
                $io->success(sprintf('Created genre: %s', $genre->getName()));
            } else {
                $genres[] = $existingGenre;
                $io->info(sprintf('Genre already exists: %s', $existingGenre->getName()));
            }
        }

        // Create sample artists
        $io->section('Creating Artists');
        $artists = [];
        $sampleArtists = [
            ['fullName' => 'The Beatles', 'description' => 'Legendary British rock band'],
            ['fullName' => 'Miles Davis', 'description' => 'Influential jazz trumpeter'],
            ['fullName' => 'Daft Punk', 'description' => 'French electronic music duo'],
            ['fullName' => 'Taylor Swift', 'description' => 'American singer-songwriter'],
        ];

        foreach ($sampleArtists as $artistData) {
            $existingArtist = $this->artistRepository->findOneBy(['fullName' => $artistData['fullName']]);
            if (!$existingArtist) {
                $artist = new Artist();
                $artist->setFullName($artistData['fullName'])
                    ->setDescription($artistData['description']);
                
                $this->entityManager->persist($artist);
                $this->entityManager->flush();
                $artists[] = $artist;
                $io->success(sprintf('Created artist: %s', $artist->getFullName()));
            } else {
                $artists[] = $existingArtist;
                $io->info(sprintf('Artist already exists: %s', $existingArtist->getFullName()));
            }
        }

        // Create sample albums
        $io->section('Creating Albums');
        $albums = [];
        $sampleAlbums = [
            ['name' => 'Abbey Road', 'description' => 'Classic Beatles album', 'artistIndex' => 0],
            ['name' => 'Kind of Blue', 'description' => 'Miles Davis masterpiece', 'artistIndex' => 1],
            ['name' => 'Random Access Memories', 'description' => 'Daft Punk album', 'artistIndex' => 2],
            ['name' => '1989', 'description' => 'Taylor Swift pop album', 'artistIndex' => 3],
        ];

        foreach ($sampleAlbums as $albumData) {
            $album = new Album();
            $album->setName($albumData['name'])
                ->setDescription($albumData['description']);
            
            $this->entityManager->persist($album);
            $this->entityManager->flush();
            $albums[] = $album;
            $io->success(sprintf('Created album: %s', $album->getName()));
        }

        // Create sample tracks
        $io->section('Creating Tracks');
        $sampleTracks = [
            ['name' => 'Come Together', 'genreIndex' => 0],
            ['name' => 'So What', 'genreIndex' => 1],
            ['name' => 'Get Lucky', 'genreIndex' => 3],
            ['name' => 'Shake It Off', 'genreIndex' => 2],
        ];

        foreach ($sampleTracks as $trackData) {
            $track = new Track();
            $track->setName($trackData['name'])
                ->setGenre($genres[$trackData['genreIndex']]);
            
            $this->entityManager->persist($track);
            $this->entityManager->flush();
            $io->success(sprintf('Created track: %s', $track->getName()));
        }

        // Create sample tags
        $io->section('Creating Tags');
        $sampleTags = ['classic', 'upbeat', 'chill', 'dance'];

        foreach ($sampleTags as $tagName) {
            $existingTag = $this->tagRepository->findOneBy(['name' => $tagName]);
            if (!$existingTag) {
                $tag = new Tag();
                $tag->setName($tagName)
                    ->setAuthor($users[rand(0, count($users) - 1)]);
                
                $this->entityManager->persist($tag);
                $this->entityManager->flush();
                $io->success(sprintf('Created tag: %s', $tag->getName()));
            } else {
                $io->info(sprintf('Tag already exists: %s', $existingTag->getName()));
            }
        }

        // Create sample playlists
        $io->section('Creating Playlists');
        $samplePlaylists = [
            ['name' => 'My Favorites', 'description' => 'Collection of favorite songs', 'userIndex' => 0],
            ['name' => 'Workout Mix', 'description' => 'High energy songs for workouts', 'userIndex' => 1],
            ['name' => 'Chill Vibes', 'description' => 'Relaxing music for studying', 'userIndex' => 2],
        ];

        foreach ($samplePlaylists as $playlistData) {
            $playlist = new Playlist();
            $playlist->setName($playlistData['name'])
                ->setDescription($playlistData['description'])
                ->setOwner($users[$playlistData['userIndex']])
                ->setIsPublic(true);
            
            $this->entityManager->persist($playlist);
            $this->entityManager->flush();
            $io->success(sprintf('Created playlist: %s', $playlist->getName()));
        }

        // Create sample media
        $io->section('Creating Media');
        $sampleMedia = [
            ['filename' => 'cover1.jpg', 'originalName' => 'album-cover-1.jpg', 'mimeType' => 'image/jpeg', 'size' => 1024000],
            ['filename' => 'track1.mp3', 'originalName' => 'song1.mp3', 'mimeType' => 'audio/mpeg', 'size' => 5242880],
            ['filename' => 'cover2.png', 'originalName' => 'album-cover-2.png', 'mimeType' => 'image/png', 'size' => 2048000],
        ];

        foreach ($sampleMedia as $mediaData) {
            $existingMedia = $this->mediaRepository->findOneBy(['fileName' => $mediaData['filename']]);
            if (!$existingMedia) {
                $media = new Media();
                $media->setFileName($mediaData['filename'])
                    ->setOriginalName($mediaData['originalName'])
                    ->setMimeType($mediaData['mimeType'])
                    ->setFileSize($mediaData['size'])
                    ->setFilePath('/uploads/' . $mediaData['filename']);
                
                $this->entityManager->persist($media);
                $this->entityManager->flush();
                $io->success(sprintf('Created media: %s', $media->getOriginalName()));
            } else {
                $io->info(sprintf('Media already exists: %s', $existingMedia->getOriginalName()));
            }
        }

        // Final verification
        $io->section('Verification Summary');
        $counts = [
            'Users' => count($this->userRepository->findAll()),
            'Artists' => count($this->artistRepository->findAll()),
            'Genres' => count($this->genreRepository->findAll()),
            'Albums' => count($this->albumRepository->findAll()),
            'Tracks' => count($this->trackRepository->findAll()),
            'Tags' => count($this->tagRepository->findAll()),
            'Playlists' => count($this->playlistRepository->findAll()),
            'Media' => count($this->mediaRepository->findAll()),
        ];

        foreach ($counts as $entity => $count) {
            $io->info(sprintf('%s: %d', $entity, $count));
        }

        $io->success('Sample data created successfully! All API endpoints should now return at least one entity.');
        
        return Command::SUCCESS;
    }
}