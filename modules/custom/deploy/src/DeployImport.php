<?php

namespace Drupal\deploy;

/**
 * Configure deploy import
 */
class DeployImport {

    /**
     * @var
     */
    protected $importFolder;

    /**
     * @var
     */
    protected $importFile;

    /**
     * @throws \Exception
     */
    public function import() {

        $this->importFolder = DRUPAL_ROOT .'/'. \Drupal::config('deploy.settings')->get('deploy_folder');
        $this->importFile   = $this->importFolder .'/'. \Drupal::config('deploy.settings')->get('deploy_archive_name');

        $this->importValidation();

        $this->doImport();
    }

    /**
     * @throws \Exception
     */
    protected function importValidation() {

        if (!file_exists($this->importFolder)) {
            throw new \Exception('The import folder does not exist');
        }

        if (!file_exists($this->importFile)) {
            throw new \Exception('The import file does not exist');
        }
    }

    /**
     * @throws \Exception
     */
    protected function doImport() {

        $zip = new \ZipArchive();

        if ($zip->open($this->importFile, \ZipArchive::CREATE) === true) {
            $extractTo = $zip->extractTo(DRUPAL_ROOT .'/');
            $zip->close();

            if ($extractTo !== true) {
                throw new \Exception('Extract archive failed, it should be a permission problem');
            }
        } else {
            throw new \Exception('Import terminated with errors, see logs files');
        }
    }
}
