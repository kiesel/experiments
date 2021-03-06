<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */

  uses(
    'io.IOException',
    'io.File',
    'util.Hashmap',
    'util.PropertiesReader',
    'util.PropertiesWriter',
    'io.streams.MemoryInputStream',
    'io.streams.TextReader',
    'io.streams.TextWriter'
  );
  
  /**
   * An interface to property-files (aka "ini-files")
   *
   * Property-files syntax is easy.
   * <pre>
   * [section]
   * key1=value
   * key2="value"
   * key3="value|value|value"
   * key4="a:value|b:value"
   * ; comment
   *
   * [section2]
   * key=value
   * </pre>
   *
   * @test      xp://net.xp_framework.unittest.util.PropertiesTest
   * @purpose   Wrapper around parse_ini_file
   */
  class Properties extends Object {
    public
      $_file    = '',
      $_data    = NULL;
      
    /**
     * Constructor
     *
     * @param   string filename
     */
    public function __construct($filename) {
      $this->_file= $filename;
    }
    
    /**
     * Create a property file from an io.File object
     *
     * @param   io.File file
     * @return  util.Properties
     * @throws  io.IOException in case the file given does not exist
     */
    public static function fromFile(File $file) {
      if (!$file->exists()) {
        throw new IOException('The file "'.$file->getURI().'" could not be read');
      }
      return new self($file->getURI());
    }

    /**
     * Create a property file from a string
     *
     * @param   string str
     * @return  util.Properties
     */
    public static function fromString($str) {
      $prop= new self(NULL);
      return create(new PropertiesReader(new TextReader(new MemoryInputStream($str), 'iso-8859-1')))->readInto($prop);
    }
    
    /**
     * Retrieves the file name containing the properties
     *
     * @return  string
     */
    public function getFilename() {
      return $this->_file;
    }
    
    /**
     * Create the property file
     *
     * @throws  io.IOException if the property file could not be created
     */
    public function create() {
      $fd= new File($this->_file);
      $fd->open(FILE_MODE_WRITE);
      $fd->close();
    }
    
    /**
     * Returns whether the property file exists
     *
     * @return  bool
     */
    public function exists() {
      return file_exists($this->_file);
    }
    
    /**
     * Helper method that loads the data from the file if needed
     *
     * @param   bool force default FALSE
     * @throws  io.IOException
     */
    protected function _load($force= FALSE) {
      if (!$force && NULL !== $this->_data) return;
      
      create(new PropertiesReader(
        new TextReader(create(new File($this->_file))->getInputStream(), 'iso-8859-1') 
      ))->readInto($this);
    }
    
    /**
     * Save properties to the file
     *
     * @deprecated Use saveToWriter instead
     * @throws  io.IOException if the property file could not be written
     */
    public function save() {
      $fd= new File($this->_file);
      $fd->open(FILE_MODE_WRITE);
      
      $this->saveToWriter(new TextWriter($fd->getOutputStream(), 'iso-8859-1'));
      $fd->close();
    }
    
    /**
     * Save properties to writer
     * 
     * @param 	io.streams.Writer writer
     */
    public function saveToWriter(TextWriter $writer) {
      create(new PropertiesWriter($writer))->writeFrom($this);
    }

    /**
     * Reload all data from the file
     *
     */
    public function reset() {
      return $this->_load(TRUE);
    }
    
    /**
     * Get the first configuration section
     *
     * @see     xp://util.Properties#getNextSection
     * @return  string the first section's name
     */
    public function getFirstSection() {
      $this->_load();
      reset($this->_data);
      return key($this->_data);
    }
    
    /**
     * Get the next configuration section
     *
     * Example:
     * <code>
     *   if ($section= $prop->getFirstSection()) do {
     *     var_dump($section, $prop->readSection($section));
     *   } while ($section= $prop->getNextSection());
     * </code>
     *
     * @see     xp://util.Properties#getFirstSection
     * @return  var string section or FALSE if this was the last section
     */
    public function getNextSection() {
      $this->_load();
      if (FALSE === next($this->_data)) return FALSE;

      return key($this->_data);
    }
    
    /**
     * Read an entire section into an array
     *
     * @param   string name
     * @param   var[] default default array() what to return in case the section does not exist
     * @return  array
     */
    public function readSection($name, $default= array()) {
      $this->_load();
      return isset($this->_data[$name]) 
        ? $this->_data[$name] 
        : $default
      ;
    }
    
    /**
     * Read a value as string
     *
     * @param   string section
     * @param   string key
     * @param   string default default '' what to return in case the section or key does not exist
     * @return  string
     */ 
    public function readString($section, $key, $default= '') {
      $this->_load();
      return isset($this->_data[$section][$key])
        ? $this->_data[$section][$key]
        : $default
      ;
    }
    
    /**
     * Read a value as array
     *
     * @param   string section
     * @param   string key
     * @param   var[] default default NULL what to return in case the section or key does not exist
     * @return  array
     */
    public function readArray($section, $key, $default= array()) {
      $this->_load();
      
      // Check if array was exploded
      if (is_array($this->_data[$section][$key])) return $this->_data[$section][$key];
      
      return isset($this->_data[$section][$key])
        ? '' == $this->_data[$section][$key] ? array() : explode('|', $this->_data[$section][$key])
        : $default
      ;
    }
    
    /**
     * Read a value as hash
     *
     * @param   string section
     * @param   string key
     * @param   util.Hashmap default default NULL what to return in case the section or key does not exist
     * @return  util.Hashmap
     */
    public function readHash($section, $key, $default= NULL) {
      $this->_load();
      if (!isset($this->_data[$section][$key])) return $default;
      
      if (is_array($this->_data[$section][$key])) return new Hashmap($this->_data[$section][$key]);
      
      $return= array();
      foreach (explode('|', $this->_data[$section][$key]) as $val) {
        if (strstr($val, ':')) {
          list($k, $v)= explode(':', $val, 2);
          $return[$k]= $v;
        } else {
          $return[]= $val;
        } 
      }
      
      return new Hashmap($return);
    }

    /**
     * Read a value as range
     *
     * @param   string section
     * @param   string key
     * @param   int[] default default NULL what to return in case the section or key does not exist
     * @return  array
     */
    public function readRange($section, $key, $default= array()) {
      $this->_load();
      if (!isset($this->_data[$section][$key])) return $default;
      
      list($min, $max)= explode('..', $this->_data[$section][$key]);
      return range((int)$min, (int)$max);
    }
    
    /**
     * Read a value as integer
     *
     * @param   string section
     * @param   string key
     * @param   int default default 0 what to return in case the section or key does not exist
     * @return  int
     */ 
    public function readInteger($section, $key, $default= 0) {
      $this->_load();
      return isset($this->_data[$section][$key])
        ? intval($this->_data[$section][$key])
        : $default
      ;
    }

    /**
     * Read a value as float
     *
     * @param   string section
     * @param   string key
     * @param   float default default 0.0 what to return in case the section or key does not exist
     * @return  float
     */ 
    public function readFloat($section, $key, $default= 0.0) {
      $this->_load();
      return isset($this->_data[$section][$key])
        ? doubleval($this->_data[$section][$key])
        : $default
      ;
    }

    /**
     * Read a value as boolean
     *
     * @param   string section
     * @param   string key
     * @param   bool default default FALSE what to return in case the section or key does not exist
     * @return  bool TRUE, when key is 1, 'on', 'yes' or 'true', FALSE otherwise
     */ 
    public function readBool($section, $key, $default= FALSE) {
      $this->_load();
      if (!isset($this->_data[$section][$key])) return $default;
      return (
        '1'     === $this->_data[$section][$key] ||
        'yes'   === $this->_data[$section][$key] ||
        'true'  === $this->_data[$section][$key] ||
        'on'    === $this->_data[$section][$key]
      );
    }
    
    /**
     * Returns whether a section exists
     *
     * @param   string name
     * @return  bool
     */
    public function hasSection($name) {
      $this->_load();
      return isset($this->_data[$name]);
    }

    /**
     * Add a section
     *
     * @param   string name
     * @param   bool overwrite default FALSE whether to overwrite existing sections
     * @return  string name
     */
    public function writeSection($name, $overwrite= FALSE) {
      if ($overwrite || !isset($this->_data[$name])) $this->_data[$name]= array();
      return $name;
    }
    
    /**
     * Add a string (and the section, if necessary)
     *
     * @param   string section
     * @param   string key
     * @param   string value
     */
    public function writeString($section, $key, $value) {
      if (!isset($this->_data[$section])) $this->_data[$section]= array();
      $this->_data[$section][$key]= (string)$value;
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function addHashElement($section, $key, $element, $value) {
      if (!isset($this->_data[$section][$key])) $this->_data[$section][$key]= array();
      $this->_data[$section][$key][$element]= (string)$value;
    }
    
    /**
     * Add a string (and the section, if necessary)
     *
     * @param   string section
     * @param   string key
     * @param   int value
     */
    public function writeInteger($section, $key, $value) {
      if (!isset($this->_data[$section])) $this->_data[$section]= array();
      $this->_data[$section][$key]= (int)$value;
    }
    
    /**
     * Add a float (and the section, if necessary)
     *
     * @param   string section
     * @param   string key
     * @param   float value
     */
    public function writeFloat($section, $key, $value) {
      if (!isset($this->_data[$section])) $this->_data[$section]= array();
      $this->_data[$section][$key]= (float)$value;
    }

    /**
     * Add a boolean (and the section, if necessary)
     *
     * @param   string section
     * @param   string key
     * @param   bool value
     */
    public function writeBool($section, $key, $value) {
      if (!isset($this->_data[$section])) $this->_data[$section]= array();
      $this->_data[$section][$key]= $value ? 'yes' : 'no';
    }
    
    /**
     * Add an array string (and the section, if necessary)
     *
     * @param   string section
     * @param   string key
     * @param   array value
     */
    public function writeArray($section, $key, $value) {
      if (!isset($this->_data[$section])) $this->_data[$section]= array();
      $this->_data[$section][$key]= $value;
    }
    
    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function addArrayElement($section, $key, $value) {
      if (!isset($this->_data[$section])) $this->_data[$section]= array();
      $this->_data[$section][$key][]= $value;
    }

    /**
     * Add a hashmap (and the section, if necessary)
     *
     * @param   string section
     * @param   string key
     * @param   var value either a util.Hashmap or an array
     */
    public function writeHash($section, $key, $value) {
      if (!isset($this->_data[$section])) $this->_data[$section]= array();
      if ($value instanceof Hashmap) {
        $this->_data[$section][$key]= $value;
      } else {
        $this->_data[$section][$key]= new Hashmap($value);
      }
    }
    
    /**
     * Add a comment (and the section, if necessary)
     *
     * @param   string section
     * @param   string key
     * @param   string comment
     */
    public function writeComment($section, $comment) {
      if (!isset($this->_data[$section])) $this->_data[$section]= array();
      $this->_data[$section][';'.sizeof($this->_data[$section])]= $comment;
    }
    
    /**
     * Creates a string representation of this property file
     *
     * @return  string
     */
    public function toString() {
      return $this->getClassName().'('.$this->_file.')@{'.xp::stringOf($this->_data).'}';
    }
  }
?>
