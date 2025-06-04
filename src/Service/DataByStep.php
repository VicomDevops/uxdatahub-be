<?php

namespace App\Service;

class DataByStep
{
    const INFINI=1000000;

    public function average($allScores)
    {
        try {
            if(count($allScores) == 0 ){
                return 0;
            }else
            {
                    return array_sum($allScores) / count($allScores);
            }
        }catch (\Exception $exception)
        {
            return 0;
        }
    }

    public function deviation($scores, $average)
    {
        try {
            $n = count($scores);
            if($average == null){
                return null;
            }else if($n <= 1) {
                return $this::INFINI;
            }else {
                $et = 0;
                foreach ($scores as $i){
                    $et += ($i - $average) ** 2;
                }
                $et = $et / $n;
                return sqrt($et);
            }

        }catch (\Exception $exception)
        {
            return 0;
        }
    }
}