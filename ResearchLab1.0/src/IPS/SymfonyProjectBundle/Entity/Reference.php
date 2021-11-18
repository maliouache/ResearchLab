<?php

namespace IPS\SymfonyProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Reference
 *
 * @ORM\Table(name="reference")
 * @ORM\Entity(repositoryClass="IPS\SymfonyProjectBundle\Repository\ReferenceRepository")
 */
class Reference
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
     * @ORM\Column(name="URL", type="string", length=255)
     */
    private $uRL;

    /**
     * @var string
     *
     * @ORM\Column(name="TYPE", type="string", length=255)
     */
    private $tYPE;

    /**
     * @ORM\ManyToOne(targetEntity="IPS\SymfonyProjectBundle\Entity\Task")
     * @ORM\JoinColumn(nullable=false)
     */
    private $TASK;

    public function setTASK(Task $task){
        $this->TASK = $task;
        return $this;
    }

    public function getTASK()
    {
        return $this->TASK;
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
     * Set uRL
     *
     * @param string $uRL
     *
     * @return Reference
     */
    public function setURL($uRL)
    {
        $this->uRL = $uRL;

        return $this;
    }

    /**
     * Get uRL
     *
     * @return string
     */
    public function getURL()
    {
        return $this->uRL;
    }

    /**
     * Set tYPE
     *
     * @param string $tYPE
     *
     * @return Reference
     */
    public function setTYPE($tYPE)
    {
        $this->tYPE = $tYPE;

        return $this;
    }

    /**
     * Get tYPE
     *
     * @return string
     */
    public function getTYPE()
    {
        return $this->tYPE;
    }
    //partie qui gere les fichiers, sans annotation pour s'assurer de ne pas creer la colonne dans la base de données
    private $file;
  
    public function getFile()
    {
      return $this->file;
    }
  
    public function setFile(UploadedFile $file = null)
    {
      $this->file = $file;
    }

    public function __construct(){

    }

    public function upload($proj,$sect,$task)
    {
    // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
    if (null === $this->file) {
        
      return;
    }
    $this->tYPE="file";
    // On récupère le nom original du fichier de l'internaute
    $name = $this->file->getClientOriginalName();

    // On déplace le fichier envoyé dans le répertoire de notre choix
    $this->file->move($this->getUploadRootDir($proj,$sect,$task), $name);

    //on fait une sauvegarde
    //$this->file->move('e:/researchlab/'.$proj.'/'.$sect.'/'.$task.'/references'.$name);
    
    // On sauvegarde le nom de fichier dans notre attribut $url
    $this->uRL = $this->getUploadDir($proj,$sect,$task).'/'.$name;
    }
    public function getUploadDir($proj,$sect,$task)
    {
    // On retourne le chemin relatif vers l'image pour un navigateur (relatif au répertoire /web donc)
    return 'uploads/'.$proj.'/'.$sect.'/'.$task.'/references';
    }

    protected function getUploadRootDir($proj,$sect,$task)
    {
    // On retourne le chemin relatif vers l'image pour notre code PHP
    return __DIR__.'/../../../../web/'.$this->getUploadDir($proj,$sect,$task);
    }

}

