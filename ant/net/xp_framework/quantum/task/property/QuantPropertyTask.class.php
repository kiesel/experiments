<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('net.xp_framework.quantum.task.QuantTask');

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class QuantPropertyTask extends QuantTask {
    protected
      $name         = NULL,
      $value        = NULL,
      $location     = NULL,
      $refid        = NULL,
      $file         = NULL,
      $url          = NULL,
      $environment  = NULL,
      $prefix       = NULL;
      
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    #[@xmlmapping(element= '@name')]
    public function setName($name) {
      $this->name= $name;
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    #[@xmlmapping(element= '@value')]
    public function setValue($value) {
      $this->value= $value;
    }
    
    #[@xmlmapping(element= '@location')]
    public function setLocation($l) {
      $this->location= $l;
    }
    
    #[@xmlmapping(element= '@refid')]
    public function setRefid($refid) {
      $this->refid= $refid;
    }
    
    #[@xmlmapping(element= '@file')]
    public function setFile($file) {
      $this->file= $file;
    }
    
    #[@xmlmapping(element= '@url')]
    public function setUrl($url) {
      $this->url= $url;
    }
    
    #[@xmlmapping(element= '@environment')]
    public function setEnvironment($env) {
      $this->environment= $env;
    }
    
    #[@xmlmapping(element= '@prefix')]
    public function setPrefix($p) {
      $this->prefix= $p;
    }
    
    public function setUp() {
      // TBI
    }
    
    protected function readProperties($prop) {
      $prefix= $prefix ? rtrim($this->valueOf($prefix), '.').'.' : '';
      $section= $prop->getFirstSection();
      while ($section) {
        foreach ($prop->readSection($section) as $name => $value) {
          $this->env()->put($prefix.$section.'.'.$name, $this->valueOf($value));
        }
        $section= $prop->getNextSection();
      }
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    protected function execute() {
      if (NULL !== $this->name) {
        // Properties may be declared twice, first occurrence wins
        if ($this->env()->exists($this->name)) return;
        
        if (NULL !== $this->value) {
          $this->env()->put($this->name, $this->valueOf($this->value));
        } else if (NULL !== $this->refid) {
          // TBI
        } else if (NULL !== $this->location) {
          $this->env()->put($this->name, realpath($this->uriOf($this->value)));
        }
        
        return;
      } else if (NULL !== $this->file) {
        $prop= new Properties($this->uriOf($this->file));
        $this->readProperties($prop);
      } else if (NULL !== $this->url) {
        $data= Properties::fromString(HttpUtil::get(new HttpConnection(new URL($this->valueOf($this->url)))));
        return $this->readProperties($prop);
      } else if (NULL !== $this->environment) {
        foreach ($_SERVER as $name => $value) {
          $this->env()->put($this->environment.'.'.strtolower($name), $value);
        }
      } else {
        throw new IllegalArgumentException('<property> task misconfigured');
      }
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function toString() {
      return $this->getClassName().'@('.$this->hashCode().') { '.$this->name.'= '.$this->value.' }';
    }    
  }
?>
