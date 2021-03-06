<?php

namespace ResearchLabUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="ResearchLabUserBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
	public function __construct()
	{
		parent::__construct();
		// your own logic
		$this->addRole('ROLE_MANAGER');
	}
}

