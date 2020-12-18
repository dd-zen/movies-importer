<?php

namespace Drupal\movie_importer;

/**
 * Interface ImporterInterface.
 */
interface MovieImporterMangerInterface {

  /**
   * Validates if the given file is not empty.
   *
   * @param array $rows
   *   The parsed CSV file rows.
   */
  public function validate(array $rows);

  /**
   * Creates nodes of movie content type based on the file data.
   *
   * @param array $rows
   *   The parsed CSV file rows.
   * @param bool $skip_first_row
   *   Marks if first row should be skipped.
   *
   * @return bool
   *   Result of the operation.
   */
  public function saveMovies(array $rows, bool $skip_first_row = TRUE): bool;

}
