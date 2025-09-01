<?php

namespace App\Controller;

use App\Entity\Track;
use App\Repository\TrackRepository;
use App\Service\MediaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/stream')]
class StreamController extends AbstractController
{
    public function __construct(
        private TrackRepository $trackRepository,
        private MediaService $mediaService
    ) {
    }

    #[Route('/track/{id}', methods: ['GET'])]
    public function streamTrack(int $id, Request $request): Response
    {
        $track = $this->trackRepository->find($id);
        
        if (!$track || !$track->getTrackFile()) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }

        $media = $track->getTrackFile();
        $filePath = $media->getFilePath();
        
        if (!file_exists($filePath)) {
            return $this->json(['error' => 'Track file not found'], Response::HTTP_NOT_FOUND);
        }

        $fileSize = $media->getFileSize();
        $mimeType = $media->getMimeType();
        
        // Validate that this is actually an audio file
        if (!$this->mediaService->isAudioFile($mimeType)) {
            return $this->json(['error' => 'File is not an audio file'], Response::HTTP_BAD_REQUEST);
        }
        
        // Ensure MIME type is accurate by detecting it from the file
        $detectedMimeType = $this->mediaService->detectMimeType($filePath);
        if ($detectedMimeType !== $mimeType && $this->mediaService->isAudioFile($detectedMimeType)) {
            $mimeType = $detectedMimeType;
        }
        
        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'public, max-age=3600',
            'Content-Disposition' => 'inline; filename="' . $media->getOriginalName() . '"'
        ];

        $rangeHeader = $request->headers->get('range');
        
        if ($rangeHeader) {
            return $this->handleRangeRequest($filePath, $fileSize, $mimeType, $rangeHeader, $media->getOriginalName());
        }

        return $this->streamFullFile($filePath, $headers);
    }

    #[Route('/track/{id}/info', methods: ['GET'])]
    public function trackInfo(int $id): Response
    {
        $track = $this->trackRepository->find($id);
        
        if (!$track || !$track->getTrackFile()) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }

        $media = $track->getTrackFile();
        $filePath = $media->getFilePath();
        
        if (!file_exists($filePath)) {
            return $this->json(['error' => 'Track file not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'trackId' => $track->getId(),
            'name' => $track->getName(),
            'description' => $track->getDescription(),
            'fileSize' => $media->getFileSize(),
            'mimeType' => $media->getMimeType(),
            'originalName' => $media->getOriginalName(),
            'supportsRangeRequests' => true,
            'streamUrl' => '/api/stream/track/' . $track->getId()
        ]);
    }

    private function handleRangeRequest(string $filePath, int $fileSize, string $mimeType, string $rangeHeader, string $filename): Response
    {
        if (!preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches)) {
            return new Response('Invalid range', Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE);
        }

        $start = (int) $matches[1];
        $end = $matches[2] !== '' ? (int) $matches[2] : $fileSize - 1;
        
        if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
            return new Response('Range not satisfiable', Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, [
                'Content-Range' => "bytes */$fileSize"
            ]);
        }

        $contentLength = $end - $start + 1;
        
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', (string) $contentLength);
        $response->headers->set('Content-Range', "bytes $start-$end/$fileSize");
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        $response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        $response->setStatusCode(Response::HTTP_PARTIAL_CONTENT);
        
        $response->setCallback(function() use ($filePath, $start, $contentLength) {
            $handle = fopen($filePath, 'rb');
            if ($handle === false) {
                return;
            }
            
            fseek($handle, $start);
            $remainingBytes = $contentLength;
            $bufferSize = 8192; // 8KB chunks
            
            while ($remainingBytes > 0 && !feof($handle)) {
                $bytesToRead = min($bufferSize, $remainingBytes);
                $chunk = fread($handle, $bytesToRead);
                
                if ($chunk === false) {
                    break;
                }
                
                echo $chunk;
                flush();
                $remainingBytes -= strlen($chunk);
            }
            
            fclose($handle);
        });

        return $response;
    }

    private function streamFullFile(string $filePath, array $headers): Response
    {
        $response = new StreamedResponse();
        
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }
        
        $response->setCallback(function() use ($filePath) {
            $handle = fopen($filePath, 'rb');
            if ($handle === false) {
                return;
            }
            
            $bufferSize = 8192; // 8KB chunks
            while (!feof($handle)) {
                $chunk = fread($handle, $bufferSize);
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
                flush();
            }
            
            fclose($handle);
        });

        return $response;
    }
}