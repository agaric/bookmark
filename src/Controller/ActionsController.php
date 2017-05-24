<?php

namespace Drupal\bookmark\Controller;

use Drupal\bookmark\BookmarkService;
use Drupal\bookmark\BookmarkServiceInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxy;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ActionsController.
 *
 * @package Drupal\bookmark\Controller
 */
class ActionsController extends ControllerBase {

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * @var \Drupal\bookmark\BookmarkLinkBuilderInterface
   */
  protected $bookmarkService;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request_stack, AccountProxy $current_user, BookmarkServiceInterface $bookmark_service) {
    $this->requestStack = $request_stack;
    $this->currentUser = $current_user;
    $this->bookmarkService = $bookmark_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('bookmark')
    );
  }

  /**
   * Delete action.
   *
   * Remove a bookmark.
   *
   * @param String $bookmark_type
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return ajax Response.
   */
  public function delete($bookmark_type) {
    $bookmarkType = $this->bookmarkService->getBookmarkTypeById($bookmark_type);
    $query_string = $this->requestStack->getCurrentRequest()->getQueryString();
    $query = [];
    if (!empty($query_string)) {
      parse_str($query_string, $query);
    }
    if (isset($query['entity_id']) && is_numeric($query['entity_id'])) {
      $entity_id = $query['entity_id'];
    }

    $arguments[] = 'success';
    if (isset($entity_id)) {
      $arguments[] = $entity_id;
    } else {
      $arguments[] = 0;
    }
    $arguments[] = $bookmarkType->getLinkText();

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'deleteBookmark', $arguments));

    return $response;
  }

}
