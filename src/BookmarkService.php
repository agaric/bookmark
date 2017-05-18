<?php

namespace Drupal\bookmark;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Session\AccountProxy;

/**
 * Class BookmarkService.
 *
 * @package Drupal\bookmark
 */
class BookmarkService implements BookmarkServiceInterface {

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entity_query;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var Drupal\Core\Session\AccountProxy
   */
  protected $current_user;
  /**
   * Constructor.
   */
  public function __construct(QueryFactory $entity_query, AccountProxy $current_user) {
    $this->entity_query = $entity_query;
    $this->current_user = $current_user;
  }

}
