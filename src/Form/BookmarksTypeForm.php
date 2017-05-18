<?php

namespace Drupal\bookmark\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;

/**
 * Class BookmarksTypeForm.
 *
 * @package Drupal\bookmark\Form
 */
class BookmarksTypeForm extends EntityForm {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface;
   */
  protected $bundleInfoService;

  /**
   * Constructs a new form.
   *
   * @param \Drupal\flag\ActionLink\ActionLinkPluginManager $action_link_manager
   *   The link type plugin manager.
   */
  public function __construct(EntityTypeBundleInfoInterface $bundle_info_service) {
    $this->bundleInfoService = $bundle_info_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info')
    );
  }

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

    $bundles = $this->bundleInfoService->getBundleInfo('node');
    $entity_bundles = [];
    foreach ($bundles as $bundle_id => $bundle_row) {
      $entity_bundles[$bundle_id] = $bundle_row['label'];
    }

    $form['extras'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Extras'),
      '#tree' => FALSE,
      '#weight' => 10,
    ];

    $form['extras']['bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Display the bookmark link on'),
      '#options' => $entity_bundles,
      '#default_value' => $bookmarks_type->getBundles(),
      '#weight' => 10,
      '#description' => $this->t('Check any content type where you want to display a link to bookmark content.'),
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
