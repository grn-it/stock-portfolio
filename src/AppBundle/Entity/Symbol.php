<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Symbol
 *
 * @ORM\Table(name="symbol")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SymbolRepository")
 */
class Symbol
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
     * @ORM\Column(name="name", type="string", length=10, nullable=true)
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Portfolio", mappedBy="symbolid")
     */
    private $portfolioid;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->portfolioid = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Symbol
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
     * Add portfolioid
     *
     * @param \AppBundle\Entity\Portfolio $portfolioid
     * @return Symbol
     */
    public function addPortfolioid(\AppBundle\Entity\Portfolio $portfolioid)
    {
        $this->portfolioid[] = $portfolioid;

        return $this;
    }

    /**
     * Remove portfolioid
     *
     * @param \AppBundle\Entity\Portfolio $portfolioid
     */
    public function removePortfolioid(\AppBundle\Entity\Portfolio $portfolioid)
    {
        $this->portfolioid->removeElement($portfolioid);
    }

    /**
     * Get portfolioid
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPortfolioid()
    {
        return $this->portfolioid;
    }
    
    public function __toString() {
    
        return $this->name;
    }
}
