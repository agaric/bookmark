<?php

namespace Drupal\bookmark\Controller;

use Drupal\bookmark\BookmarkServiceInterface;
use Drupal\bookmark\Entity\Bookmark;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ActionsController.
 *
 * @package Drupal\bookmark\Controller
 */
class ActionsController extends ControllerBase {

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Current User.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Bookmark Service.
   *
   * @var \Drupal\bookmark\BookmarkServiceInterface
   */
  protected $bookmarkService;

  /**
   * CacheTagInvalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
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
   * @param int $bookmark_id
   *   Bookmark Id.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return ajax Response.
   */
  public function ajaxLinkDelete($bookmark_id) {
    if (($bookmark = Bookmark::load($bookmark_id)) == NULL) {
      // @todo handle  errors.
      return $this->redirect('/');
    }

    $url = $bookmark->get('url')->getValue();
    $bookmark_uri = (isset($url[0]['uri'])) ? $bookmark->get('url')->getValue()[0]['uri'] : '';
    $entity_id = (!empty($bookmark_uri)) ? str_replace('entity:node/', '', $bookmark_uri) : 0;
    $entity = Node::load($entity_id);
    $bookmarkType = $this->bookmarkService->getBookmarkTypeById($bookmark->bundle());

    $response = new AjaxResponse();
    // Check that the user is owner of this bookmark before to try to delete it.
    // @todo Can this be handled at BookmarkAccessControlHandler level?
    if ($this->currentUser->id() != $bookmark->getOwnerId()) {
      // @todo handle errors
      return $this->redirect('/');
    }

    $bookmark->delete();
    $link = $this->bookmarkService->generateAddLink($bookmarkType, $entity);
    $response->addCommand(new ReplaceCommand('[data-bookmark-entity-id="' . $entity->id() . '"]', $link));

    // Expire the node cache because the link will change.
    $this->cacheTagsInvalidator->invalidateTags(["node:{$entity->id()}"]);
    return $response;
  }

  /**
   * Delete the bookmark entity (used in the my-bookmarks page).
   *
   * @param int $bookmark_id
   *   Bookmark Id.
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
    return $this->redirect('bookmark.actions_controller_user_bookmarks', ['user' => $this->currentUser->id()]);
  }

  /**
   * @param \Drupal\user\Entity\User $user
   */
  public function userBookmarks(User $user) {
    // @todo use a view instead of this method.
    $bookmarks = $this->bookmarkService->getAllBookmarksByUser($user->id());
    $content = [];

    if (empty($bookmarks)) {
      return [
        '#markup' => "<h3>You don't have any bookmarks yet.  Add one!</h3>",
      ];
    }

    foreach ($bookmarks as $key => $bookmark) {
      $url = $bookmark->get('url')->getValue();
      $content[$key]['bookmark'] = $bookmark;
      $content[$key]['link'] = Link::fromTextAndUrl($bookmark->label(), Url::fromUri($url[0]['uri']));
      // Only allow the delete action if the user is owner of the bookmark.
      $uid = $bookmark->get('uid')->getValue();
      $uid = $uid[0]['target_id'];
      $options = ['attributes' => ['onclick' => "return confirm('Are you sure? this action cannot be undone')"]];
      if ($this->currentUser->id() == $uid) {
        $content[$key]['delete'] = Link::createFromRoute('Delete', 'bookmark.actions_controller_delete_link', ['bookmark_id' => $bookmark->id()], $options);
      }
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

  /**
   * Check if the user can see the bookmark's page.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\user\Entity\User $user
   *
   * @return \Drupal\Core\Access\AccessResult
   */
  public function userBookmarksAccess(AccountInterface $account, User $user) {
    if ($user->id() == $account->id()) {
      return AccessResult::allowedIf($account->hasPermission('view own bookmarks'));
    } else {
      return AccessResult::allowedIf($account->hasPermission('view any bookmarks'));
    }

  }

  /**
   * Check if the user can delete the bookmark.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\user\Entity\User $user
   *
   * @return \Drupal\Core\Access\AccessResult
   */
  public function linkDeleteAccess(AccountInterface $account, $bookmark_id) {
    // @todo I think that this check is unnecessary, the same check is already
    // done in at the BookmarkAccessControlHandler.
    if (($bookmark = Bookmark::load($bookmark_id)) == NULL) {
      drupal_set_message('The bookmark does not exists.', ['warning']);
      return AccessResult::neutral();
    }

    if ($this->currentUser->id() != $bookmark->getOwnerId()) {
      return AccessResult::allowedIf($account->hasPermission('delete any bookmarks'));
    } else {
      return AccessResult::allowedIf($account->hasPermission('delete own bookmarks'));
    }

  }

}
