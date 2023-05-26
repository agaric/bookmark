<?php

namespace Drupal\Tests\bookmark\Kernel;

use Drupal\bookmark\Entity\Bookmark;
use Drupal\bookmark\Entity\BookmarkInterface;
use Drupal\bookmark\Entity\BookmarkType;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Session\AccountInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\UserCreationTrait;
use Drupal\user\RoleInterface;

class BookmarkAccessTest extends KernelTestBase {

  use UserCreationTrait {
    createUser as drupalCreateUser;
    createRole as drupalCreateRole;
    createAdminRole as drupalCreateAdminRole;
  }
  protected static $modules = [
    'bookmark',
    'system',
    'user',
    'field',
    'text',
    'link',
  ];

  /**
   * @var \Drupal\bookmark\Entity\BookmarkType;
   */
  protected $bookmarkType;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $admin;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $userAccessOwnBookmarks;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $userAccessAnyBookmarks;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installSchema('system', 'sequences');
    $this->installEntitySchema('user');
    $this->installEntitySchema('bookmark');
    $this->accessHandler = $this->container->get('entity_type.manager')
      ->getAccessControlHandler('bookmark');
    // Clear permissions for authenticated users.
    $this->config('user.role.' . RoleInterface::AUTHENTICATED_ID)
      ->set('permissions', [])
      ->save();

    // Create a bookmarkType.
    $this->bookmarkType = $this->createBookmarkType();

    // Create user 1 who has special permissions.
    $this->admin = $this->drupalCreateUser([], [], TRUE);

    // Create user that can only edit/delete/view it's own bookmarks.
    $this->userAccessOwnBookmarks = $this->drupalCreateUser([
      'delete own bookmarks',
      'edit own bookmarks',
      'view own bookmarks',
    ]);
    // Create user than can edit/delete/view all the bookmarks.
    $this->userAccessAnyBookmarks = $this->drupalCreateUser([
      'delete any bookmarks',
      'edit any bookmarks',
      'view any bookmarks',
    ]);
  }

  /**
   * Test that the user can edit it's own bookmarks.
   */
  public function testOwnBookmarkAccess() {
    /** @var \Drupal\bookmark\Entity\Bookmark $bookmark1 */
    $bookmark1 = $this->createBookmark([
      'type' => $this->bookmarkType->id(),
      'uid' => $this->userAccessOwnBookmarks->id(),
    ]);
    $bookmark2 = $this->createBookmark([
      'type' => $this->bookmarkType->id(),
      'uid' => $this->userAccessAnyBookmarks->id(),
    ]);
    $this->assertTrue($bookmark1->getOwnerId() == $this->userAccessOwnBookmarks->id());

    // Check that the user can access to their own bookmarks.
    $this->assertBookmarkAccess([
      'update' => TRUE,
      'view' => TRUE,
      'delete' => TRUE,
    ], $bookmark1, $this->userAccessOwnBookmarks);

    // Check the access with the "any" permissions.
    $this->assertBookmarkAccess([
      'update' => TRUE,
      'view' => TRUE,
      'delete' => TRUE,
    ], $bookmark1, $this->userAccessAnyBookmarks);

    // Check that the user cannot access if doesn't has the "any" permissions.
    $this->assertBookmarkAccess([
      'update' => FALSE,
      'view' => FALSE,
      'delete' => FALSE,
    ], $bookmark2, $this->userAccessOwnBookmarks);

    // Check that the user cannot access to their own bookmarks if he doesn't
    // have the "own" permissions.
    $this->assertBookmarkAccess([
      'update' => FALSE,
      'view' => FALSE,
      'delete' => FALSE,
    ], $bookmark2, $this->userAccessAnyBookmarks);

    // Check that the admin is not restricted by the permissions.
    $this->assertBookmarkAccess([
      'update' => TRUE,
      'view' => TRUE,
      'delete' => TRUE,
    ], $bookmark1, $this->admin);

    $this->assertBookmarkAccess([
      'update' => TRUE,
      'view' => TRUE,
      'delete' => TRUE,
    ], $bookmark2, $this->admin);
  }

  /**
   * Creates a bookmark based on default settings.
   *
   * @param array $settings
   *   (optional) An associative array of settings for the bookmark, as used in
   *   entity_create(). Override the defaults by specifying the key and value
   *   in the array, for example:
   *   @code
   *     $this->createBookmark(array(
   *       'url' =>  ['title' => t('Hello, world!'), 'uri' => 'http://www.google.com'],
   *       'type' => 'bookmark type',
   *     ));
   *   @endcode
   *   The following defaults are provided:
   *   - title: Random string.
   *   - type: 'page'.
   *   - uid: The currently logged in user, or anonymous.
   *
   * @return \Drupal\bookmark\Entity\BookmarkInterface
   *   The created bookmark entity.
   */
  protected function createBookmark(array $settings = []) {
    // Populate defaults array.
    $settings += [
      'status'  => 1,
      'url'   => [
        'uri' => 'http://www.google.com.mx',
        'title' => 'This is the title',
      ],
      'type' => 'page',
      'uid'       => \Drupal::currentUser()->id(),
    ];

    $bookmark = Bookmark::create($settings);
    $bookmark->save();

    return $bookmark;
  }

  /**
   * @param array $settings
   *
   * @return \Drupal\bookmark\Entity\BookmarkTypeInterface
   *  The created bookmark type.
   */
  protected function createBookmarkType(array $settings = []) {

    $settings += [
      'id' => $this->randomMachineName(8),
      'label' => $this->randomMachineName(8),
      'link_text' => $this->randomMachineName(8),
      'bundles' => [],
    ];

    $bookmarkType = BookmarkType::create($settings);
    return $bookmarkType;
  }


  /**
   * Asserts that bookmarks access correctly grants or denies access.
   *
   * @param array $ops
   *   An associative array of the expected access grants for the bookmark
   *   and account, with each key as the name of an operation (e.g. 'view',
   *   'delete') and each value a Boolean indicating whether access to that
   *   operation should be granted.
   * @param \Drupal\bookmark\entity\BookmarkInterface $bookmark
   *   The bookmark object to check.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which to check access.
   */
  public function assertBookmarkAccess(array $ops, BookmarkInterface $bookmark, AccountInterface $account) {
    foreach ($ops as $op => $result) {
      $this->assertEquals($result, $this->accessHandler->access($bookmark, $op, $account), $this->bookmarkAccessAssertMessage($op, $result));
    }
  }

  /**
   * Constructs an assert message to display which bookmark access was tested.
   *
   * @param string $operation
   *   The operation to check access for.
   * @param bool $result
   *   Whether access should be granted or not.
   * @return string
   *   An assert message string which contains information in plain English
   *   about the bookmark access permission test that was performed.
   */
  public function bookmarkAccessAssertMessage($operation, $result) {
    return new FormattableMarkup(
      'Bookmark access returns @result with operation %op.',
      [
        '@result' => $result ? 'true' : 'false',
        '%op' => $operation,
      ]
    );
  }



}
