<?php
namespace MirrorApiBundle\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ControllerTrait
{
    public function userNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User not found');
    }

    public function moduleNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Module not found');
    }

    public function wrongOwner()
    {
        return new JsonResponse(['message' => 'Wrong owner'], Response::HTTP_FORBIDDEN);
    }

    public function convertRequestSnakeCaseToCamelCase(&$request) {
        $tempo = [];
        foreach ($request->request as $key=>$value) {
            $tempo[$key] = lcfirst(str_replace(' ', '',ucwords(str_replace('_', ' ', $key))));
        }

        foreach ($tempo as $oldKey=>$newKey) {
            $tempo = $request->request->get($oldKey);
            $request->request->remove($oldKey);
            $request->request->set($newKey, $tempo);
        }
    }
}