<?php

namespace MirrorApiBundle\Controller;

use MirrorApiBundle\Entity\Photo;
use MirrorApiBundle\Form\PhotoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;


class PhotoController extends Controller
{
    use ControllerTrait;

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Post("/photo")
     * @param Request $request
     * @return JsonResponse
     */
    public function postPhotoAction(Request $request)
    {
        $photo = new Photo();

        $form = $this->createForm(PhotoType::class, $photo);

        //todo ajouter un nom à l'image, puis la stocker en Bdds
        $form->submit($request->files->all(), false);

        $photo->getImage()->move(
            $this->getParameter('photo_directory'),
            $photo->getName().".".$photo->getExtension()
        );

        $em = $this->getDoctrine()->getManager();
        $em->persist($photo);
        $em->flush();

        if (!$form->isValid()) {
            return $form;
        }

        return ["name"  => $photo->getName()];
    }

}
