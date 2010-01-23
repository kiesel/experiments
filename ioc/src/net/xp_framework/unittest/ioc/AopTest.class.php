<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'inject.IoC',
    'inject.AbstractModule',
    'Injectee'
  );
  
  /**
   * Testcase for DI container
   *
   */
  class AopTest extends TestCase {
    protected $fixtureClass= NULL;
    protected $injector= NULL;
  
    /**
     * Populates fixtureClass member with a unique name
     *
     */
    public function setUp() {
      $this->fixtureClass= __CLASS__.'��'.$this->name;
      $this->injector= IoC::getInjectorFor(newinstance('inject.AbstractModule', array(), '{
        protected function configure() {
          $this->bind(XPClass::forName("Injectee"))->toInstance(newinstance("Injectee", array(), "{
            public function getGreeting() { return \"Hello\"; }
          }"));
          $this->intercept("call:Injectee::getGreeting", newinstance("util.invoke.InvocationInterceptor", array(), "{
            public function invoke(InvocationChain \$chain) {
              // throw new IllegalStateException(__FUNCTION__);
              return \$chain->proceed();
            }
          }"));
        }
      }'));
    }
    
    /**
     * Tests constructor injection
     *
     */
    #[@test]
    public function constructorInjection() {
      ClassLoader::defineClass($this->fixtureClass, 'lang.Object', array(), '{
        #[@inject]
        public function __construct(Injectee $i) {
          $this->injectee= $i;
        }
      }');
      
      $instance= $this->injector->getInstance($this->fixtureClass);
      $this->assertEquals('Hello', $instance->injectee->getGreeting());
    }
  }
?>
