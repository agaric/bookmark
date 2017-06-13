<?php

namespace Drupal\Tests\bookmark\Kernel;

use Drupal\bookmark\Entity\Bookmark;
use Drupal\bookmark\Entity\BookmarkType;
use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\simpletest\UserCreationTrait;

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
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('system', 'sequences');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig('node');
    $this->accessHandler = $this->container->get('entity_type.manager')
      ->getAccessControlHandler('bookmark');
    // Clear permissions for authenticated users.
    $this->config('user.role.' . RoleInterface::AUTHENTICATED_ID)
      ->set('permissions', [])
      ->save();

    // Create user 1 who has special permissions.
    $this->drupalCreateUser();

    // Create a node type.
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'display_submitted' => FALSE,
    ]);
  }

  /**
   * Run basic tests for bookmark_access function.
   */
  public function testBookmarkAccess() {
    $this->assertTrue(TRUE, 'This is true');
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
      'title'     => $this->randomMachineName(8),
      'type'      => 'page',
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
    $bookmarkType = BookmarkType::create($settings);
    return $bookmarkType;
  }

}