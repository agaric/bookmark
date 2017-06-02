<?php

namespace Drupal\bookmark\Form;

use Drupal\bookmark\BookmarkServiceInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\ReplaceCommand;
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
   * RequestStack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $requestStack;

  /**
   * The entity where the bookmark will be applied.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  public $targetEntity;

  /**
   * Bookmark Service.
   *
   * @var \Drupal\bookmark\BookmarkServiceInterface
   */
  public $bookmarkService;

  /**
   * CacheTagsInvalidator Service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * {@inheritdoc}
   */
  public function __construct($entity_manager, RequestStack $request_stack, BookmarkServiceInterface $bookmark_service, CacheTagsInvalidatorInterface $cache_tags_invalidator, $entity_type_bundle_info = NULL, $time = NULL) {
    parent::__construct($entity_manager, $entity_type_bundle_info = NULL, $time = NULL);
    $this->requestStack = $request_stack;
    $this->bookmarkService = $bookmark_service;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('request_stack'),
      $container->get('bookmark'),
      $container->get('cache_tags.invalidator'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
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
    if (isset($query['entity_id']) && is_numeric($query['entity_id']) && $this->targetEntity = Node::load($query['entity_id'])) {
      $form['url']['widget'][0]['title']['#default_value'] = $this->targetEntity->label();
      $form['url']['widget'][0]['title']['#attributes']['readonly'] = 'readonly';
      $form['url']['widget'][0]['uri']['#default_value'] = "{$this->targetEntity->label()} ({$this->targetEntity->id()})";
      $form['url']['widget'][0]['uri']['#attributes']['readonly'] = 'readonly';
    }

    if (isset($query['use_ajax']) && $query['use_ajax']) {
      // Ajax Modal.
      $form['actions']['submit']['#submit'] = [];
      $form['actions']['submit']['#ajax'] = [
        'callback' => '::ajaxSubmit',
        'event' => 'click',
      ];
      $form['actions']['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#submit' => [],
        '#ajax' => ['callback' => '::cancelAjaxSubmit', 'event' => 'click'],
      ];
    } else {
      $form['actions']['submit']['#submit'] = ['::noAjaxSubmit'];
    }

    // removing the repeated label.
    $form['url']['widget'][0]['#title'] = '';
    // /Link Text/Title/g
    $form['url']['widget'][0]['title']['#title'] = $this->t('Title');

    // Removing this part of the help text:
    // "Enter <front> to link to the front page."
    $description = $form['url']['widget'][0]['uri']['#description'];
    $description = str_replace('Enter <em class="placeholder">&lt;front&gt;</em> to link to the front page.', "" , (string) $description);
    $form['url']['widget'][0]['uri']['#description'] = $this->t($description);

    return $form;
  }

  /**
   * Doesn't save the bookmark, just close the modal window.
   *
   * @param array $form
   *   The Bookmark Form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   FormState object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return an AjaxResponse.
   */
  public function cancelAjaxSubmit(array &$form, FormStateInterface $formState) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Expire the node cache because the link will change.
    if ($this->targetEntity instanceof  EntityInterface) {
      $this->cacheTagsInvalidator->invalidateTags(["node:{$this->targetEntity->id()}"]);
    }
    return $status = parent::save($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function noAjaxSubmit(array &$form, FormStateInterface $form_state) {
    $this->submitForm($form, $form_state);
    if ($this->save($form, $form_state)) {
      drupal_set_message($this->t("The @bookmark_bundle has been saved correctly", ['@bookmark_bundle' => $this->entity->bundle()]));
    } else {
      drupal_set_message($this->t("There was a problem, please try again later."));
    }
    $form_state->setRedirect('entity.bookmark.collection');
  }

  /**
   * Save the bookmark.
   *
   * @param array $form
   *   The BookmarkForm.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormState Object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return an Ajax response.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $this->save($form, $form_state);
    $response = new AjaxResponse();
    $link = $this->bookmarkService->generateDeleteLink($this->entity->id(), $this->targetEntity);
    $response->addCommand(new ReplaceCommand('[data-bookmark-entity-id="' . $this->targetEntity->id() . '"]', $link));
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // @todo make sure that the uri field is mandatory.
  }

}
