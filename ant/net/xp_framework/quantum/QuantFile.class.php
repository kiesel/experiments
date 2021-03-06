<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'io.collections.IOElement'
  );

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class QuantFile extends Object implements IOElement {
    public
      $file     = NULL,
      $base     = NULL;
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function __construct(FileElement $element, $base) {
      $this->file= $element;
      $this->base= $base;
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function relativePath() {
      return substr($this->file->getURI(), strlen($this->base)+ 1);
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function getURI() {
      return $this->file->getURI();
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function toString() {
      return $this->relativePath().' @ <'.$this->base.'>';
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function getSize() {
      return $this->file->getSize();
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function createdAt() {
      return $this->file->createdAt();
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function lastAccessed() {
      return $this->file->lastAccessed();
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function lastModified() {
      return $this->file->lastModified();
    }    
  }
?>
