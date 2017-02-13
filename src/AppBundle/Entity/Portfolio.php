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
    private $user;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Symbol", inversedBy="portfolio")
     * @ORM\JoinTable(name="portfolio_symbol",
     *   joinColumns={
     *     @ORM\JoinColumn(name="portfolioId", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="symbolId", referencedColumnName="id")
     *   }
     * )
     */
    private $symbols;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->symbols = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Portfolio
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add symbol
     *
     * @param \AppBundle\Entity\Symbol $symbol
     * @return Portfolio
     */
    public function addSymbol(\AppBundle\Entity\Symbol $symbol)
    {
        $this->symbols[] = $symbol;

        return $this;
    }

    /**
     * Remove symbol
     *
     * @param \AppBundle\Entity\Symbol $symbolid
     */
    public function removeSymbol(\AppBundle\Entity\Symbol $symbol)
    {
        $this->symbols->removeElement($symbol);
    }

    /**
     * Get symbols
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSymbols()
    {
        return $this->symbols;
    }

    public function getSymbolIds()
    {
        $symbolIds = $this->getSymbols()->map(function($symbol) {
            return $symbol->getId();
        })->toArray();
        
        return $symbolIds;
    }
    
    /**
     * Проверяет, является ли указанный пользователь владельцем портфеля
     *
     * @param \AppBundle\Entity\User $user
     * @return boolean
     */
    public function isOwner(\AppBundle\Entity\User $user)
    {
        if (is_null($user) || is_null($this->getUser())) {
            return false;
        }

        if ($user->getId() === $this->getUser()->getId()) {
            return true;
        }

        return false;
    }

    /**
     * @return type
     */
    public function __toString()
    {
        return $this->name;
    }
}
