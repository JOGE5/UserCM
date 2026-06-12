<?php

namespace App\Services;

class QueueTheoryService
{
    /**
     * Calculate M/M/c priority queuing metrics
     * 
     * @param float $lambda1 Arrival rate of high priority (offensive) reports
     * @param float $lambda2 Arrival rate of low priority (minor/bug) reports
     * @param float $mu Service rate per moderator (reports resolved per day)
     * @param int $c Number of moderators (servers)
     * @return array
     */
    public function calculateMetrics($lambda1, $lambda2, $mu, $c)
    {
        $lambda = $lambda1 + $lambda2;
        
        // Prevent division by zero
        if ($mu <= 0 || $c <= 0 || $lambda <= 0) {
            return $this->getEmptyMetrics();
        }

        $rho = $lambda / ($c * $mu);
        $rho1 = $lambda1 / ($c * $mu);
        
        // If unstable, return infinities
        if ($rho >= 1) {
            return [
                'lambda' => $lambda,
                'lambda1' => $lambda1,
                'lambda2' => $lambda2,
                'mu' => $mu,
                'c' => $c,
                'rho' => round($rho, 2),
                'estable' => false,
                'P0' => 0,
                'Lq' => '∞',
                'Wq' => '∞',
                'W' => '∞',
                'Wq1' => '∞',
                'W1' => '∞',
                'Wq2' => '∞',
                'W2' => '∞',
            ];
        }

        // 1. Calculate P0 (Probability of 0 units in system)
        $p0_sum = 0;
        $lambda_mu = $lambda / $mu;
        for ($n = 0; $n < $c; $n++) {
            $p0_sum += pow($lambda_mu, $n) / $this->factorial($n);
        }
        
        $p0_last_term = (pow($lambda_mu, $c) / $this->factorial($c)) * (1 / (1 - $rho));
        $P0 = 1 / ($p0_sum + $p0_last_term);

        // 2. Calculate Lq (Average length of queue)
        $Lq = ($P0 * pow($lambda_mu, $c) * $rho) / ($this->factorial($c) * pow(1 - $rho, 2));

        // 3. Calculate basic Wq and W (Average waiting times for M/M/c without priorities)
        $Wq_basic = $Lq / $lambda;
        $W_basic = $Wq_basic + (1 / $mu);

        // 4. Calculate Priority waiting times (Non-preemptive M/M/c)
        // Wq1 (Urgente) = Wq_basic * (1 - rho) / (1 - rho1)
        // Wq2 (Normal)  = Wq_basic * (1 - rho) / ((1 - rho1) * (1 - rho)) = Wq_basic / (1 - rho1)
        
        $denominator1 = 1 - $rho1;
        
        // If priority 1 alone is unstable, Wq1 -> infinity
        if ($rho1 >= 1) {
            $Wq1 = INF;
            $Wq2 = INF;
        } else {
            $Wq1 = $Wq_basic * (1 - $rho) / $denominator1;
            $Wq2 = $Wq_basic / $denominator1;
        }

        $W1 = $Wq1 + (1 / $mu);
        $W2 = $Wq2 + (1 / $mu);

        return [
            'lambda' => round($lambda, 2),
            'lambda1' => round($lambda1, 2),
            'lambda2' => round($lambda2, 2),
            'mu' => round($mu, 2),
            'c' => $c,
            'rho' => round($rho, 2),
            'estable' => true,
            'P0' => round($P0, 4),
            'Lq' => round($Lq, 2),
            'Wq' => round($Wq_basic, 4), // basic wait in queue (days)
            'W' => round($W_basic, 4),   // basic total wait (days)
            'Wq1' => round($Wq1, 4),     // wait in queue for P1 (days)
            'W1' => round($W1, 4),       // total wait for P1 (days)
            'Wq2' => round($Wq2, 4),     // wait in queue for P2 (days)
            'W2' => round($W2, 4),       // total wait for P2 (days)
        ];
    }

    private function getEmptyMetrics()
    {
        return [
            'lambda' => 0, 'lambda1' => 0, 'lambda2' => 0, 'mu' => 0, 'c' => 0,
            'rho' => 0, 'estable' => true, 'P0' => 1, 'Lq' => 0,
            'Wq' => 0, 'W' => 0, 'Wq1' => 0, 'W1' => 0, 'Wq2' => 0, 'W2' => 0,
        ];
    }

    private function factorial($n)
    {
        if ($n <= 1) return 1;
        $res = 1;
        for ($i = 2; $i <= $n; $i++) {
            $res *= $i;
        }
        return $res;
    }
}
