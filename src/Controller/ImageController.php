<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ImageController extends AbstractController
{
    private EntityManagerInterface $em;
    private ImageRepository $imageRepo;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $em, ImageRepository $imageRepo, SluggerInterface $slugger)
    {
        $this->em = $em;
        $this->imageRepo = $imageRepo;
        $this->slugger = $slugger;
    }

    #[Route('/image/add', name: 'app_add', methods: ['POST'])]
    public function imageAdd(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $file = $request->files->get('image');
        $alt = trim($request->request->get('alt', ''));
        $title = trim($request->request->get('title', ''));

        if (!$file || !$alt || !$title) {
            return $this->json(['error' => 'Image file, alt and title are required.'], 400);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        if (!in_array($file->guessExtension(), ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $this->json(['error' => 'Invalid image format. Allowed: jpg, jpeg, png, webp.'], 400);
        }

        try {
            $file->move($this->getParameter('images_directory'), $newFilename);
        } catch (FileException $e) {
            return $this->json(['error' => 'Failed to upload image.'], 500);
        }

        $image = new Image();
        $image->setUrl('/uploads/images/' . $newFilename);
        $image->setAlt(htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'));
        $image->setTitle(htmlspecialchars($title, ENT_QUOTES, 'UTF-8'));

        $this->em->persist($image);
        $this->em->flush();

        return $this->json(['success' => true, 'imageId' => $image->getId()], 201);
    }

    #[Route('/image/update/{id}', name: 'app_update', methods: ['POST'])]
    public function imageUpdate(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $image = $this->imageRepo->find($id);
        if (!$image) {
            return $this->json(['error' => 'Image not found.'], 404);
        }

        $alt = trim($request->request->get('alt', $image->getAlt()));
        $title = trim($request->request->get('title', $image->getTitle()));

        $file = $request->files->get('image');

        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            if (!in_array($file->guessExtension(), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                return $this->json(['error' => 'Invalid image format. Allowed: jpg, jpeg, png, webp.'], 400);
            }

            try {
                $file->move($this->getParameter('images_directory'), $newFilename);
            } catch (FileException $e) {
                return $this->json(['error' => 'Failed to upload image.'], 500);
            }

            // Delete old image file
            $oldFile = $this->getParameter('kernel.project_dir') . '/public' . $image->getUrl();
            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }

            $image->setUrl('/uploads/images/' . $newFilename);
        }

        $image->setAlt(htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'));
        $image->setTitle(htmlspecialchars($title, ENT_QUOTES, 'UTF-8'));

        $this->em->flush();

        return $this->json(['success' => true], 200);
    }

    #[Route('/image/delete/{id}', name: 'app_delete', methods: ['DELETE'])]
    public function imageDelete(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $image = $this->imageRepo->find($id);
        if (!$image) {
            return $this->json(['error' => 'Image not found.'], 404);
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $image->getUrl();
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $this->em->remove($image);
        $this->em->flush();

        return $this->json(['success' => true], 200);
    }
}
