<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('net.xp_framework.quantum.task.DirectoryBasedTask');

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class QuantCopyTask extends DirectoryBasedTask {
    public
      $file                 = NULL,
      $toFile               = NULL,
      $toDir                = NULL,
      $overwrite            = FALSE,
      $flatten              = FALSE,
      $includeEmptyDirs     = FALSE,
      $failOnError          = TRUE,
      $verbose              = FALSE,
      $preserveLastmodified = FALSE;
          
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    #[@xmlmapping(element= '@file')]
    public function setFile($f) {
      $this->file= $f;
    }    
     
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    #[@xmlmapping(element= '@toFile')]
    public function setToFile($f) {
      $this->toFile= $f;
    }    
   
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    #[@xmlmapping(element= '@toDir')]
    public function setToDir($d) {
      $this->toDir= $d;
    }    
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    #[@xmlmapping(element= '@overwrite')]
    public function setOverwrite($o) {
      $this->overwrite= ($o == 'true');
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    #[@xmlmapping(element= '@flatten')]
    public function setFlatten($f) {
      $this->flatten= ($f == 'true');
    }
    
    #[@xmlmapping(element= '@preservelastmodified')]
    public function setPreserveLastmodified($m) {
      $this->preserveLastmodified= ('true' == $m);
    }
    
    protected function copy($source, $target= NULL) {
      $s= new File($this->uriOf($source));
      
      if (NULL === $target) {
        $target= $this->uriOf($this->toDir.'/'.($this->flatten ? basename($source) : $source));
      }
      $t= new File($target);
      
      // Only perform if target does not exist, or is outdated compared to
      // original, or overwrite mode is enabled.
      if (
        $t->exists() && 
        $t->lastModified()- $s->lastModified() < 2 &&
        !$this->overwrite
      ) return;

      if ($this->verbose) {
        $this->env()->out->writeLine('===> Copy '.$s->getURI().' to '.$t->getURI());
      }
      
      $s->copy($t->getURI());
      
      if (TRUE === $this->preserveLastmodified) {
        $t->touch($s->lastModified());
      }
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    protected function execute() {
      if (NULL !== $this->file) {
        if (NULL === $this->toFile) throw new IllegalArgumentException('file given, but toFile is missing.');

        $this->copy($this->file, $this->uriOf($this->toFile));
        return;
      }

      $iter= $this->fileset->iteratorFor($this->env());
      while ($iter->hasNext()) {
        $element= $iter->next();
        
        $this->copy($element->relativePath());
      }
    }    
  }
?>
