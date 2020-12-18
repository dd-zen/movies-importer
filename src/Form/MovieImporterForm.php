<?php

namespace Drupal\movie_importer\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\movie_importer\Exception\MovieImportFileEmptyException;
use Drupal\movie_importer\MovieImporterMangerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Movie Imported admin form.
 */
class MovieImporterForm extends FormBase {

  /**
   * The movie importer manager.
   *
   * @var \Drupal\movie_importer\MovieImporterMangerInterface
   */
  protected $movieImporterManager;

  /**
   * The entity storage for the 'file' entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;


  /**
   * MovieImporterForm constructor.
   *
   * @param \Drupal\movie_importer\MovieImporterMangerInterface $movie_importer_manger
   *   The movie importer service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(MovieImporterMangerInterface $movie_importer_manger, EntityTypeManagerInterface $entity_type_manager, FileSystemInterface $file_system) {
    $this->movieImporterManager = $movie_importer_manger;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->fileSystem = $file_system;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('movie_importer.manager'),
      $container->get('entity_type.manager'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'movie_importer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['skip_first_row'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Skip first row'),
      '#description' => $this->t('Skip first row if file has header.'),
    ];

    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('CSV file'),
      '#required' => TRUE,
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['import'] = [
      '#type' => 'submit',
      '#value' => $this->t('Start import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $validators = ['file_validate_extensions' => ['csv']];
    /* @var \Drupal\file\FileInterface $file */
    $file = file_save_upload('file', $validators, FALSE, 0);

    if (isset($file)) {
      $file_path = $this->fileSystem->realpath($file->getFileUri());
      $rows = array_map('str_getcsv', file($file_path));

      try {
        $this->movieImporterManager->validate($rows);
      }
      catch (MovieImportFileEmptyException $exception) {
        $form_state->setErrorByName('file', $exception->getMessage());
        $form_state->setRebuild(TRUE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
