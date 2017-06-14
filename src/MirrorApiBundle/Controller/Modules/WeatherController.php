<?php

namespace MirrorApiBundle\Controller\Modules;

use MirrorApiBundle\Controller\ControllerTrait;
use MirrorApiBundle\Entity\Weather;
use MirrorApiBundle\Form\WeatherType;
use MirrorApiBundle\Security\Authorization\Voter\OwnerVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class WeatherController extends Controller
{

    use ControllerTrait;

    /**
     * @Rest\View(serializerGroups={"module"})
     * @Rest\Get("/user/{user_id}/weather/{weather_module_id}")
     * @return JsonResponse
     */
    public function getWeatherAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var $place User
         */
        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        $repository = $this->getDoctrine()->getRepository('MirrorApiBundle:Weather');

        $weatherModule = $repository->findOneBy([
            'id'    => $request->get('weather_module_id'),
            'user'  => $user,
        ]);

        $this->denyAccessUnlessGranted(OwnerVoter::OWNER_OR_MIRROR, $weatherModule);

        if (empty($weatherModule)) {
            return new JsonResponse(['message' => 'Weather module not found'], Response::HTTP_NOT_FOUND);
        }

        //TODO ajouter les href

        return $weatherModule;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"module"})
     * @Rest\Post("/user/{user_id}/weather")
     * @return JsonResponse
     */
    public function postWeatherAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        if ($user === null) {
            return $this->userNotFound();
        } else {
            $this->denyAccessUnlessGranted(OwnerVoter::OWNER, $user);

            $weather = new Weather();
            $weather->setUser($user);

            $form = $this->createForm(WeatherType::class, $weather);

            $form->submit($request->request->all());

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($weather);
                $em->flush();
                return $weather;
            } else {
                return $form;
            }
        }
    }

    /**
     * @Rest\View(serializerGroups={"module"})
     * @Rest\Patch("/user/{user_id}/weather/{weather_module_id}")
     * @return JsonResponse
     */
    public function patchWeatherAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        $repository = $this->getDoctrine()->getRepository('MirrorApiBundle:Weather');

        $weatherModule = $repository->findOneBy([
            'id'    => $request->get('weather_module_id'),
            'user'  => $user,
        ]);

        if (empty($weatherModule)) {
            return $this->moduleNotFound();
        }

        $this->denyAccessUnlessGranted(OwnerVoter::OWNER, $weatherModule);

        $form = $this->createForm(WeatherType::class, $weatherModule);

        $this->convertRequestSnakeCaseToCamelCase($request);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($weatherModule);
            $em->flush();
            return $weatherModule;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/user/{user_id}/weather/{weather_module_id}")
     */
    public function removeWeatherAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        $weatherModule = $this->getDoctrine()->getRepository('MirrorApiBundle:Weather')->findOneBy([
            'id'    => $request->get('weather_module_id'),
            'user'  => $user,
        ]);

        if (empty($weatherModule)) {
            return $this->moduleNotFound();
        }

        $this->denyAccessUnlessGranted(OwnerVoter::OWNER, $weatherModule);

        $em->remove($weatherModule);
        $em->flush();
    }
}
