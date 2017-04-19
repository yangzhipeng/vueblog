<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\ImageRequest;
use App\Repositories\LinkRepository;
use App\Services\FileManager\UploadManager;

class UploadController extends ApiController
{
    protected $manager;
    protected $link;

    public function __construct(UploadManager $manager, LinkRepository $link)
    {
        parent::__construct();

        $this->manager = $manager;

        $this->link = $link;
    }

    /**
     * Response the folder info.
     * 
     * @param  Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $data = $this->manager->folderInfo($request->get('folder'));

        return $this->respondWithArray([ 'data' => $data ]);
    }

    /**
     * Upload the file.
     *
     * @param  Request $request
     * @return array
     */
    public function uploadFile(Request $request)
    {
        $file = $_FILES['file'];

        $fileName = $request->get('name');

        $fileName = $fileName ? $fileName.'.'.explode('/', $file['type'])[1] : $file['name'];

        $path = str_finish($request->get('folder'), '/').$fileName;

        $content = \File::get($file['tmp_name']);

        if ($this->manager->checkFile($path)) {
            return $this->errorWrongArgs('This File exists.');
        }

        $this->manager->saveFile($path, $content);

        return $this->respondWithArray($this->manager->fileDetail($path));
    }

    /**
     * Upload file by the path.
     * 
     * @param  Request $request
     * @return array
     */
    public function uploadFileByPath(Request $request)
    {
        $image = $this->manager->store($request->file('image'), $request->get('path'));

        return $this->respondWithArray($image);
    }

    /**
     * Generic file upload method.
     * 
     * @param  ImageRequest $request
     * @return array
     */
    public function fileUpload(ImageRequest $request)
    {
        $strategy = $request->get('strategy', 'images');

        if (!$request->hasFile('image')) {
            return $this->respondWithArray([
                'success' => false,
                'error' => 'no file found.',
            ]);
        }

        $path = $strategy . '/' . date('Y') . '/' . date('m') . '/' . date('d');

        $result = $this->manager->store($request->file('image'), $path);

        return $this->respondWithArray($result);
    }

    /**
     * Create the folder.
     * 
     * @param  Request $request
     * @return array
     */
    public function createFolder(Request $request)
    {
        $folder = $request->get('folder');

        $data = $this->manager->createFolder($folder);

        return $this->respondWithArray([ 'data' => $data ]);
    }

    /**
     * Delete the folder.
     * 
     * @param  Request $request
     * @return array
     */
    public function deleteFolder(Request $request)
    {
        $del_folder = $request->get('del_folder');

        $folder = $request->get('folder') . '/' . $del_folder;

        $data = $this->manager->deleteFolder($folder);

        return $this->respondWithArray([ 'data' => $data ]);
    }

    /**
     * Delete the file.
     * 
     * @param  Request $request
     * @return array
     */
    public function deleteFile(Request $request)
    {
        $path = $request->get('path');

        $data = $this->manager->deleteFile($path);

        return $this->respondWithArray([ 'data' => $data ]);
    }
}
