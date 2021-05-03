<?php

namespace Drupal\social_moodle_enrollment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class PdfDownloadController.
 */
class PdfDownloadController extends ControllerBase {

  public function download() {

    $uri_prefix = 'public://downloads/';

    $uri = $uri_prefix . 'supervisor_confirmation_form.pdf';

    $headers = [
      'Content-Type' => 'application/pdf', // Would want a condition to check for extension and set Content-Type dynamically
      'Content-Description' => 'File Download',
      'Content-Disposition' => 'attachment; filename=' . $filename
    ];

    // Return and trigger file donwload.
    return new BinaryFileResponse($uri, 200, $headers, true );

  }
}
