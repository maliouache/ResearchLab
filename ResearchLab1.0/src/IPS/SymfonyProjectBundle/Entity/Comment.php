<?php

namespace IPS\SymfonyProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ResearchLabUserBundle\Entity\User;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="IPS\SymfonyProjectBundle\Repository\CommentRepository")
 */
class Comment
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
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="ResearchLabUserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $sender;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity="IPS\SymfonyProjectBundle\Entity\Task")
     * @ORM\JoinColumn(nullable=true)
     */
    private $task;

    /**
     * @ORM\ManyToOne(targetEntity="IPS\SymfonyProjectBundle\Entity\Section")
     * @ORM\JoinColumn(nullable=true)
     */
    private $section;

    /**
     * @ORM\ManyToOne(targetEntity="IPS\SymfonyProjectBundle\Entity\Project")
     * @ORM\JoinColumn(nullable=true)
     */
    private $project;

    /**
     * @var int
     *
     * @ORM\Column(name="seen", type="integer")
     */
    private $seen;

    /**
     * @ORM\ManyToOne(targetEntity="ResearchLabUserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $receiver;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="adddate", type="datetime", nullable=true)
     */
    private $adddate;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content.
     *
     * @param string|null $content
     *
     * @return Comment
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set sender.
     *
     * @param int $sender
     *
     * @return Comment
     */
    public function setSender(User $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender.
     *
     * @return int
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set subject.
     *
     * @param string $subject
     *
     * @return Comment
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    public function setTask(Task $task)
    {
        $this->task = $task;

        return $this;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function setSection(Section $section = null)
    {
        $this->section = $section;

        return $this;
    }

    public function getSection()
    {
        return $this->section;
    }

 
    public function setProject(Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set seen.
     *
     * @param int $seen
     *
     * @return Comment
     */
    public function setSeen($seen)
    {
        $this->seen = $seen;

        return $this;
    }

    /**
     * Get seen.
     *
     * @return int
     */
    public function getSeen()
    {
        return $this->seen;
    }

  
    public function setReceiver(User $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }


    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set adddate.
     *
     * @param \DateTime|null $adddate
     *
     * @return Comment
     */
    public function setAdddate($adddate = null)
    {
        $this->adddate = $adddate;

        return $this;
    }

    /**
     * Get adddate.
     *
     * @return \DateTime|null
     */
    public function getAdddate()
    {
        return $this->adddate;
    }
}
