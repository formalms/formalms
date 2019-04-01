<?php

namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class TestConfigurationMethodOfUseRenderEvent extends Event
{
    const EVENT_SECTION_BASE = 0;
    
    const EVENT_NAME = 'lms.test.configuration_method_of_use_render';
    
    protected $formElementsSections;
    /**
     * @var null
     */
    protected $object_test = null;
    /**
     * @var null
     */
    protected $lang = null;
    
    public function __construct ($object_test , $lang)
    {
        $this->object_test = $object_test;
        $this->lang = $lang;
        
        $this->formElementsSections[ self::EVENT_SECTION_BASE ] = [];
        
    }
    
    /**
     * @return null
     */
    public function getLang ()
    {
        return $this->lang;
    }
    
    /**
     * @return null
     */
    public function getObjectTest ()
    {
        return $this->object_test;
    }
    
    public function resetFormElementsForSection ($section)
    {
        $this->formElementsSections[ $section ] = [];
    }
    
    
    /**
     * @param array $formElements
     * @param integer $section
     */
    public function setFormElementsForSection (array $formElements , int $section)
    {
        $this->formElementsSections[ $section ] = $formElements;
    }
    
    /**
     * @return array
     */
    public function getFormElements ()
    {
        return $this->formElements;
    }
    
    /**
     * @param integer $section
     * @return array
     */
    public function getFormElementsForSection ( $section)
    {
        if (isset($this->formElementsSections[ $section ])) {
            return $this->formElementsSections[ $section ];
        }
        return [];
    }
    
    public function addFormElementForSection ($formElement , $section)
    {
        $this->formElementsSections[ $section ][] = $formElement;
    }
    
    public function getElementString ()
    {
        $formString = "";
        
        foreach ($this->formElementsSections as $section => $formElements) {
            foreach ($formElements as $formElement) {
                $formString .= $formElement;
            }
        }
        
        return $formString;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'formElementsSections' => $this->formElementsSections,
            'object_test' => $this->object_test,
            'lang' => $this->lang,
        ];
    }
    
}
