<?php

namespace IPS\SymfonyProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 *
 * @ORM\Table(name="task")
 * @ORM\Entity(repositoryClass="IPS\SymfonyProjectBundle\Repository\TaskRepository")
 */
class Task
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
     * @ORM\Column(name="NAME", type="string", length=255)
     */
    private $nAME;

    /**
     * @var string
     *
     * @ORM\Column(name="IMPORTANCE", type="string", length=255)
     */
    private $iMPORTANCE;

    /**
     * @var string
     *
     * @ORM\Column(name="COMMENT", type="text", nullable=true)
     */
    private $cOMMENT;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ADDDATE", type="datetime")
     */
    private $aDDDATE;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ENDDATE", type="datetime", nullable=true)
     */
    private $eNDDATE;

    /**
     * @var int
     *
     * @ORM\Column(name="STATUT", type="integer", length=11)
     */
    private $sTATUT;

    /**
     * @var string
     *
     * @ORM\Column(name="INCHARGE", type="string", length=255)
     */
    private $iNCHARGE;

    /**
     * @var string
     *
     * @ORM\Column(name="ADDER", type="string", length=255)
     */
    private $aDDER;

    /**
     * @ORM\ManyToOne(targetEntity="IPS\SymfonyProjectBundle\Entity\Section")
     * @ORM\JoinColumn(nullable=false)
     */
    private $SECTION;

    public function setSECTION(Section $section)
    {
        $this->SECTION = $section;

        return $this;
    }

    public function getSECTION()
    {
        return $this->SECTION;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nAME
     *
     * @param string $nAME
     *
     * @return Task
     */
    public function setNAME($nAME)
    {
        $this->nAME = $nAME;

        return $this;
    }

    /**
     * Get nAME
     *
     * @return string
     */
    public function getNAME()
    {
        return $this->nAME;
    }

    /**
     * Set iMPORTANCE
     *
     * @param string $iMPORTANCE
     *
     * @return Task
     */
    public function setIMPORTANCE($iMPORTANCE)
    {
        $this->iMPORTANCE = $iMPORTANCE;

        return $this;
    }

    /**
     * Get iMPORTANCE
     *
     * @return string
     */
    public function getIMPORTANCE()
    {
        return $this->iMPORTANCE;
    }

    /**
     * Set cOMMENT
     *
     * @param string $cOMMENT
     *
     * @return Task
     */
    public function setCOMMENT($cOMMENT)
    {
        $this->cOMMENT = $cOMMENT;

        return $this;
    }

    /**
     * Get cOMMENT
     *
     * @return string
     */
    public function getCOMMENT()
    {
        return $this->cOMMENT;
    }

    /**
     * Set aDDDATE
     *
     * @param \DateTime $aDDDATE
     *
     * @return Task
     */
    public function setADDDATE($aDDDATE)
    {
        $this->aDDDATE = $aDDDATE;

        return $this;
    }

    /**
     * Get aDDDATE
     *
     * @return \DateTime
     */
    public function getADDDATE()
    {
        return $this->aDDDATE;
    }

    /**
     * Set eNDDATE
     *
     * @param \DateTime $eNDDATE
     *
     * @return Task
     */
    public function setENDDATE($eNDDATE)
    {
        $this->eNDDATE = $eNDDATE;

        return $this;
    }

    /**
     * Get eNDDATE
     *
     * @return \DateTime
     */
    public function getENDDATE()
    {
        return $this->eNDDATE;
    }

    /**
     * Set sTATUT
     *
     * @param int $sTATUT
     *
     * @return Task
     */
    public function setSTATUT($sTATUT)
    {
        $this->sTATUT = $sTATUT;

        return $this;
    }

    /**
     * Get sTATUT
     *
     * @return int
     */
    public function getSTATUT()
    {
        return $this->sTATUT;
    }

    /**
     * Set iNCHARGE
     *
     * @param string $iNCHARGE
     *
     * @return Task
     */
    public function setINCHARGE($iNCHARGE)
    {
        $this->iNCHARGE = $iNCHARGE;

        return $this;
    }

    /**
     * Get iNCHARGE
     *
     * @return string
     */
    public function getINCHARGE()
    {
        return $this->iNCHARGE;
    }

    /**
     * Set iNCHARGE
     *
     * @param string $iNCHARGE
     *
     * @return Task
     */
    public function setADDER($aDDER)
    {
        $this->aDDER = $aDDER;

        return $this;
    }

    /**
     * Get iNCHARGE
     *
     * @return string
     */
    public function getADDER()
    {
        return $this->aDDER;
    }

    public function __construct(){

    }

    function init($name,$importance,$incharge,$comment,$section,$adder){
        $this->setNAME($name);
        $this->setIMPORTANCE($importance);
        $this->setADDDATE(new \Datetime());
        $this->setINCHARGE($incharge);
        $this->setADDER($adder);
        $this->setSECTION($section);
        $this->setCOMMENT($comment);
        $this->setSTATUT(0);
    }
}

