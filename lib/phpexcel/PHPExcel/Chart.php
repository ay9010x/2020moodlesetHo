<?php


class PHPExcel_Chart
{
    
    private $name = '';

    
    private $worksheet;

    
    private $title;

    
    private $legend;

    
    private $xAxisLabel;

    
    private $yAxisLabel;

    
    private $plotArea;

    
    private $plotVisibleOnly = true;

    
    private $displayBlanksAs = '0';

    
    private $yAxis;

    
    private $xAxis;

    
    private $majorGridlines;

    
    private $minorGridlines;

    
    private $topLeftCellRef = 'A1';


    
    private $topLeftXOffset = 0;


    
    private $topLeftYOffset = 0;


    
    private $bottomRightCellRef = 'A1';


    
    private $bottomRightXOffset = 10;


    
    private $bottomRightYOffset = 10;


    
    public function __construct($name, PHPExcel_Chart_Title $title = null, PHPExcel_Chart_Legend $legend = null, PHPExcel_Chart_PlotArea $plotArea = null, $plotVisibleOnly = true, $displayBlanksAs = '0', PHPExcel_Chart_Title $xAxisLabel = null, PHPExcel_Chart_Title $yAxisLabel = null, PHPExcel_Chart_Axis $xAxis = null, PHPExcel_Chart_Axis $yAxis = null, PHPExcel_Chart_GridLines $majorGridlines = null, PHPExcel_Chart_GridLines $minorGridlines = null)
    {
        $this->name = $name;
        $this->title = $title;
        $this->legend = $legend;
        $this->xAxisLabel = $xAxisLabel;
        $this->yAxisLabel = $yAxisLabel;
        $this->plotArea = $plotArea;
        $this->plotVisibleOnly = $plotVisibleOnly;
        $this->displayBlanksAs = $displayBlanksAs;
        $this->xAxis = $xAxis;
        $this->yAxis = $yAxis;
        $this->majorGridlines = $majorGridlines;
        $this->minorGridlines = $minorGridlines;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function getWorksheet()
    {
        return $this->worksheet;
    }

    
    public function setWorksheet(PHPExcel_Worksheet $pValue = null)
    {
        $this->worksheet = $pValue;

        return $this;
    }

    
    public function getTitle()
    {
        return $this->title;
    }

    
    public function setTitle(PHPExcel_Chart_Title $title)
    {
        $this->title = $title;

        return $this;
    }

    
    public function getLegend()
    {
        return $this->legend;
    }

    
    public function setLegend(PHPExcel_Chart_Legend $legend)
    {
        $this->legend = $legend;

        return $this;
    }

    
    public function getXAxisLabel()
    {
        return $this->xAxisLabel;
    }

    
    public function setXAxisLabel(PHPExcel_Chart_Title $label)
    {
        $this->xAxisLabel = $label;

        return $this;
    }

    
    public function getYAxisLabel()
    {
        return $this->yAxisLabel;
    }

    
    public function setYAxisLabel(PHPExcel_Chart_Title $label)
    {
        $this->yAxisLabel = $label;

        return $this;
    }

    
    public function getPlotArea()
    {
        return $this->plotArea;
    }

    
    public function getPlotVisibleOnly()
    {
        return $this->plotVisibleOnly;
    }

    
    public function setPlotVisibleOnly($plotVisibleOnly = true)
    {
        $this->plotVisibleOnly = $plotVisibleOnly;

        return $this;
    }

    
    public function getDisplayBlanksAs()
    {
        return $this->displayBlanksAs;
    }

    
    public function setDisplayBlanksAs($displayBlanksAs = '0')
    {
        $this->displayBlanksAs = $displayBlanksAs;
    }


    
    public function getChartAxisY()
    {
        if ($this->yAxis !== null) {
            return $this->yAxis;
        }

        return new PHPExcel_Chart_Axis();
    }

    
    public function getChartAxisX()
    {
        if ($this->xAxis !== null) {
            return $this->xAxis;
        }

        return new PHPExcel_Chart_Axis();
    }

    
    public function getMajorGridlines()
    {
        if ($this->majorGridlines !== null) {
            return $this->majorGridlines;
        }

        return new PHPExcel_Chart_GridLines();
    }

    
    public function getMinorGridlines()
    {
        if ($this->minorGridlines !== null) {
            return $this->minorGridlines;
        }

        return new PHPExcel_Chart_GridLines();
    }


    
    public function setTopLeftPosition($cell, $xOffset = null, $yOffset = null)
    {
        $this->topLeftCellRef = $cell;
        if (!is_null($xOffset)) {
            $this->setTopLeftXOffset($xOffset);
        }
        if (!is_null($yOffset)) {
            $this->setTopLeftYOffset($yOffset);
        }

        return $this;
    }

    
    public function getTopLeftPosition()
    {
        return array(
            'cell'    => $this->topLeftCellRef,
            'xOffset' => $this->topLeftXOffset,
            'yOffset' => $this->topLeftYOffset
        );
    }

    
    public function getTopLeftCell()
    {
        return $this->topLeftCellRef;
    }

    
    public function setTopLeftCell($cell)
    {
        $this->topLeftCellRef = $cell;

        return $this;
    }

    
    public function setTopLeftOffset($xOffset = null, $yOffset = null)
    {
        if (!is_null($xOffset)) {
            $this->setTopLeftXOffset($xOffset);
        }
        if (!is_null($yOffset)) {
            $this->setTopLeftYOffset($yOffset);
        }

        return $this;
    }

    
    public function getTopLeftOffset()
    {
        return array(
            'X' => $this->topLeftXOffset,
            'Y' => $this->topLeftYOffset
        );
    }

    public function setTopLeftXOffset($xOffset)
    {
        $this->topLeftXOffset = $xOffset;

        return $this;
    }

    public function getTopLeftXOffset()
    {
        return $this->topLeftXOffset;
    }

    public function setTopLeftYOffset($yOffset)
    {
        $this->topLeftYOffset = $yOffset;

        return $this;
    }

    public function getTopLeftYOffset()
    {
        return $this->topLeftYOffset;
    }

    
    public function setBottomRightPosition($cell, $xOffset = null, $yOffset = null)
    {
        $this->bottomRightCellRef = $cell;
        if (!is_null($xOffset)) {
            $this->setBottomRightXOffset($xOffset);
        }
        if (!is_null($yOffset)) {
            $this->setBottomRightYOffset($yOffset);
        }

        return $this;
    }

    
    public function getBottomRightPosition()
    {
        return array(
            'cell'    => $this->bottomRightCellRef,
            'xOffset' => $this->bottomRightXOffset,
            'yOffset' => $this->bottomRightYOffset
        );
    }

    public function setBottomRightCell($cell)
    {
        $this->bottomRightCellRef = $cell;

        return $this;
    }

    
    public function getBottomRightCell()
    {
        return $this->bottomRightCellRef;
    }

    
    public function setBottomRightOffset($xOffset = null, $yOffset = null)
    {
        if (!is_null($xOffset)) {
            $this->setBottomRightXOffset($xOffset);
        }
        if (!is_null($yOffset)) {
            $this->setBottomRightYOffset($yOffset);
        }

        return $this;
    }

    
    public function getBottomRightOffset()
    {
        return array(
            'X' => $this->bottomRightXOffset,
            'Y' => $this->bottomRightYOffset
        );
    }

    public function setBottomRightXOffset($xOffset)
    {
        $this->bottomRightXOffset = $xOffset;

        return $this;
    }

    public function getBottomRightXOffset()
    {
        return $this->bottomRightXOffset;
    }

    public function setBottomRightYOffset($yOffset)
    {
        $this->bottomRightYOffset = $yOffset;

        return $this;
    }

    public function getBottomRightYOffset()
    {
        return $this->bottomRightYOffset;
    }


    public function refresh()
    {
        if ($this->worksheet !== null) {
            $this->plotArea->refresh($this->worksheet);
        }
    }

    public function render($outputDestination = null)
    {
        $libraryName = PHPExcel_Settings::getChartRendererName();
        if (is_null($libraryName)) {
            return false;
        }
                $this->refresh();

        $libraryPath = PHPExcel_Settings::getChartRendererPath();
        $includePath = str_replace('\\', '/', get_include_path());
        $rendererPath = str_replace('\\', '/', $libraryPath);
        if (strpos($rendererPath, $includePath) === false) {
            set_include_path(get_include_path() . PATH_SEPARATOR . $libraryPath);
        }

        $rendererName = 'PHPExcel_Chart_Renderer_'.$libraryName;
        $renderer = new $rendererName($this);

        if ($outputDestination == 'php://output') {
            $outputDestination = null;
        }
        return $renderer->render($outputDestination);
    }
}
