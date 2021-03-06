<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'io.File',
    'io.TempFile',
    'net.xp_framework.quantum.task.DirectoryBasedTask',
    'lang.Process'
  );

  /**
   * (Insert class' description here)
   *
   * @see      reference
   * @purpose  Task
   */
  class QuantJavacTask extends DirectoryBasedTask {
    public
      $destdir= NULL,
      $classpath= NULL,
      $sourcepaths= NULL,
      $bootclasspath= NULL,
      $classpathref= NULL,
      $sourcepathref= NULL,
      $bootclasspathref= NULL,
      $extdirs= NULL,
      $encoding= NULL,
      $nowarn= FALSE,
      $debug= FALSE,
      $debuglevel= NULL,
      $optimize= NULL,
      $deprecation= NULL,
      $target= NULL,
      $verbose= FALSE,
      $depend= NULL,
      $includeQuantRuntime= TRUE,
      $includeJavaRuntime= FALSE,
      $fork= TRUE,
      $executable= 'javac',
      $memoryInitialSize= NULL,
      $memoryMaximumSize= NULL,
      $failonerror= TRUE,
      $source= NULL,
      $listfiles= FALSE,
      $tempdir= NULL;

    /**
     * (Insert method's description here)
     *
     * @param   
     * @return  
     */
    public function __construct() {
      parent::__construct();
      $this->fileset->addIncludePatternString('**/*.java');
    }

    /**
     * Set Srcdir
     *
     * @access  public
     * @param   lang.Object srcdir
     */
    #[@xmlmapping(element= '@srcdir')]
    function setSrcdir($srcdir) {
      $this->fileset->setDir($srcdir);
    }

    /**
     * Get Srcdir
     *
     * @access  public
     * @return  lang.Object
     */
    function getSrcdir(QuantEnvironment $env) {
      return $env->localUri($env->substitute($this->srcdir));
    }

    /**
     * Set Destdir
     *
     * @access  public
     * @param   lang.Object destdir
     */
    #[@xmlmapping(element= '@destdir')]
    function setDestdir($destdir) {
      $this->destdir= $destdir;
    }

    /**
     * Get Destdir
     *
     * @access  public
     * @return  lang.Object
     */
    function getDestdir(QuantEnvironment $env) {
      return $env->localUri($env->substitute($this->destdir));
    }

    /**
     * Set Classpath
     *
     * @access  public
     * @param   lang.Object classpath
     */
    #[@xmlmapping(element= '@classpath')]
    function setClasspath($classpath) {
      $this->classpath= $classpath;
    }

    /**
     * Get Classpath
     *
     * @param   net.xp_framework.quantum.QuantEnvironment env
     * @return  string[]
     */
    function getClasspath(QuantEnvironment $env) {
      if ($this->classpathref) {
        return $env->getPath($this->classpathref);
      }
      
      return $env->localUri($env->substitute($this->classpath));
    }

    /**
     * Add sourcepath
     *
     * @access  public
     * @param   lang.Object sourcepath
     */
    #[@xmlmapping(element= '@sourcepath|src/@path')]
    function addSourcepath($sourcepath) {
      $this->sourcepaths[]= $sourcepath;
    }

    /**
     * Get sourcepath
     *
     * @access  public
     * @return  string[]
     */
    function getSourcepath(QuantEnvironment $env) {
      if ($this->sourcepathref) {
        return $env->getPath($this->sourcepathref);
      }
      
      $r= array();
      foreach ($this->sourcepaths as $sourcepath) {
        $r[]= $env->localUri($env->substitute($sourcepath));
      }
      return $r;
    }

    /**
     * Set Classpathref
     *
     * @access  public
     * @param   lang.Object classpathref
     */
    #[@xmlmapping(element= '@classpathref|classpath/@refid')]
    function setClasspathref($classpathref) {
      $this->classpathref= $classpathref;
    }

    /**
     * Set Sourcepathref
     *
     * @access  public
     * @param   lang.Object sourcepathref
     */
    #[@xmlmapping(element= '@sourcepathref')]
    function setSourcepathref($sourcepathref) {
      $this->sourcepathref= $sourcepathref;
    }

    /**
     * Set Bootclasspathref
     *
     * @access  public
     * @param   lang.Object bootclasspathref
     */
    function setBootclasspathref($bootclasspathref) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Set Extdirs
     *
     * @access  public
     * @param   lang.Object extdirs
     */
    function setExtdirs($extdirs) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Get Extdirs
     *
     * @access  public
     * @return  lang.Object
     */
    function getExtdirs() {
      return $this->extdirs;
    }

    /**
     * Set Encoding
     *
     * @access  public
     * @param   lang.Object encoding
     */
    #[@xmlmapping(element= '@encoding')]
    function setEncoding($encoding) {
      $this->encoding= $encoding;
    }

    /**
     * Set Nowarn
     *
     * @access  public
     * @param   lang.Object nowarn
     */
    #[@xmlmapping(element= '@nowarn')]
    function setNowarn($nowarn) {
      $this->nowarn= ($nowarn == 'off');
    }

    /**
     * Set Debug
     *
     * @access  public
     * @param   lang.Object debug
     */
    function setDebug($debug) {
      $this->debug= ($debug == 'true');
    }

    /**
     * Set Debuglevel
     *
     * @access  public
     * @param   lang.Object debuglevel
     */
    function setDebuglevel($debuglevel) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Set Optimize
     *
     * @access  public
     * @param   lang.Object optimize
     */
    function setOptimize($optimize) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Set Deprecation
     *
     * @access  public
     * @param   lang.Object deprecation
     */
    function setDeprecation($deprecation) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Set Target
     *
     * @access  public
     * @param   lang.Object target
     */
    function setTarget($target) {
      if (!in_array($target, array('1.1', '1.2', '1.3', '1.4', '1.5', '1.6')))
        throw new IllegalArgumentException('No such compiler target: "'.$target.'"');

      $this->target= $target;
    }

    /**
     * Set Verbose
     *
     * @access  public
     * @param   lang.Object verbose
     */
    #[@xmlmapping(element= '@verbose')]
    function setVerbose($verbose) {
      $this->verbose= ($verbose == 'yes');
    }

    /**
     * Set Depend
     *
     * @access  public
     * @param   lang.Object depend
     */
    #[@xmlmapping(element= '@depend')]
    function setDepend($depend) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Set IncludeQuantRuntime
     *
     * @access  public
     * @param   lang.Object includeQuantRuntime
     */
    #[@xmlmapping(element= '@includeQuantRuntime')]
    function setIncludeQuantRuntime($includeQuantRuntime) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Set IncludeJavaRuntime
     *
     * @access  public
     * @param   lang.Object includeJavaRuntime
     */
    #[@xmlmapping(element= '@depend')]
    function setIncludeJavaRuntime($includeJavaRuntime) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Set Fork
     *
     * @access  public
     * @param   lang.Object fork
     */
    #[@xmlmapping(element= '@depend')]
    function setFork($fork) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Set Executable
     *
     * @access  public
     * @param   lang.Object executable
     */
    #[@xmlmapping(element= '@executable')]
    function setExecutable($executable) {
      $this->executable= $executable;
    }

    /**
     * Get Executable
     *
     * @access  public
     * @return  lang.Object
     */
    function getExecutable() {
      return $this->executable;
    }

    /**
     * Set MemoryInitialSize
     *
     * @access  public
     * @param   lang.Object memoryInitialSize
     */
    #[@xmlmapping(element= '@memoryInitialSize')]
    function setMemoryInitialSize($memoryInitialSize) {
      $this->memoryInitialSize= $memoryInitialSize;
    }

    /**
     * Set MemoryMaximumSize
     *
     * @access  public
     * @param   lang.Object memoryMaximumSize
     */
    #[@xmlmapping(element= '@memoryMaximumSize')]
    function setMemoryMaximumSize($memoryMaximumSize) {
      $this->memoryMaximumSize= $memoryMaximumSize;
    }

    /**
     * Set Failonerror
     *
     * @access  public
     * @param   lang.Object failonerror
     */
    #[@xmlmapping(element= '@depend')]
    function setFailonerror($failonerror) {
      $this->failonerror= ('true' == $failonerror);
    }

    /**
     * Set Source
     *
     * @access  public
     * @param   lang.Object source
     */
    #[@xmlmapping(element= '@source')]
    function setSource($source) {
      if (!in_array($target, array('1.3', '1.4')))
        throw new IllegalArgumentException('No such compiler source: "'.$source.'"');

      $this->source= $source;
    }

    /**
     * Set Compiler
     *
     * @access  public
     * @param   lang.Object compiler
     */
    #[@xmlmapping(element= '@depend')]
    function setCompiler($compiler) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Set Listfiles
     *
     * @access  public
     * @param   lang.Object listfiles
     */
    #[@xmlmapping(element= '@listfiles')]
    function setListfiles($listfiles) {
      $this->listfiles= ('yes' == $listfiles);
    }

    /**
     * Set Tempdir
     *
     * @access  public
     * @param   lang.Object tempdir
     */
    #[@xmlmapping(element= '@tempdir')]
    function setTempdir($tempdir) {
      throw new MethodNotImplementedException(__FUNCTION__.' not implemented');
    }

    /**
     * Execute this task
     *
     */
    protected function execute() {
      $env= $this->env();
      $cmdline= array();
      if ($this->destdir) $cmdline[]= '-d '.$this->getDestdir($env);

      // Compile classpath by merging destination dir (if set) and classpath
      // and passing it as "-classpath" argument to the Java compiler
      if ($this->classpath || $this->classpathref) $cmdline[]= sprintf(
        '-classpath "%s%s"',
        $this->destdir ? $this->getDestdir($env).$env->pathSeparator() : '',
        implode($env->pathSeparator(), $this->getClasspath($env))
      );

      if ($this->encoding) $cmdline[]= '-encoding '.$this->encoding;
      if ($this->debug) $cmdline[]= '-g';
      if (TRUE === $this->nowarn) $cmdline[]= '-nowarn';
      if ($this->source) $cmdline[]= '-source '.$this->source;
      if ($this->sourcepaths || $this->sourcepathref) $cmdline[]= '-sourcepath "'.implode($env->pathSeparator(), $this->getSourcepath($env)).'"';
      if (TRUE === $this->verbose) $cmdline[]= '-verbose';
      
      // Evaluate fileset
      $iterator= $this->iteratorForFileset($env);
      $compileable= array();
      
      $destdir= $this->getDestdir($env);
      while ($iterator->hasNext()) {
        $sourceElement= $iterator->next();
        $targetfile= new File($destdir.$env->directorySeparator().str_replace('.java', '.class', $sourceElement->relativePath()));
        
        // Rebuild when target does not exist or is out of date
        if (
          !$targetfile->exists() ||
          $sourceElement->lastModified()->getTime() > $targetfile->lastModified()
        ) {
          $compileable[]= $sourceElement;
          continue;
        }
      }
      
      if (0 == sizeof($compileable)) return;
      
      $lfile= new TempFile();
      $lfile->open(FILE_MODE_WRITE);
      foreach ($compileable as $compile) {
        if ($this->listfiles) $env->out->writeLine('Compiling '.$compile->relativePath());
        $lfile->writeLine($compile->getURI());
      }
      $lfile->close();
      
      $cmdline[]= '@'.escapeshellarg($lfile->getURI());
      try {
        $p= new Process($this->getExecutable(), $cmdline);
        $env->out->writeLine('---> Executing '.$p->getCommandLine());
        $p->getInputStream()->close();
        
        while (/*($out= $p->getOutputStream()->readLine()) ||*/ ($err= $p->getErrorStream()->readLine())) {
          if ($out) $env->out->writeLine('[STDOUT:javac] '.$out); 
          if ($err) $env->err->writeLine('[STDERR:javac] '.$err);
        }
        $p->close();
      } catch (IOException $e) {
        if ($this->failOnError) {
          $lfile->unlink();  
          throw $e;
        }
        
        $env->err->writeLine($e->toString());
      }
      
      $lfile->unlink();
    }
  }
?>
