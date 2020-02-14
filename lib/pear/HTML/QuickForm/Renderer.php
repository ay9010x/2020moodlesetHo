<?php



class HTML_QuickForm_Renderer
{
   
    public function __construct() {
    } 
    
    public function HTML_QuickForm_Renderer() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

   
    function startForm(&$form)
    {
        return;
    } 
   
    function finishForm(&$form)
    {
        return;
    } 
   
    function renderHeader(&$header)
    {
        return;
    } 
   
    function renderElement(&$element, $required, $error)
    {
        return;
    } 
   
    function renderHidden(&$element)
    {
        return;
    } 
   
    function renderHtml(&$data)
    {
        return;
    } 
   
    function startGroup(&$group, $required, $error)
    {
        return;
    } 
   
    function finishGroup(&$group)
    {
        return;
    } } ?>
