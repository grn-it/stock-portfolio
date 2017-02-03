<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Portfolio
 *
 * @ORM\Table(name="portfolio", indexes={@ORM\Index(name="FK_portfolio_user", columns={"userId"})})
 * @ORM\Entity
 */
class Portfolio
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userId", referencedColumnName="id")
     * })
     */
    private $userid;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Symbol", inversedBy="portfolioid")
     * @ORM\JoinTable(name="portfolio_symbol",
     *   joinColumns={
     *     @ORM\JoinColumn(name="portfolioId", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="symbolId", referencedColumnName="id")
     *   }
     * )
     */
    private $symbolid;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->symbolid = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Portfolio
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set userid
     *
     * @param \AppBundle\Entity\User $userid
     * @return Portfolio
     */
    public function setUserid(\AppBundle\Entity\User $userid = null)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Add symbolid
     *
     * @param \AppBundle\Entity\Symbol $symbolid
     * @return Portfolio
     */
    public function addSymbolid(\AppBundle\Entity\Symbol $symbolid)
    {
        $this->symbolid[] = $symbolid;

        return $this;
    }

    /**
     * Remove symbolid
     *
     * @param \AppBundle\Entity\Symbol $symbolid
     */
    public function removeSymbolid(\AppBundle\Entity\Symbol $symbolid)
    {
        $this->symbolid->removeElement($symbolid);
    }

    /**
     * Get symbolid
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSymbolid()
    {
        return $this->symbolid;
    }
    
    public function isOwner(\AppBundle\Entity\User $user) {
    
        if (is_null($user) || is_null($this->getUserid())) {
            return false;
        }
        
        if ($user->getId() === $this->getUserid()->getId()) {
            return true;
        }
        
        return false;
    } 
    
    public function __toString() {
        
        return $this->name;
    }
}
