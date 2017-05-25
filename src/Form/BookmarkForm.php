<?php

namespace Drupal\bookmark\Form;

use Drupal\bookmark\BookmarkServiceInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Entity\ContentEntityForm;
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
   * @var RequestStack
   */
  var $requestStack;

  /**
   * @var \Drupal\Core\Entity\Entity
   */
  var $targetEntity;

  /**
   * @var \Drupal\bookmark\BookmarkServiceInterface
   */
  var $bookmarkService;

  /**
   * {@inheritdoc}
   */
  public function __construct($entity_manager, $entity_type_bundle_info = NULL, $time = NULL, RequestStack $request_stack, BookmarkServiceInterface $bookmark_service) {
    parent::__construct($entity_manager, $entity_type_bundle_info = NULL, $time = NULL);
    $this->requestStack = $request_stack;
    $this->bookmarkService = $bookmark_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('request_stack'),
      $container->get('bookmark')
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo Validate the form and make it compatible with the ajax submit.
    return parent::validateForm($form, $form_state);
  }



  /**
   * Doesn't save the bookmark, just close the modal window.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function cancelAjaxSubmit(array &$form, FormStateInterface $formState) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

  /**
   * Save the bookmark.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $entity = &$this->entity;
    $status = parent::save($form, $form_state);

    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      // @todo display any error correctly in the form.
      // Add a command to execute on form, jQuery .html() replaces content between tags.
      // In this case, we replace the desription with wheter the username was found or not.
      //$response->addCommand(new HtmlCommand('input[name=name[0][value]', $text));

      // Add a command, InvokeCommand, which allows for custom jQuery commands.
      // In this case, we alter the color of the description.
      //$response->addCommand(new InvokeCommand('#edit-user-name--description', 'css', array('color', $color)));

      // Return the AjaxResponse Object.
      return $response;
    }
    else {
      $link = $this->bookmarkService->generateDeleteLink($this->entity->id(), $this->targetEntity);
      $response->addCommand(new ReplaceCommand('[data-bookmark-entity-id="' . $this->targetEntity->id() . '"]', $link));
      $response->addCommand(new CloseModalDialogCommand());
    }
    return $response;
  }

}
