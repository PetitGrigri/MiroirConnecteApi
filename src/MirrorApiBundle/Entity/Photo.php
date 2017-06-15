<?php

namespace MirrorApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Image
 * @ORM\Entity(repositoryClass="PhotoRepository")
 * @ORM\Table(name="photo")
 */
class Photo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string")
     */
    private $extension;


    /**
     * @ORM\Column(name="upload_at", type="datetime")
     * @var \DateTime
     */
    protected $uploadAt;

    private $image;

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage( $image)
    {
        $this->image = $image;

        $dateTime = new \DateTime();

        $this->uploadAt     = $dateTime;
        $this->name         = $dateTime->format("Hmdyis");
        $this->extension    = $image->guessExtension();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return \DateTime
     */
    public function getUploadAt()
    {
        return $this->uploadAt;
    }

    /**
     * @param \DateTime $uploadAt
     */
    public function setUploadAt($uploadAt)
    {
        $this->uploadAt = $uploadAt;
    }

}