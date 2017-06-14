<?php

namespace MirrorApiBundle\Controller\Modules;

use MirrorApiBundle\Controller\ControllerTrait;
use MirrorApiBundle\Entity\Time;
use MirrorApiBundle\Form\TimeType;
use MirrorApiBundle\Security\Authorization\Voter\OwnerVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class TimeController extends Controller
{

    use ControllerTrait;

    /**
     * @Rest\View(serializerGroups={"module"})
     * @Rest\Get("/user/{user_id}/time/{time_module_id}")
     * @return JsonResponse
     */
    public function getTimeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var $place User
         */
        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        $repository = $this->getDoctrine()->getRepository('MirrorApiBundle:Time');

        $timeModule = $repository->findOneBy([
            'id'    => $request->get('time_module_id'),
            'user'  => $user,
        ]);

        $this->denyAccessUnlessGranted(OwnerVoter::OWNER_OR_MIRROR, $timeModule);

        if (empty($timeModule)) {
            return new JsonResponse(['message' => 'Time module not found'], Response::HTTP_NOT_FOUND);
        }

        //TODO ajouter les href

        return $timeModule;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"module"})
     * @Rest\Post("/user/{user_id}/time")
     * @return JsonResponse
     */
    public function postTimeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        if ($user === null) {
            return $this->userNotFound();
        } else {
            $this->denyAccessUnlessGranted(OwnerVoter::OWNER, $user);

            $time = new Time();
            $time->setUser($user);

            $form = $this->createForm(TimeType::class, $time);

            $form->submit($request->request->all());

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($time);
                $em->flush();
                return $time;
            } else {
                return $form;
            }
        }
    }

    /**
     * @Rest\View(serializerGroups={"module"})
     * @Rest\Patch("/user/{user_id}/time/{time_module_id}")
     * @return JsonResponse
     */
    public function patchTimeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        $repository = $this->getDoctrine()->getRepository('MirrorApiBundle:Time');

        $timeModule = $repository->findOneBy([
            'id'    => $request->get('time_module_id'),
            'user'  => $user,
        ]);

        if (empty($timeModule)) {
            return $this->moduleNotFound();
        }

        $this->denyAccessUnlessGranted(OwnerVoter::OWNER, $timeModule);

        $form = $this->createForm(TimeType::class, $timeModule);

        $this->convertRequestSnakeCaseToCamelCase($request);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($timeModule);
            $em->flush();
            return $timeModule;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/user/{user_id}/time/{time_module_id}")
     */
    public function removeTimeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        $timeModule = $this->getDoctrine()->getRepository('MirrorApiBundle:Time')->findOneBy([
            'id'    => $request->get('time_module_id'),
            'user'  => $user,
        ]);

        if (empty($timeModule)) {
            return $this->moduleNotFound();
        }

        $this->denyAccessUnlessGranted(OwnerVoter::OWNER, $timeModule);

        $em->remove($timeModule);
        $em->flush();
    }
}
