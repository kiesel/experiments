<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'net.xp_framework.quantum.task.QuantTask',
    'peer.http.HttpConnection',
    'util.profiling.Timer'
  );

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class QuantGetTask extends QuantTask {
    protected
      $src          = NULL,
      $dest         = NULL,
      $verbose      = FALSE,
      $ignoreErrors = FALSE,
      $useTimestamp = FALSE,
      $username     = NULL,
      $password     = NULL;
    
    #[@xmlmapping(element= '@src')]
    public function setSrc($s) {
      $this->src= $s;
    }
    
    #[@xmlmapping(element= '@dest')]
    public function setDest($d) {
      $this->dest= $d;
    }
    
    #[@xmlmapping(element= '@verbose')]
    public function setVerbose($v) {
      $this->verbose= ('true' === $v);
    }
    
    #[@xmlmapping(element= '@ignoreErrors')]
    public function setIgnoreErrors($e) {
      $this->ignoreErrors= ('true' === $e);
    }
    
    #[@xmlmapping(element= '@useTimestamp')]
    public function setUseTimestamp($t) {
      $this->useTimestamp= ('true' === $t);
    }
    
    #[@xmlmapping(element= '@username')]
    public function setUsername($u) {
      $this->username= $u;
    }
    
    #[@xmlmapping(element= '@password')]
    public function setPassword($p) {
      $this->password= $p;
    }
    
    public function execute() {
      if (!$this->dest) throw new IllegalArgumentException('Destination must be given in <get>');
      $url= $this->valueOf($this->src);
      $c= new HttpConnection($url);
      
      $headers= array();
      if (NULL !== $this->username) {
        $headers[]= new BasicAuthentication($this->username, $this->password);
      }
      
      $dest= new File($uriOf($this->dest));
      if (
        TRUE === $this->useTimestamp &&
        $dest->exists()
      ) {
        $headers['If-Modified-Since']= create(new Date($dest->lastModified()))->toString(DATE_COOKIE);
      }
      
      $timer= new Timer();
      $timer->start();
      $this->verbose && $this->env()->out->writeLine('Fetching '.$url.' ...');
      
      $response= $c->get(NULL, $headers);
      switch ($response->getStatusCode()) {
        case HTTP_NOT_MODIFIED: {
          $this->verbose && $this->env()->out->writeLine('Local copy of '.$url.' still up-to-date.');
          return;
        }
        case HTTP_OK: {
          $dest->open(FILE_MODE_WRITE);
          
          while (FALSE !== ($buf= $response->readData(8192, TRUE))) {
            $dest->write($buf);
            unset($buf);
          }
          
          $dest->close();
          $timer->stop();
          $this->verbose && $this->env()->out->writeLinef('Successfully fetched %s - retrieved %dkb in %0.2f sec.',
            $url,
            intval($dest->size() / 1024),
            $timer->elapsedTime()
          );
          return;
        }
        
        default: {
          $this->env()->err->writeLine('Could not fetch URL '.$url.', HTTP response code: '.$response->getStatusCode());
          if (TRUE === $this->ignoreErrors) return; 

          throw new IllegalStateException('Could not download from '.$url.', HTTP response code: '.$response->getStatusCode());
        }
      }
    }
  }
?>
