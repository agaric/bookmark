<?php

namespace Drupal\bookmark\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Form controller for Bookmark edit forms.
 *
 * @ingroup bookmark
 */
class BookmarkForm extends ContentEntityForm {

  /**
   * @var RequestStack
   */
  var $requestStack;

  /**
   * {@inheritdoc}
   */
  public function __construct($entity_manager, $entity_type_bundle_info = NULL, $time = NULL, RequestStack $request_stack) {
    parent::__construct($entity_manager, $entity_type_bundle_info = NULL, $time = NULL);
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\bookmark\Entity\Bookmark */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;
    $query_string = $this->requestStack->getCurrentRequest()->getQueryString();
    $query = [];
    if (!empty($query_string)) {
      parse_str($query_string, $query);
    }

    // If this was clicked using the link in the entity, let's prepopulate the
    // url and the title.
    if (isset($query['entity_id']) && is_numeric($query['entity_id']) && $node = Node::load($query['entity_id'])) {
      $form['url']['widget'][0]['title']['#default_value'] = $node->label();
      $form['url']['widget'][0]['title']['#attributes']['readonly'] = 'readonly';
      $form['url']['widget'][0]['uri']['#default_value'] = "{$node->label()} ({$node->id()})";
      $form['url']['widget'][0]['uri']['#attributes']['readonly'] = 'readonly';
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Bookmark.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Bookmark.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.bookmark.canonical', ['bookmark' => $entity->id()]);
  }

}
