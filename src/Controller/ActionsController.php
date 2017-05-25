<?php

namespace Drupal\bookmark\Controller;

use Drupal\bookmark\entity\Bookmark;
use Drupal\bookmark\BookmarkServiceInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxy;
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
   * @param String $bookmark_id
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return ajax Response.
   */
  public function delete($bookmark_id) {
    $bookmark = Bookmark::load($bookmark_id);
    $entity_type_id = $bookmark->bundle();
    $bookmark_uri = (isset($bookmark->get('url')->getValue()[0]['uri'])) ? $bookmark->get('url')->getValue()[0]['uri'] : '';
    $entity_id = (!empty($bookmark_uri)) ? str_replace('entity:node/', '', $bookmark_uri) : 0;
    $bookmarkType = $this->bookmarkService->getBookmarkTypeById($entity_type_id);

    $response = new AjaxResponse();
    $arguments[0] = 'success';
    $arguments[1] = $entity_id;
    // Check that the user is owner of this bookmark before to try to delete it.
    if ($this->currentUser->id() != $bookmark->getOwnerId()) {
      $arguments[2] = 'An error occurred';
      return $response;
    }
    if (!$bookmark->delete()) {
      $arguments[2] = 'An error occurred';
    }
    $arguments[2] = $bookmarkType->getLinkText();
    $response->addCommand(new InvokeCommand(NULL, 'deleteBookmark', $arguments));
    return $response;
  }

}
