<?php

namespace Drupal\bookmark\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BookmarksTypeForm.
 *
 * @package Drupal\bookmark\Form
 */
class BookmarksTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\bookmark\Entity\BookmarksTypeInterface $bookmarks_type */
    $bookmarks_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $bookmarks_type->label(),
      '#description' => $this->t("Label for the Bookmarks type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $bookmarks_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\bookmark\Entity\BookmarksType::load',
      ],
      '#disabled' => !$bookmarks_type->isNew(),
    ];

    $form['link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link Text'),
      '#maxlength' => 255,
      '#default_value' => ($bookmarks_type->getLinkText()) ?: 'Bookmark this',
      '#description' => $this->t('The text for the "Bookmark this" link'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $bookmarks_type = $this->entity;
    $status = $bookmarks_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Bookmarks type.', [
          '%label' => $bookmarks_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Bookmarks type.', [
          '%label' => $bookmarks_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($bookmarks_type->toUrl('collection'));
  }

}
