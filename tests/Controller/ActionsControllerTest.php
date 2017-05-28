<?php

namespace Drupal\bookmark\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the bookmark module.
 */
class ActionsControllerTest extends WebTestBase {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "bookmark ActionsController's controller functionality",
      'description' => 'Test Unit for module bookmark and controller ActionsController.',
      'group' => 'Other',
    );
  }

  /**
   * Tests bookmark functionality.
   */
  public function testActionsController() {
    // Check that the basic functions of module bookmark.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
