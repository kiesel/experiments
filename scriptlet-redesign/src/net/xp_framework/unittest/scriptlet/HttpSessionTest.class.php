<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('scriptlet.HttpSession', 'unittest.TestCase', 'util.Date', 'io.Folder');

  /**
   * TestCase for scriptlet.HttpSession class.
   *
   * @purpose   TestCase
   */
  class HttpSessionTest extends TestCase {
    protected $session= NULL;

    /**
     * Helper method to create the testing session object.
     *
     * @return  scriptlet.HttpSession
     */
    protected function _session() {
      return new HttpSession();
    }

    /**
     * Setup testcase environment for next testcase
     *
     */
    public function setUp() {
      $this->session= $this->_session();
    }

    /**
     * Cleanup last testcase run. Invalidate old sessions and
     * remove environment leftovers
     *
     */
    public function tearDown() {
      if (is('scriptlet.HttpSession', $this->session) && $this->session->isValid()) {
        $this->session->invalidate();
      }
    }

    /**
     * Session mess: Set session save path
     *
     */
    #[@beforeClass]
    public static function setSessionSavePath() {
      session_save_path(getcwd());
    }

    /**
     * Session mess: Cleanup session save path
     *
     */
    #[@afterClass]
    public static function cleanupSessionSavePath() {
      $f= new Folder(session_save_path());
      while ($e= $f->getEntry()) {
        if (0 === strncmp('sess_', $e, 5)) unlink($f->getURI().$e);
      }
    }

    /**
     * Test session creation
     *
     */
    #[@test]
    public function testCreate() {
      $this->session->initialize(NULL);
      $this->assertTrue($this->session->isValid());
    }

    /**
     * Test isNew() method
     *
     */
    #[@test]
    public function testNew() {
      $this->session->initialize(NULL);
      $this->assertTrue($this->session->isNew());
    }

    /**
     * Test reattaching of sessions
     *
     */
    #[@test]
    public function reattach() {
      $this->session->initialize(NULL);

      $copy= new HttpSession();
      $copy->initialize($this->session->getId());
      $this->assertFalse($copy->isNew());
    }

    /**
     * Test invalidating of session
     *
     */
    #[@test]
    public function invalidate() {
      $this->session->initialize(NULL);
      $this->assertTrue($this->session->isValid());

      $this->session->invalidate();
      $this->assertFalse($this->session->isValid());
    }

    /**
     * Test fetching of registered session keys
     *
     */
    #[@test]
    public function valueNames() {
      $this->session->initialize(NULL);
      $this->session->putValue('foo', $f= 1);
      $this->session->putValue('bar', $f= 2);

      $this->assertEquals(
        array('foo', 'bar'),
        $this->session->getValueNames()
      );
    }

    /**
     * Test fetching of registered session keys
     *
     */
    #[@test]
    public function putDoesNotOverwriteValue() {
      $this->session->initialize(NULL);
      $fixture= new Object();
      $hash= $fixture->hashCode();
      $this->session->putValue('foo', $fixture);
      $this->assertClass($fixture, 'lang.Object');
      $this->assertEquals($hash, $fixture->hashCode());
    }

    /**
     * Test resetting of sessions
     *
     */
    #[@test]
    public function testReset() {
      $this->session->initialize(NULL);
      $this->session->putValue('foo', $f= NULL);
      $this->assertEquals(1, sizeof($this->session->getValueNames()));

      $this->session->reset();
      $this->assertEquals(0, sizeof($this->session->getValueNames()));
    }

    /**
     * Test round trip
     *
     */
    #[@test]
    public function stringRoundtrip() {
      $this->session->initialize(NULL);
      $this->session->putValue('foo', 'FOO');
      $this->assertEquals('FOO', $this->session->getValue('foo'));
    }

    /**
     * Test round trip
     *
     */
    #[@test]
    public function intRoundtrip() {
      $this->session->initialize(NULL);
      $this->session->putValue('foo', 1);
      $this->assertEquals(1, $this->session->getValue('foo'));
    }

    /**
     * Test round trip
     *
     */
    #[@test]
    public function arrayRoundtrip() {
      $this->session->initialize(NULL);
      $this->session->putValue('foo', array(1, 2, 3));
      $this->assertEquals(array(1, 2, 3), $this->session->getValue('foo'));
    }

    /**
     * Test round trip
     *
     */
    #[@test]
    public function objectRoundtrip() {
      $this->session->initialize(NULL);
      $this->session->putValue('foo', new Date('1977-12-14'));
      $this->assertEquals(new Date('1977-12-14'), $this->session->getValue('foo'));
    }

    /**
     * Test session fixation protection (users may not pass
     * arbitrary names as session ids)
     *
     */
    #[@test, @ignore('Creates an unremovable file sess_ILLEGALSESSIONID')]
    public function testIllegalConstruct() {
      $this->assertFalse($this->session->initialize('ILLEGALSESSIONID'));
    }

    /**
     * Test access protection on invalid sessions
     *
     */
    #[@test, @ignore('Creates an unremovable file sess_ILLEGALSESSIONID'), @expect('lang.IllegalStateException')]
    public function testIllegalSessionAccess() {
      $this->session->initialize('ILLEGALSESSIONID');
      $this->session->putValue('foo', $f= 3);
    }
  }
?>
