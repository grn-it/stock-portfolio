<?php

namespace AppBundle\Util\Date;

class DateUtil implements DateUtilInterface
{
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @return string
     */
    public function getLastWeek()
    {
        return date(self::DATE_FORMAT, strtotime('-7 day'));
    }
    
    /**
     * @param string $date
     * @return string
     */
    public function getYesterday($date = '')
    {
        return date(self::DATE_FORMAT, strtotime($date.' -1 day'));
    }
    
    /**
     * @param string $date
     * @return string
     */
    public function getTomorrow($date = '')
    {
        return date(self::DATE_FORMAT, strtotime($date.' +1 day'));
    }
    
    /**
     * @return string
     */
    public function getLastTwoYear()
    {
        return date(self::DATE_FORMAT, strtotime('-2 year'));
    }
    
    /**
     * @param string $date
     * @return string
     */
    public function getDefaultStockStartDateIfNull($date = null)
    {
        if (is_null($date)) {
            return $this->getLastTwoYear();
        }
        
        return $date;
    }
    
    /**
     * @return string
     */
    public function getToday()
    {
        return date(self::DATE_FORMAT);
    }
    
    /**
     * @param string $date
     * @return string
     */
    public function getDefaultStockEndDateIfNull($date = null)
    {
        if (is_null($date)) {
            return $this->getToday();
        }
        
        return $date;
    }
    
    /**
     * @param string $startDate
     * @param string $endDate
     * @return boolean
     */
    public function isStartDateGreaterThanEndDate($startDate, $endDate)
    {
        if (strtotime($startDate) > strtotime($endDate)) {
            return true;
        }
        
        return false;
    }

    /**
     * @param string $date
     * @return string
     */
    public function getYear($date = '')
    {
        return date('Y', strtotime($date));
    }
    
    /**
     * @param string $dateFormat
     * @param string $date
     * @return string
     */
    public function getDateWithFormat($dateFormat, $date)
    {
        return date($dateFormat, strtotime($date));
    }
    
    /**
     * @param string $startDateYear
     * @param string $endDateYear
     * @return boolean
     */
    public function isYearEqual($startDateYear, $endDateYear)
    {
        if ($startDateYear == $endDateYear) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param string $startDate
     * @param string $stockMinDate
     * @return boolean
     */
    public function isPastDataMissing($startDate, $stockMinDate)
    {
        if (strtotime($startDate) < strtotime($stockMinDate)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param string $startDate
     * @param string $endDate
     * @param string $startDateFormat
     * @param string $endDateFormat
     * @return array
     */
    public function getDateRangeSingle($startDate, $endDate, $startDateFormat = null, $endDateFormat = null)
    {
        if (is_null($startDateFormat)) {
            $startDateFormat = self::DATE_FORMAT;
        }
        
        if (is_null($endDateFormat)) {
            $endDateFormat = self::DATE_FORMAT;
        }
        
        $dateRange = array('startDate'   => $this->getDateWithFormat($startDateFormat, $startDate),
                           'endDate'     => $this->getDateWithFormat($endDateFormat, $endDate));

        return $dateRange;
    }
    
    /**
     * @param string $currentYear
     * @param string $year
     * @param string $equalYearsValue
     * @param string $notEqualYearsValue
     * @return string
     */
    public function getValueByCompareYears($currentYear, $year, $equalYearsValue, $notEqualYearsValue)
    {
        if ($currentYear == $year) {
            return $equalYearsValue;
        }

        return $notEqualYearsValue;
    }
    
    /**
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getDateRangeByYears($startDate, $endDate)
    {
        $dateRanges = array();
        
        $startDateYear  = $this->getYear($startDate);
        $endDateYear    = $this->getYear($endDate);
        
        for ($currentYear = $startDateYear; $currentYear <= $endDateYear; $currentYear++) {
            $currentStartDate   = $this->getValueByCompareYears($currentYear, $startDateYear,   $startDate,         $currentYear.'-01-01');
            $currentEndDate     = $this->getValueByCompareYears($currentYear, $endDateYear,     $endDate,           $currentYear.'-12-31');
            $startDateFormat    = $this->getValueByCompareYears($currentYear, $startDateYear,   self::DATE_FORMAT,  'Y-01-01');
            $endDateFormat      = $this->getValueByCompareYears($currentYear, $endDateYear,     self::DATE_FORMAT,  'Y-12-31');
            
            $dateRanges[] = $this->getDateRangeSingle($currentStartDate, $currentEndDate, $startDateFormat, $endDateFormat);
        }
        
        return $dateRanges;
    }
    
    /**
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getDateRanges($startDate = null, $endDate = null)
    {
        $startDate  = $this->getYesterday($this->getDefaultStockStartDateIfNull($startDate));
        $endDate    = $this->getDefaultStockEndDateIfNull($endDate);

        if ($this->isStartDateGreaterThanEndDate($startDate, $endDate)) {
            return false;
        }

        $dateRanges = array();

        if ($this->isYearEqual($this->getYear($startDate), $this->getYear($endDate))) {
            $dateRanges[] = $this->getDateRangeSingle($startDate, $endDate);
        } else {
            $dateRanges = $this->getDateRangeByYears($startDate, $endDate);
        }

        return $dateRanges;
    }
}
