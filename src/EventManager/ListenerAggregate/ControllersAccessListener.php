<?php
namespace Bricks\Acl\EventManager\ListenerAggregate;

use Closure;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;
use Zend\Permissions\Acl\AclInterface;
use Zend\Permissions\Acl\Resource\GenericResource as AclResource;
use Zend\View\Model\ViewModel;

/**
 * @author Artur Sh. Mamedbekov
 */
class ControllersAccessListener extends AbstractListenerAggregate{
  /**
   * @var Closure
   */
  private $currentRoleGetter;
  
  /**
   * @param Closure $currentRoleGetter
   */
  public function __construct(Closure $currentRoleGetter){
    $this->currentRoleGetter = $currentRoleGetter;
  }

  /**
   * {@inheritdoc}
   */
  public function attach(EventManagerInterface $events, $priority = 1){
    $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute']);
  }

  /**
   * @param EventInterface $e
   */
  public function onRoute(EventInterface $e){
    $locator = $e->getApplication()->getServiceManager();
    $acl = $locator->get(AclInterface::class);
    $routeMatch = $e->getRouteMatch();
    $controller = $routeMatch->getParam('controller', '');
    $action = $routeMatch->getParam('action', '');
    if(!$acl->hasResource($controller)){
      $acl->addResource(new AclResource($controller), 'controller');
    }

    $currentRole = call_user_func($this->currentRoleGetter);
    if(!$acl->isAllowed($currentRole, $controller, $action)){
      $e->setError('Access is denied');
      $e->setViewModel((new ViewModel)->setTemplate('error/403'));
    }
  }
}
