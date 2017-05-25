<?php

namespace Drupal\bookmark\Controller;

use Drupal\bookmark\entity\Bookmark;
use Drupal\bookmark\BookmarkServiceInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\ReplaceCommand;
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
    $bookmark_uri = (isset($bookmark->get('url')->getValue()[0]['uri'])) ? $bookmark->get('url')->getValue()[0]['uri'] : '';
    $entity_id = (!empty($bookmark_uri)) ? str_replace('entity:node/', '', $bookmark_uri) : 0;
    $entity = Node::load($entity_id);
    $bookmarkType = $this->bookmarkService->getBookmarkTypeById($bookmark->bundle());

    $response = new AjaxResponse();
    // Check that the user is owner of this bookmark before to try to delete it.
    if ($this->currentUser->id() != $bookmark->getOwnerId()) {
      // @todo handle errors.
    }
    if (!$bookmark->delete()) {
      // @todo handle errors.
    }
    $link = $this->bookmarkService->generateAddLink($bookmarkType, $entity);
    $response->addCommand(new ReplaceCommand('[data-bookmark-entity-id="' . $entity->id() . '"]', $link));
    return $response;
  }

}
