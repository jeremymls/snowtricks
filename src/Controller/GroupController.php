<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/add/group", name="app_add_group", methods={"GET", "POST"})
     */
    public function add(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $group = new Group();
        $group->setName($request->request->get('name'));
        $group->setDescription($request->request->get('description'));

        $error = $validator->validate($group);
        if (count($error) > 0) {
            $errors = [];
            foreach ($error as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'message' => 'Group not created',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }
        $this->em->persist($group);
        $this->em->flush();
        $this->em->refresh($group);
        return new JsonResponse([
            'success' => true,
            'message' => 'Group created',
            'group' => $group->getName(),
            'id' => $group->getId()

        ]);
    }
}
