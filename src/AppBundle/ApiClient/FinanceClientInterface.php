<?php

namespace AppBundle\ApiClient;

interface FinanceClientInterface {
    public function get($symbolName, $startDate, $endDate);
}
