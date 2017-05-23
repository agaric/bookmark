<?php

namespace Drupal\bookmark\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ActionsController.
 *
 * @package Drupal\bookmark\Controller
 */
class ActionsController extends ControllerBase {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request_stack;
  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request_stack) {
    $this->request_stack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Delete.
   *
   * @return string
   *   Return Hello string.
   */
  public function delete($bookmark) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: delete with parameter(s): $bookmark'),
    ];
  }

}
