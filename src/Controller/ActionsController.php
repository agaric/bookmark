<?php

namespace Drupal\bookmark\Controller;

use Drupal\bookmark\entity\Bookmark;
use Drupal\bookmark\BookmarkServiceInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
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
   * @var CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request_stack, AccountProxy $current_user, BookmarkServiceInterface $bookmark_service, CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->requestStack = $request_stack;
    $this->currentUser = $current_user;
    $this->bookmarkService = $bookmark_service;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('bookmark'),
      $container->get('cache_tags.invalidator')
    );
  }

  /**
   * Delete Ajax action, return the link to let the user add again the bookmark.
   *
   * @param string $bookmark_id
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return ajax Response.
   */
  public function ajaxLinkDelete($bookmark_id) {
    if (($bookmark = Bookmark::load($bookmark_id)) == NULL) {
      // @todo handle  errors.
      return;
    }

    $bookmark_uri = (isset($bookmark->get('url')->getValue()[0]['uri'])) ? $bookmark->get('url')->getValue()[0]['uri'] : '';
    $entity_id = (!empty($bookmark_uri)) ? str_replace('entity:node/', '', $bookmark_uri) : 0;
    $entity = Node::load($entity_id);
    $bookmarkType = $this->bookmarkService->getBookmarkTypeById($bookmark->bundle());

    $response = new AjaxResponse();
    // Check that the user is owner of this bookmark before to try to delete it.
    // @todo Can this be handled at BookmarkAccessControlHandler level?
    if ($this->currentUser->id() != $bookmark->getOwnerId()) {
      // @todo handle errors
      return;
    }

    $bookmark->delete();
    $link = $this->bookmarkService->generateAddLink($bookmarkType, $entity);
    $response->addCommand(new ReplaceCommand('[data-bookmark-entity-id="' . $entity->id() . '"]', $link));

    // expire the node cache because the link will change.
    $this->cacheTagsInvalidator->invalidateTags(["node:{$entity->id()}"]);
    return $response;
  }

  /**
   * @param string $bookmark_id
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object
   */
  public function linkDelete($bookmark_id) {
    if (($bookmark = Bookmark::load($bookmark_id)) == NULL) {
      drupal_set_message('The bookmark does not exists.', ['warning']);
      return $this->redirect('bookmark.actions_controller_my_bookmarks');
    }


    // Check that the user is owner of this bookmark before to try to delete it.
    // @todo Can this be handled at BookmarkAccessControlHandler level?
    if ($this->currentUser->id() != $bookmark->getOwnerId()) {
      drupal_set_message('You cannot delete this bookmark.', 'error');
      return $this->redirect('bookmark.actions_controller_my_bookmarks');
    }

    $bookmark->delete();
    drupal_set_message('The bookmark has been deleted.');
    return $this->redirect('bookmark.actions_controller_my_bookmarks');
  }

  /**
   * My Bookmarks page.
   */
  public function myBookmarks() {
    // @todo use a view instead of this method.
    $bookmarks = $this->bookmarkService->getAllBookmarksByUser($this->currentUser->id());
    $content = [];
    foreach ($bookmarks as $key => $bookmark) {
      $url = $bookmark->get('url')->getValue();
      $content[$key]['bookmark'] = $bookmark;
      $content[$key]['link'] = Link::fromTextAndUrl($bookmark->label(), Url::fromUri($url[0]['uri']));
      $content[$key]['delete'] = Link::createFromRoute('Delete', 'bookmark.actions_controller_delete_link', ['bookmark_id' => $bookmark->id()]);
    }

    return [
      '#theme' => 'bookmarks_list',
      '#bookmarks' => $content,
      'pager' => [
        '#theme' => 'pager',
        '#weight' => 10,
      ],
    ];

  }

}
