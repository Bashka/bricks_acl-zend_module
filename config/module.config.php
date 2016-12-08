<?php
namespace Bricks\Acl;

use Zend\Permissions\Acl\AclInterface;
use Bricks\Acl\ServiceManager\Factory\AclFactory;

return [
  'acl' => [
    'roles'     => [],
    'resources' => [],
    'allows'    => [],
    'denies'    => [],
  ],
  'service_manager' => [
    'factories' => [
      AclInterface::class => AclFactory::class,
    ],
  ],
];
