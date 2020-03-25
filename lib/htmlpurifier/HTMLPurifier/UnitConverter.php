<?php


class HTMLPurifier_UnitConverter
{

    const ENGLISH = 1;
    const METRIC = 2;
    const DIGITAL = 3;

    
    protected static $units = array(
        self::ENGLISH => array(
            'px' => 3,             'pt' => 4,
            'pc' => 48,
            'in' => 288,
            self::METRIC => array('pt', '0.352777778', 'mm'),
        ),
        self::METRIC => array(
            'mm' => 1,
            'cm' => 10,
            self::ENGLISH => array('mm', '2.83464567', 'pt'),
        ),
    );

    
    protected $outputPrecision;

    
    protected $internalPrecision;

    
    private $bcmath;

    public function __construct($output_precision = 4, $internal_precision = 10, $force_no_bcmath = false)
    {
        $this->outputPrecision = $output_precision;
        $this->internalPrecision = $internal_precision;
        $this->bcmath = !$force_no_bcmath && function_exists('bcmul');
    }

    
    public function convert($length, $to_unit)
    {
        if (!$length->isValid()) {
            return false;
        }

        $n = $length->getN();
        $unit = $length->getUnit();

        if ($n === '0' || $unit === false) {
            return new HTMLPurifier_Length('0', false);
        }

        $state = $dest_state = false;
        foreach (self::$units as $k => $x) {
            if (isset($x[$unit])) {
                $state = $k;
            }
            if (isset($x[$to_unit])) {
                $dest_state = $k;
            }
        }
        if (!$state || !$dest_state) {
            return false;
        }

                        $sigfigs = $this->getSigFigs($n);
        if ($sigfigs < $this->outputPrecision) {
            $sigfigs = $this->outputPrecision;
        }

                                        $log = (int)floor(log(abs($n), 10));
        $cp = ($log < 0) ? $this->internalPrecision - $log : $this->internalPrecision; 
        for ($i = 0; $i < 2; $i++) {

                        if ($dest_state === $state) {
                                $dest_unit = $to_unit;
            } else {
                                $dest_unit = self::$units[$state][$dest_state][0];
            }

                        if ($dest_unit !== $unit) {
                $factor = $this->div(self::$units[$state][$unit], self::$units[$state][$dest_unit], $cp);
                $n = $this->mul($n, $factor, $cp);
                $unit = $dest_unit;
            }

                        if ($n === '') {
                $n = '0';
                $unit = $to_unit;
                break;
            }

                        if ($dest_state === $state) {
                break;
            }

            if ($i !== 0) {
                                                return false;
            }

            
                        $n = $this->mul($n, self::$units[$state][$dest_state][1], $cp);
            $unit = self::$units[$state][$dest_state][2];
            $state = $dest_state;

            
        }

                if ($unit !== $to_unit) {
            return false;
        }

                        
        $n = $this->round($n, $sigfigs);
        if (strpos($n, '.') !== false) {
            $n = rtrim($n, '0');
        }
        $n = rtrim($n, '.');

        return new HTMLPurifier_Length($n, $unit);
    }

    
    public function getSigFigs($n)
    {
        $n = ltrim($n, '0+-');
        $dp = strpos($n, '.');         if ($dp === false) {
            $sigfigs = strlen(rtrim($n, '0'));
        } else {
            $sigfigs = strlen(ltrim($n, '0.'));             if ($dp !== 0) {
                $sigfigs--;
            }
        }
        return $sigfigs;
    }

    
    private function add($s1, $s2, $scale)
    {
        if ($this->bcmath) {
            return bcadd($s1, $s2, $scale);
        } else {
            return $this->scale((float)$s1 + (float)$s2, $scale);
        }
    }

    
    private function mul($s1, $s2, $scale)
    {
        if ($this->bcmath) {
            return bcmul($s1, $s2, $scale);
        } else {
            return $this->scale((float)$s1 * (float)$s2, $scale);
        }
    }

    
    private function div($s1, $s2, $scale)
    {
        if ($this->bcmath) {
            return bcdiv($s1, $s2, $scale);
        } else {
            return $this->scale((float)$s1 / (float)$s2, $scale);
        }
    }

    
    private function round($n, $sigfigs)
    {
        $new_log = (int)floor(log(abs($n), 10));         $rp = $sigfigs - $new_log - 1;         $neg = $n < 0 ? '-' : '';         if ($this->bcmath) {
            if ($rp >= 0) {
                $n = bcadd($n, $neg . '0.' . str_repeat('0', $rp) . '5', $rp + 1);
                $n = bcdiv($n, '1', $rp);
            } else {
                                                $n = bcadd($n, $neg . '5' . str_repeat('0', $new_log - $sigfigs), 0);
                $n = substr($n, 0, $sigfigs + strlen($neg)) . str_repeat('0', $new_log - $sigfigs + 1);
            }
            return $n;
        } else {
            return $this->scale(round($n, $sigfigs - $new_log - 1), $rp + 1);
        }
    }

    
    private function scale($r, $scale)
    {
        if ($scale < 0) {
                                    $r = sprintf('%.0f', (float)$r);
                                                            $precise = (string)round(substr($r, 0, strlen($r) + $scale), -1);
                        return substr($precise, 0, -1) . str_repeat('0', -$scale + 1);
        }
        return sprintf('%.' . $scale . 'f', (float)$r);
    }
}

