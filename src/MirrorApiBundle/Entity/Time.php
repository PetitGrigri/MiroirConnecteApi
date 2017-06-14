<?php

namespace MirrorApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\AccessorOrder;

/**
 * Time
 *
 * @ORM\Entity
 */
class Time extends Module
{
    /**
     * @var string
     *
     * http://php.net/manual/fr/timezones.php
     *
     * @ORM\Column(name="time_zone", type="string", length=100)
     */
    private $timeZone;


    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @param string $timeZone
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
    }


}

