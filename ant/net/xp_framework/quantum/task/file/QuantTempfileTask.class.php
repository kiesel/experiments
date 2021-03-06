<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'net.xp_framework.quantum.task.QuantTask',
    'lang.System'
  );

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class QuantTempfileTask extends QuantTask {
    public
      $property = NULL,
      $destdir  = NULL,
      $prefix   = NULL,
      $suffix   = NULL;
      
    #[@xmlmapping(element= '@destdir')]
    public function setDestDir($dir) {
      $this->destdir= $dir;
    }
    
    #[@xmlmapping(element= '@property')]
    public function setProperty($p) {
      $this->property= $p;
    }
    
    #[@xmlmapping(element= '@prefix')]
    public function setPrefix($p) {
      $this->prefix= $p;
    }
    
    #[@xmlmapping(element= '@suffix')]
    public function setSuffix($s) {
      $this->suffix= $s;
    }
    
    protected function getDestDir() {
      if (NULL === $this->destdir) return System::tempDir();
      return $this->uriOf($this->destdir);
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    protected function execute() {
      $tempfile= tempnam($this->getDestDir(), $this->prefix.uniqid((double)microtime()));
      $this->env()->put($this->property, $tempfile);
    }
  }
?>
