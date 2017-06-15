<?php

namespace Drupal\Tests\bookmark\Kernel;

use Drupal\bookmark\Entity\Bookmark;
use Drupal\bookmark\Entity\BookmarkInterface;
use Drupal\bookmark\Entity\BookmarkType;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Session\AccountInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\simpletest\UserCreationTrait;
use Drupal\user\RoleInterface;

class BookmarkAccessTest extends KernelTestBase {

  use UserCreationTrait {
    createUser as drupalCreateUser;
    createRole as drupalCreateRole;
    createAdminRole as drupalCreateAdminRole;
  }

  use NodeCreationTrait {
    getNodeByTitle as drupalGetNodeByTitle;
    createNode as drupalCreateNode;
  }

  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

  public static $modules = [
    'bookmark',
    'node',
    'system',
    'datetime',
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
   * @var \Drupal\user\Entity\User
   */
  protected $userAccessOwnBookmarks;

  /**
   * @var \Drupal\user\Entity\User
   */
  protected $userAccessAnyBookmarks;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('system', 'sequences');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('bookmark');
    $this->installConfig('node');
    $this->accessHandler = $this->container->get('entity_type.manager')
      ->getAccessControlHandler('bookmark');
    // Clear permissions for authenticated users.
    $this->config('user.role.' . RoleInterface::AUTHENTICATED_ID)
      ->set('permissions', [])
      ->save();

    // Create a bookmarkType.
    $this->bookmarkType = $this->createBookmarkType();

    // Create user 1 who has special permissions.
    $this->drupalCreateUser();

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

    // Create a node type.
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'display_submitted' => FALSE,
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
  }

  /**
   * Creates a bookmark based on default settings.
   *
   * @param array $settings
   *   (optional) An associative array of settings for the node, as used in
   *   entity_create(). Override the defaults by specifying the key and value
   *   in the array, for example:
   *   @code
   *     $this->drupalCreateNode(array(
   *       'title' => t('Hello, world!'),
   *       'type' => 'article',
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
      'bundles' => ['page'],
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
      $this->assertEquals($result, $this->accessHandler->access($bookmark, $op, $account), $this->bookmarkAccessAssertMessage($op, $result, $bookmark->language()
        ->getId()));
    }
  }

  /**
   * Constructs an assert message to display which node access was tested.
   *
   * @param string $operation
   *   The operation to check access for.
   * @param bool $result
   *   Whether access should be granted or not.
   * @param string|null $langcode
   *   (optional) The language code indicating which translation of the node
   *   to check. If NULL, the untranslated (fallback) access is checked.
   *
   * @return string
   *   An assert message string which contains information in plain English
   *   about the node access permission test that was performed.
   */
  public function bookmarkAccessAssertMessage($operation, $result, $langcode = NULL) {
    return new FormattableMarkup(
      'Bookmark access returns @result with operation %op, language code %langcode.',
      [
        '@result' => $result ? 'true' : 'false',
        '%op' => $operation,
        '%langcode' => !empty($langcode) ? $langcode : 'empty',
      ]
    );
  }



}