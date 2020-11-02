<?php
declare(strict_types=1);
/**
 * File Storage Plugin for CakePHP
 *
 * @author Florian Krämer
 * @copyright 2012 - 2020 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;

/**
 * Class StorageShell
 *
 * @package Burzum\FileStorage\Shell
 * @property \Burzum\FileStorage\Shell\Task\ImageTask $Image
 */
class StorageShell extends Shell
{
    /**
     * Tasks
     *
     * @var array
     */
    public $tasks = [
        'Burzum/FileStorage.Image',
    ];

    /**
     * @inheritDoc
     * @return void
     */
    public function main(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();
        $parser->addOption('adapter', [
            'short' => 'a',
            'help' => __('The adapter config name to use.'),
            'default' => 'Local',
        ]);
        $parser->addOption('identifier', [
            'short' => 'i',
            'help' => __('The files identifier (`model` field in `file_storage` table).'),
            'default' => null,
        ]);
        $parser->addOption('model', [
            'short' => 'm',
            'help' => __('The model / table to use.'),
            'default' => 'Burzum/FileStorage.FileStorage',
        ]);
        $parser->addSubcommand('image', [
            'help' => __('Image Processing Task.'),
            'parser' => $this->Image->getOptionParser(),
        ]);
        $parser->addSubcommand('store', [
            'help' => __('Stores a file in the DB.'),
        ]);

        return $parser;
    }

    /**
     * Does the arg and params checks for store().
     *
     * @return void
     */
    protected function _storePrecheck(): void
    {
        if (empty($this->args[0])) {
            $this->abort('No file provided!');
        }

        if (!file_exists($this->args[0])) {
            $this->abort('The file does not exist!');
        }

        $adapterConfig = StorageManager::config($this->params['adapter']);
        if (empty($adapterConfig)) {
            $this->abort(sprintf('Invalid adapter config `%s` provided!', $this->params['adapter']));
        }
    }

    /**
     * Store a local file via command line in any storage backend.
     *
     * @return void
     */
    public function store(): void
    {
        $this->_storePrecheck();
        $model = $this->loadModel($this->params['model']);
        $fileData = StorageUtils::fileToUploadArray($this->args[0]);
        $entity = $model->newEntity([
            'adapter' => $this->params['adapter'],
            'file' => $fileData,
            'filename' => $fileData['name'],
        ]);

        if ($model->save($entity)) {
            $this->out('File successfully saved!');
            $this->out('ID:   ' . $entity->get('id'));
            $this->out('Path: ' . $entity->get('full_path'));
        } else {
            $this->abort('Failed to save the file.');
        }
    }
}
