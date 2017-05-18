<?php

namespace Drupal\bookmark\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Bookmarks edit forms.
 *
 * @ingroup bookmark
 */
class BookmarksForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\bookmark\Entity\Bookmarks */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

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
        drupal_set_message($this->t('Created the %label Bookmarks.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Bookmarks.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.bookmarks.canonical', ['bookmarks' => $entity->id()]);
  }

}
