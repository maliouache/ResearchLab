<?php

namespace IPS\SymfonyProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Section
 *
 * @ORM\Table(name="section")
 * @ORM\Entity(repositoryClass="IPS\SymfonyProjectBundle\Repository\SectionRepository")
 */
class Section
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
     * @var int
     *
     * @ORM\Column(name="STATUT", type="integer", length=11)
     */
    private $sTATUT;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ENDDATE", type="datetime", nullable=true)
     */
    private $eNDDATE;

    /**
     * @ORM\ManyToOne(targetEntity="IPS\SymfonyProjectBundle\Entity\Project")
     * @ORM\JoinColumn(nullable=false)
     */
    private $PROJECT;

    public function setPROJECT(Project $project)
    {
        $this->PROJECT = $project;

        return $this;
    }

    public function getPROJECT()
    {
        return $this->PROJECT;
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
     * @return Section
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
     * @return Section
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
     * @return Section
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
     * @return Section
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
     * Set sTATUT
     *
     * @param int $sTATUT
     *
     * @return Section
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
     * Set eNDDATE
     *
     * @param \DateTime $eNDDATE
     *
     * @return Section
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

    public function __construct(){

    }

    function init($name,$importance,$comment,$project){
        $this->setNAME($name);
        $this->setIMPORTANCE($importance);
        $this->setADDDATE(new \Datetime());
        $this->setPROJECT($project);
        $this->setCOMMENT($comment);
        $this->setSTATUT(0);
    }

    // function update_statut(){

    // }
}

