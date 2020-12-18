<?php

namespace Drupal\movie_importer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\movie_importer\Exception\MovieImportFileEmptyException;

/**
 * Provides movie importer manager.
 */
class MovieImporterManager implements MovieImporterMangerInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * MovieImporterManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $rows) {
    // @todo Add correct condition.
    if (empty($file_content)) {
      throw new MovieImportFileEmptyException('Movie import file is empty.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function saveMovies(array $rows, bool $skip_first_row = TRUE): bool {
    if ($skip_first_row == TRUE) {
      array_unshift($data);
    }

    foreach ($rows as $row) {
      if (!$this->saveMovie($row)) {
        // @todo Add logger if node has not been created for some reason.
      }
    }
  }

  /**
   * Helper function for creating and saving the node.
   *
   * @param array $properties
   *   The node properties.
   *
   * @return bool
   *   The result of operation.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function saveMovie(array $properties): bool {
    $node = $this->nodeStorage->create([
      'title' => $properties['title'],
      'field_id' => $properties['id'],
      'field_year' => $properties['year'],
    ]);

    return (bool) $node->save();
  }

}
