<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stock
 *
 * @ORM\Table(name="stock", indexes={@ORM\Index(name="FK_symbol_symbolInfo", columns={"symbolId"}), @ORM\Index(name="date", columns={"date"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StockRepository")
 */
class Stock
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="open", type="decimal", precision=10, scale=6, nullable=true)
     */
    private $open;

    /**
     * @var string
     *
     * @ORM\Column(name="high", type="decimal", precision=10, scale=6, nullable=true)
     */
    private $high;

    /**
     * @var string
     *
     * @ORM\Column(name="low", type="decimal", precision=10, scale=6, nullable=true)
     */
    private $low;

    /**
     * @var string
     *
     * @ORM\Column(name="close", type="decimal", precision=10, scale=6, nullable=true)
     */
    private $close;

    /**
     * @var string
     *
     * @ORM\Column(name="`change`", type="decimal", precision=10, scale=6, nullable=true)
     */
    private $change;

    /**
     * @var integer
     *
     * @ORM\Column(name="volume", type="bigint", nullable=true)
     */
    private $volume;

    /**
     * @var \Symbol
     *
     * @ORM\ManyToOne(targetEntity="Symbol")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="symbolId", referencedColumnName="id")
     * })
     */
    private $symbolid;



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
     * Set date
     *
     * @param \DateTime $date
     * @return Stock
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set open
     *
     * @param string $open
     * @return Stock
     */
    public function setOpen($open)
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get open
     *
     * @return string
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * Set high
     *
     * @param string $high
     * @return Stock
     */
    public function setHigh($high)
    {
        $this->high = $high;

        return $this;
    }

    /**
     * Get high
     *
     * @return string
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * Set low
     *
     * @param string $low
     * @return Stock
     */
    public function setLow($low)
    {
        $this->low = $low;

        return $this;
    }

    /**
     * Get low
     *
     * @return string
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * Set close
     *
     * @param string $close
     * @return Stock
     */
    public function setClose($close)
    {
        $this->close = $close;

        return $this;
    }

    /**
     * Get close
     *
     * @return string
     */
    public function getClose()
    {
        return $this->close;
    }

    /**
     * Set change
     *
     * @param string $change
     * @return Stock
     */
    public function setChange($change)
    {
        $this->change = $change;

        return $this;
    }

    /**
     * Get change
     *
     * @return string
     */
    public function getChange()
    {
        return $this->change;
    }

    /**
     * Set volume
     *
     * @param integer $volume
     * @return Stock
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return integer
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set symbolid
     *
     * @param \AppBundle\Entity\Symbol $symbolid
     * @return Stock
     */
    public function setSymbolid(\AppBundle\Entity\Symbol $symbolid = null)
    {
        $this->symbolid = $symbolid;

        return $this;
    }

    /**
     * Get symbolid
     *
     * @return \AppBundle\Entity\Symbol
     */
    public function getSymbolid()
    {
        return $this->symbolid;
    }
}
