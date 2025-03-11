<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostReaction;
use App\Repository\PostReactionRepository;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'post_list')]
    public function index(EntityManagerInterface $entityManager)
    {
        $posts = $entityManager->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/create', name: 'post_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserInterface $user): JsonResponse
    {
        $post = new Post();
        $post->setUser($user);
        $post->setDescription($request->request->get('description'));

        // Handle Image Upload
        $file = $request->files->get('postImage');
        if ($file) {
            $newFilename = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($this->getParameter('post_images_directory'), $newFilename);
                $post->setImage($newFilename);
            } catch (FileException $e) {
                return new JsonResponse(['success' => false, 'error' => 'Image upload failed']);
            }
        }

        $entityManager->persist($post);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'id' => $post->getId(),
            'description' => $post->getDescription(),
            'image' => $post->getImage(),
            'fullName' => $user->getFullName(),
            'profilePicture' => $user->getProfilePicture(),
            'createdAt' => $post->getCreatedAt()->format('d M Y'),
        ]);
    }

    // #[Route('/post/delete/{id}', name: 'post_delete', methods: ['POST'])]
    // public function delete(Post $post, EntityManagerInterface $entityManager)
    // {
    //     if ($post->getUser() !== $this->getUser()) {
    //         throw $this->createAccessDeniedException();
    //     }

    //     $entityManager->remove($post);
    //     $entityManager->flush();

    //     return new JsonResponse(['success' => true]);
    // }

    #[Route('/post/delete/{id}', name: 'post_delete', methods: ['POST'])]
public function delete(Post $post, EntityManagerInterface $entityManager, UserInterface $user): JsonResponse
{
    if ($post->getUser() !== $user) {
        return new JsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
    }

    $entityManager->remove($post);
    $entityManager->flush();

    return new JsonResponse(['success' => true]);
}


    #[Route('/like/{id}', name: 'post_like', methods: ['POST'])]
    public function like(Post $post, EntityManagerInterface $entityManager, UserInterface $user, PostReactionRepository $reactionRepo): JsonResponse
    {
        $existingReaction = $reactionRepo->findUserReaction($post, $user);
    
        if ($existingReaction) {
            if ($existingReaction->getType() === 'like') {
                $entityManager->remove($existingReaction); // Remove like if already liked
            } else {
                $existingReaction->setType('like'); // Switch dislike to like
            }
        } else {
            $reaction = new PostReaction();
            $reaction->setPost($post);
            $reaction->setUser($user);
            $reaction->setType('like');
            $entityManager->persist($reaction);
        }
    
        $entityManager->flush();
    
        return new JsonResponse([
            'likes' => $post->getLikesCount(),
            'dislikes' => $post->getDislikesCount()            
        ]);
    }
    

    #[Route('/dislike/{id}', name: 'post_dislike', methods: ['POST'])]
    public function dislike(Post $post, EntityManagerInterface $entityManager, UserInterface $user, PostReactionRepository $reactionRepo): JsonResponse
    {
        $existingReaction = $reactionRepo->findUserReaction($post, $user);

        if ($existingReaction) {
            if ($existingReaction->getType() === 'dislike') {
                $entityManager->remove($existingReaction);
            } else {
                $existingReaction->setType('dislike');
            }
        } else {
            $reaction = new PostReaction();
            $reaction->setPost($post);
            $reaction->setUser($user);
            $reaction->setType('dislike');
            $entityManager->persist($reaction);
        }

        $entityManager->flush();

        return new JsonResponse([
            'likes' => $post->getLikesCount(),
            'dislikes' => $post->getDislikesCount()

        ]);
    }
    
    
}
