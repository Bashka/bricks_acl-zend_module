<?php
namespace Bricks\Acl\ServiceManager\Factory;

use Zend\ServiceManager\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Permissions\Acl\AclInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as AclRole;
use Zend\Permissions\Acl\Resource\GenericResource as AclResource;

/**
 * @author Artur Sh. Mamedbekov
 */
class AclFactory implements FactoryInterface{
  /**
   * {@inheritdoc}
   */
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
    $options = $container->get('Configuration');
    $aclOptions = $options['acl'];

    $acl = new Acl;
    if(isset($aclOptions['roles'])){
      $this->setRoles($acl, $aclOptions['roles']);
    }
    if(isset($aclOptions['resources'])){
      $this->setResources($acl, $aclOptions['resources']);
    }
    if(isset($aclOptions['allows'])){
      $this->setAllow($acl, $aclOptions['allows'], $container);
    }
    if(isset($aclOptions['denies'])){
      $this->setDeny($acl, $aclOptions['denies'], $container);
    }

    return $acl;
  }
  
  /**
   * For v2.
   *
   * {@inheritdoc}
   */
  public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null){
    return $this($container, $requestedName?: ConverterInterface::class, []);
  }

  protected function setRoles(AclInterface &$acl, array $roles){
    foreach($roles as $role => $parents){
      if(is_int($role)){
        $role = $parents;
        $parents = [];
      }
      $acl->addRole(new AclRole($role), $parents);
    }
  }

  protected function setResources(AclInterface &$acl, array $resources){
    foreach($resources as $resource => $parent){
      if(is_int($resource)){
        $resource = $parent;
        $parent = null;
      }
      $acl->addResource(new AclResource($resource), $parent);
    }
  }

  protected function setAllow(AclInterface &$acl, array $allows, ContainerInterface $container){
    foreach($allows as $role => $params){
      if($role == '*'){
        $role = null;
      }
      $resource = null;
      $privilegies = null;
      $assert = null;
      if(is_string($params)){
        $resource = $params;
      }
      else{
        if(isset($params['resource'])){
          $resource = $params['resource'];
        }
        if(isset($params['privilegies'])){
          $privilegies = $params['privilegies'];
        }
        if(isset($params['assert'])){
          $assert = $container->get($params['assert']);
        }
      }

      $acl->allow($role, $resource, $privilegies, $assert);
    }
  }

  protected function setDeny(AclInterface &$acl, array $denies, ContainerInterface $container){
    foreach($denies as $role => $params){
      if($role == '*'){
        $role = null;
      }
      $resource = null;
      $privilegies = null;
      $assert = null;
      if(is_string($params)){
        $resource = $params;
      }
      else{
        if(isset($params['resource'])){
          $resource = $params['resource'];
        }
        if(isset($params['privilegies'])){
          $privilegies = $params['privilegies'];
        }
        if(isset($params['assert'])){
          $assert = $container->get($params['assert']);
        }
      }

      $acl->deny($role, $resource, $privilegies, $assert);
    }
  }
}
