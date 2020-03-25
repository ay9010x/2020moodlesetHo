<?php


class PHPExcel_Chart_PlotArea
{
    
    private $layout = null;

    
    private $plotSeries = array();

    
    public function __construct(PHPExcel_Chart_Layout $layout = null, $plotSeries = array())
    {
        $this->layout = $layout;
        $this->plotSeries = $plotSeries;
    }

    
    public function getLayout()
    {
        return $this->layout;
    }

    
    public function getPlotGroupCount()
    {
        return count($this->plotSeries);
    }

    
    public function getPlotSeriesCount()
    {
        $seriesCount = 0;
        foreach ($this->plotSeries as $plot) {
            $seriesCount += $plot->getPlotSeriesCount();
        }
        return $seriesCount;
    }

    
    public function getPlotGroup()
    {
        return $this->plotSeries;
    }

    
    public function getPlotGroupByIndex($index)
    {
        return $this->plotSeries[$index];
    }

    
    public function setPlotSeries($plotSeries = array())
    {
        $this->plotSeries = $plotSeries;
        
        return $this;
    }

    public function refresh(PHPExcel_Worksheet $worksheet)
    {
        foreach ($this->plotSeries as $plotSeries) {
            $plotSeries->refresh($worksheet);
        }
    }
}
