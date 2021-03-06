<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class PDFObject extends Object {
    var
      $generation=    0,
      $number=        -1;
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function __construct($number= -1) {
      $this->number= $number;
    }

    /**
     * Set Generation
     *
     * @access  public
     * @param   int generation
     */
    function setGeneration($generation) {
      $this->generation= $generation;
    }

    /**
     * Get Generation
     *
     * @access  public
     * @return  int
     */
    function getGeneration() {
      return $this->generation;
    }

    /**
     * Set Number
     *
     * @access  public
     * @param   int number
     */
    function setNumber($number= 0) {
      $this->number= $number;
    }

    /**
     * Get Number
     *
     * @access  public
     * @return  int
     */
    function getNumber() {
      return $this->number;
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function getReference() {
      if (0 > $this->number) return throw(new IllegalStateException(
        'Object has not yet been registered in the document. Cannot create reference.'
      ));
      return $this->number.' '.$this->generation.' R';
    }
    
    function getObjectDeclaration() {
      return $this->number.' '.$this->generation." obj\n<< ";
    }
    
    function getObjectEndDeclaration() {
      return ">>\nendobject\n";
    }
    
    function toPDF() {
      return $this->getClassName()."::toPDF()\n";
    }

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function output(&$stream) {
      $data= $this->toPDF();
      $stream->write($data);
      return strlen($data);
    }
  }
?>
