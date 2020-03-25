<?php




class PHPExcel_Chart_DataSeries
{
    const TYPE_BARCHART        = 'barChart';
    const TYPE_BARCHART_3D     = 'bar3DChart';
    const TYPE_LINECHART       = 'lineChart';
    const TYPE_LINECHART_3D    = 'line3DChart';
    const TYPE_AREACHART       = 'areaChart';
    const TYPE_AREACHART_3D    = 'area3DChart';
    const TYPE_PIECHART        = 'pieChart';
    const TYPE_PIECHART_3D     = 'pie3DChart';
    const TYPE_DOUGHTNUTCHART  = 'doughnutChart';
    const TYPE_DONUTCHART      = self::TYPE_DOUGHTNUTCHART;        const TYPE_SCATTERCHART    = 'scatterChart';
    const TYPE_SURFACECHART    = 'surfaceChart';
    const TYPE_SURFACECHART_3D = 'surface3DChart';
    const TYPE_RADARCHART      = 'radarChart';
    const TYPE_BUBBLECHART     = 'bubbleChart';
    const TYPE_STOCKCHART      = 'stockChart';
    const TYPE_CANDLECHART     = self::TYPE_STOCKCHART;       
    const GROUPING_CLUSTERED       = 'clustered';
    const GROUPING_STACKED         = 'stacked';
    const GROUPING_PERCENT_STACKED = 'percentStacked';
    const GROUPING_STANDARD        = 'standard';

    const DIRECTION_BAR        = 'bar';
    const DIRECTION_HORIZONTAL = self::DIRECTION_BAR;
    const DIRECTION_COL        = 'col';
    const DIRECTION_COLUMN     = self::DIRECTION_COL;
    const DIRECTION_VERTICAL   = self::DIRECTION_COL;

    const STYLE_LINEMARKER   = 'lineMarker';
    const STYLE_SMOOTHMARKER = 'smoothMarker';
    const STYLE_MARKER       = 'marker';
    const STYLE_FILLED       = 'filled';


    
    private $plotType;

    
    private $plotGrouping;

    
    private $plotDirection;

    
    private $plotStyle;

    
    private $plotOrder = array();

    
    private $plotLabel = array();

    
    private $plotCategory = array();

    
    private $smoothLine;

    
    private $plotValues = array();

    
    public function __construct($plotType = null, $plotGrouping = null, $plotOrder = array(), $plotLabel = array(), $plotCategory = array(), $plotValues = array(), $plotDirection = null, $smoothLine = null, $plotStyle = null)
    {
        $this->plotType = $plotType;
        $this->plotGrouping = $plotGrouping;
        $this->plotOrder = $plotOrder;
        $keys = array_keys($plotValues);
        $this->plotValues = $plotValues;
        if ((count($plotLabel) == 0) || (is_null($plotLabel[$keys[0]]))) {
            $plotLabel[$keys[0]] = new PHPExcel_Chart_DataSeriesValues();
        }

        $this->plotLabel = $plotLabel;
        if ((count($plotCategory) == 0) || (is_null($plotCategory[$keys[0]]))) {
            $plotCategory[$keys[0]] = new PHPExcel_Chart_DataSeriesValues();
        }
        $this->plotCategory = $plotCategory;
        $this->smoothLine = $smoothLine;
        $this->plotStyle = $plotStyle;
        
        if (is_null($plotDirection)) {
            $plotDirection = self::DIRECTION_COL;
        }
        $this->plotDirection = $plotDirection;
    }

    
    public function getPlotType()
    {
        return $this->plotType;
    }

    
    public function setPlotType($plotType = '')
    {
        $this->plotType = $plotType;
        return $this;
    }

    
    public function getPlotGrouping()
    {
        return $this->plotGrouping;
    }

    
    public function setPlotGrouping($groupingType = null)
    {
        $this->plotGrouping = $groupingType;
        return $this;
    }

    
    public function getPlotDirection()
    {
        return $this->plotDirection;
    }

    
    public function setPlotDirection($plotDirection = null)
    {
        $this->plotDirection = $plotDirection;
        return $this;
    }

    
    public function getPlotOrder()
    {
        return $this->plotOrder;
    }

    
    public function getPlotLabels()
    {
        return $this->plotLabel;
    }

    
    public function getPlotLabelByIndex($index)
    {
        $keys = array_keys($this->plotLabel);
        if (in_array($index, $keys)) {
            return $this->plotLabel[$index];
        } elseif (isset($keys[$index])) {
            return $this->plotLabel[$keys[$index]];
        }
        return false;
    }

    
    public function getPlotCategories()
    {
        return $this->plotCategory;
    }

    
    public function getPlotCategoryByIndex($index)
    {
        $keys = array_keys($this->plotCategory);
        if (in_array($index, $keys)) {
            return $this->plotCategory[$index];
        } elseif (isset($keys[$index])) {
            return $this->plotCategory[$keys[$index]];
        }
        return false;
    }

    
    public function getPlotStyle()
    {
        return $this->plotStyle;
    }

    
    public function setPlotStyle($plotStyle = null)
    {
        $this->plotStyle = $plotStyle;
        return $this;
    }

    
    public function getPlotValues()
    {
        return $this->plotValues;
    }

    
    public function getPlotValuesByIndex($index)
    {
        $keys = array_keys($this->plotValues);
        if (in_array($index, $keys)) {
            return $this->plotValues[$index];
        } elseif (isset($keys[$index])) {
            return $this->plotValues[$keys[$index]];
        }
        return false;
    }

    
    public function getPlotSeriesCount()
    {
        return count($this->plotValues);
    }

    
    public function getSmoothLine()
    {
        return $this->smoothLine;
    }

    
    public function setSmoothLine($smoothLine = true)
    {
        $this->smoothLine = $smoothLine;
        return $this;
    }

    public function refresh(PHPExcel_Worksheet $worksheet)
    {
        foreach ($this->plotValues as $plotValues) {
            if ($plotValues !== null) {
                $plotValues->refresh($worksheet, true);
            }
        }
        foreach ($this->plotLabel as $plotValues) {
            if ($plotValues !== null) {
                $plotValues->refresh($worksheet, true);
            }
        }
        foreach ($this->plotCategory as $plotValues) {
            if ($plotValues !== null) {
                $plotValues->refresh($worksheet, false);
            }
        }
    }
}
