<?php

namespace MirrorApiBundle\Controller\Modules;

use MirrorApiBundle\Controller\ControllerTrait;
use MirrorApiBundle\Entity\Weather;
use MirrorApiBundle\Form\WeatherType;
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

        $WeatherModule = $repository->findOneBy([
            'id'    => $request->get('weather_module_id'),
            'user'  => $user,
        ]);

        if (empty($WeatherModule)) {
            return new JsonResponse(['message' => 'Weather module not found'], Response::HTTP_NOT_FOUND);
        }

        //TODO ajouter les href

        return $WeatherModule;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"module"})
     * @Rest\Post("/user/{user_id}/weather")
     * @return JsonResponse
     */
    public function postWeatherAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var $place User
         */
        $user = $em->getRepository('MirrorApiBundle:User')->find($request->get('user_id'));

        if ($user === null) {
            return $this->userNotFound();
        } else {
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
        $repository = $this->getDoctrine()->getRepository('MirrorApiBundle:Weather');

        $weather = $repository->find($request->get("weather_module_id"));

        $form = $this->createForm(WeatherType::class, $weather);

        if (empty($weather)) {
            return $this->moduleNotFound();
        } else if ($request->get("user_id") != $weather->getUser()->getId()) {
            return $this->wrongOwner();
        }

        $this->convertRequestSnakeCaseToCamelCase($request);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($weather);
            $em->flush();
            return $weather;
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

        /* @var $place Place */

        $em->remove($weatherModule);
        $em->flush();
    }

}
