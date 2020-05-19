<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiPostController extends AbstractController
{
    /**
     * @Route("/api/post", name="api_post_index"    ) //cette route ne fonctionnera seulement si il y a une méthode GET
     */
    public function index(PostRepository $postRepository){


            //nous pouvons encore factoriser en passant le résultat de $postRepository->findAll(); à la place de $posts
        //            $posts = $postRepository->findAll();
            
        //$postsNormalises = $normalizer->normalize($posts, null, ['groups' => 'post:read']);

    
            //transforme le tableau d'objet post mais ne vois pas les donnée car celles-ci sont toutes privé
        //    $json = json_encode($postsNormalises);
            // on va transformer nos post normaliser en une phrase qui sera du json
            //on cree une veritable response

        //$json = $serializer->serialize($posts, 'json', ['groups' => 'post:read']);  


            /*$response = new Response($json, 200, [  //$json correspond au contenu que nous souhaitons envoyer //2eme le statue de la response  "200" // Puis on nous demande si il y a des en tete que l'on souhaite passer : en tete "Content-type" => sa valeur "application/json" 
                //le content type s'est d'expliqué au client (nav ou postman) que la reponse qu'on ait en train de lui envoyer contient du json et non du txt / html 
                "Content-Type" => "aplication/json"
            ]);*/

            //on utilise la classe JsonResponse() class enfant de response
            //$response = new JsonResponse($json, 200, [], true); //true car le json est déjà le json lui-même il n'a pas besoin de faire de transf
                
            //json hérite de notre abstractController on lui passe nos posts, le status que je veux, les entete mais jen ai pas besoin, puis les options de contexte
            return $this->json($postRepository->findAll(), 200, [], ['groups' => 'post:read']);

                //au lieu de faire un return je veux retourner le resultat de $this->json
            //return $response;
    }


    /**
     * @Route("/api/post", name="api_post_store", methods={"POST"}) //cette route ne fonctionnera seulement si il y a une méthode POST
     */
    public function store(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator) {
        $jsonRecu = $request->getContent();
        

        try {
        $post = $serializer->deserialize($jsonRecu, Post::class, 'json'); //on lui dit dans quel format le deserialiser "Post", on lui dit de quel format on pars "json"

        $post->setCreatedAt(new \DateTime());

        $errors = $validator->validate($post);

        if(count($errors) > 0){
            return $this->json($errors, 400);

        }

        $em->persist($post);
        $em->flush();

        return $this->json($post, 201, [], ['groups' => 'post:read']);

        } catch(NotEncodableValueException $e) {
            //je suis en train de te dire que je t'envois ce tableau là et je te lenvois en reponse avec le status 400
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);

        }

        

    }
}
