<?php

namespace MirrorApiBundle\Controller;


use MirrorApiBundle\Entity\User;
use MirrorApiBundle\Form\UserType;
use MirrorApiBundle\Security\Authorization\Voter\OwnerVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class UserController extends Controller
{
    use ControllerTrait;

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/user/{user_id}")
     * @return JsonResponse
     */
    public function getUserAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('MirrorApiBundle:User');

        $user = $repository->find($request->get("user_id"));

        $this->denyAccessUnlessGranted(OwnerVoter::OWNER_OR_MIRROR, $user);

        if (empty($user)) {
            $this->userNotFound();
        }

        return $user;
    }

    /**
     * @Rest\View(serializerGroups={"users"})
     * @Rest\Get("/users")
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsersAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('MirrorApiBundle:User');

        $user = $repository->findAll();

        if (empty($user)) {
            $this->userNotFound();
        }

        return $user;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/user")
     * @return JsonResponse
     */
    public function postUserAction(Request $request)
    {
        $this->convertRequestSnakeCaseToCamelCase($request);
        $em = $this->getDoctrine()->getManager();

        $user = new User();
        $user->setRoles(serialize([User::ROLE_USER]));

        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            //recherche de la photo correspondante
            $photo = $this->getDoctrine()->getRepository('MirrorApiBundle:Photo')->findOneBy([
                "name"  => $user->getPhotoName()
            ]);


            if (empty($photo)) {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Photo not Found');
            }
            else {
                $user->setPhotoName($photo->getName().".".$photo->getExtension());
            }

            if (!empty($user->getPlainPassword())) {
                $this->encodePassword($user);
            }


            $em->persist($user);
            $em->flush();
            $em->remove($photo);
            $em->flush();

            return $user;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Patch("/user/{user_id}")
     * @return JsonResponse
     */
    public function patchUserAction(Request $request)
    {
        $this->convertRequestSnakeCaseToCamelCase($request);

        $repository = $this->getDoctrine()->getRepository('MirrorApiBundle:User');

        /* */
        $user = $repository->find($request->get("user_id"));

        $form = $this->createForm(UserType::class, $user);

        if (empty($user)) {
            $this->userNotFound();
        }

        $this->denyAccessUnlessGranted(OwnerVoter::OWNER, $user);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {

            $containExtension = strpos($user->getPhotoName(), ".");

            if (!$containExtension) {
                $photo = $this->getDoctrine()->getRepository('MirrorApiBundle:Photo')->findOneBy([
                    "name"  => $user->getPhotoName()
                ]);


                if (empty($photo)) {
                    throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Photo not Found');
                }
                else {
                    $user->setPhotoName($photo->getName().".".$photo->getExtension());
                }
            }


            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            if (!$containExtension){
                $em->remove($photo);
                $em->flush();
            }


            $em->flush();
            return $user;
        } else {
            return $form;
        }
        return $user;
    }


    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/user/{user_id}")
     * @param Request $request
     * @return JsonResponse
     */
    public function removeUserAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        if (empty($user)) {
            return $this->userNotFound();
        }

        $this->denyAccessUnlessGranted(OwnerVoter::OWNER, $user);

        $em->remove($user);
        $em->flush();
    }


    private function encodePassword($user)
    {
        $encoder = $this->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($encoded);
    }

}
