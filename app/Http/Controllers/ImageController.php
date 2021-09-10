<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZanySoft\Zip\Zip;
use Illuminate\Support\Facades\File;

use App\Models\Images;

class ImageController extends Controller
{

    /**
     * Display index page, with a listing of stacked images.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //option1 - read images from DB
        $images = $this->retrieveAllImagesFromDatabase();

        //option2 - readall images in images folder
        // $images = $this->retrieveAllImagesFromFileSystem();
        
        return view('index')
            ->with('images', $this->createImageStacks($images));
    }


    /**
     * Store image in images/layer folder.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function imageUploadStore(Request $request)
    {
    	 $request->validate([
            'image' => 'required|image|mimes:png|max:2048',
        ]);

        //save image to filesystem
        $imageName = time().'.'.$request->image->extension();  
        $request->image->move(public_path('images/layers/' . $request->rowIndex), $imageName);

        //save record in DB
        $filePath = $request->rowIndex . '/' . $imageName;
        Images::create([
            'row_ndex' => $request->input('rowIndex'),
            'file_path' => $filePath
        ]);
  
        //option1 - read images from DB
        $images = $this->retrieveAllImagesFromDatabase();

        //option2 - readall images in images folder
        // $images = $this->retrieveAllImagesFromFileSystem();

        // return back()
        //     ->with('success','You have successfully upload image.')
        //     ->with('rowIndex', $request->rowIndex);

        return view('index')
            ->with('success','Image uploaded successfully.')
            ->with('images',$this->createImageStacks($images))
            ->with('rowIndex', $request->rowIndex);
    }


    /**
     * Read, extract content of zipfile and store images in zipfile into images/layer folder.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function zipfileUploadStore (Request $request) {

        //read zip file and validate its content
        $zip = Zip::open($request->zip);
        $unzippedFilesArr = $zip->listFiles();
        foreach ($unzippedFilesArr as $key => $unzippedFile) {
            $status = $this->isUnzippedFilePathValid($unzippedFile);
            if ($status !== true) {
                //option1 - read images from DB
                $images = $this->retrieveAllImagesFromDatabase();

                //option2 - readall images in images folder
                // $images = $this->retrieveAllImagesFromFileSystem();
                
                return view('index')
                    ->withErrors([$status])
                    ->with('images',$this->createImageStacks($images));;
            }
        }
        
        //delete current layers folder
        File::deleteDirectory(public_path('images/layers'));

        //extract whole zip folder to filesystem
        $zip = Zip::open($request->zip);
        $zip->extract(public_path('images'), $unzippedFilesArr);
        $zip->close();

        //delete all records in Images table
        Images::truncate();

        //save records in DB
        foreach ($unzippedFilesArr as $key => $unzippedfilePath) {
            $explodes = explode('/', $unzippedfilePath);
            if (isset($explodes[0]) && isset($explodes[1]) && isset($explodes[2])
                    && $explodes[0] != '' && $explodes[1] != '' && $explodes[2] != '') {
                Images::create([
                    'row_ndex' => $explodes[1],
                    'file_path' => $explodes[1] . '/' . $explodes[2]
                ]);
            }
        }

        //option1 - read images from DB
        $images = $this->retrieveAllImagesFromDatabase();

        //option2 - readall images in images folder
        // $images = $this->retrieveAllImagesFromFileSystem();
        
        return view('index')
            ->with('success','Image uploaded successfully.')
            ->with('images',$this->createImageStacks($images));
    }


    /**
     * Display index page, with a listing of resorted stacked images.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function stackRegenerate (Request $request) {
        //option1 - read images from DB
        $images = $this->retrieveAllImagesFromDatabase();

        //option2 - readall images in images folder
        // $images = $this->retrieveAllImagesFromFileSystem();
        
        $stackedImages = $this->createImageStacks($images);
        shuffle($stackedImages);

        return view('index')
            ->with('success','Stacked images have been Regenerated Successfully.')
            ->with('images', $stackedImages);
    }


    /**
     * @desc Retrieve all images in the images/layer folder into an array.
     * @return array of images in the images/layer folder
     */
    public function retrieveAllImagesFromFileSystem() {
        $images = [];
        //retrieve images from images/layer directory into array
        for ($i=1; $i<=4; $i++) {
            $stack = [];
            $dir_path = public_path() . '/images/layers/' . $i;
            if ( file_exists($dir_path) ) {
                $dir = new \DirectoryIterator($dir_path);
                foreach ($dir as $fileinfo) {
                    if (!$fileinfo->isDot()) {
                        $stack[] = $i . '/' . $fileinfo->getFilename();
                    }
                }
                $images['' . $i] = $stack;
            }
        }
        return $images;
    }


    /**
     * @desc Retrieve all images in the images database table into an array.
     * @return array of images in the retrieved from database
     */
    public function retrieveAllImagesFromDatabase() {
        $images = [];
        $imagesDb = Images::all();
        foreach ($imagesDb as $key => $image) {
            if (isset($images['' . $image->row_ndex])) {
                $images['' . $image->row_ndex][] = $image->file_path;
            }
            else {
                $images['' . $image->row_ndex] = [$image->file_path];
            }
        }
        return $images;
    }


    /**
    * createImageStacks function
    * @param files array of images in the the images/layer folder
    * @return multidimensional array consisting of images to be stacked
    */
    public function createImageStacks ($files) {
        $rowCount = count($files);
        $numStacked = 0;
        $stack = [];
        $result = [];

        foreach ($files as $key => $fileRow) {
            foreach ($fileRow as $key2 => $image) {
                $numStacked = 0;
                $stack = [];
                //add first stack image
                $stack[] = $image;
                $numStacked++;
                //add 2nd to 4th stack images
                while ($numStacked < $rowCount) {
                    $stack[] = $this->addStack($key, $numStacked, $rowCount, $files);
                    $numStacked++;
                }
                //reverse stacked images, to bring unique images to the top
                $result[] = array_reverse($stack);
            }
        }
        return $result;
    }


    /**
    * addStack function
    * @param rowStartPoint the current row position, where the current image stack begun
    * @param numStacked the current number of images that have been stacked
    * @param totalRows total number of rows
    * @param images array of images in the images/layer folder
    * @return imagePath of the image to be added to the current image stack being created
    */
    public function addStack ($rowStartPoint, $numStacked, $totalRows, $images) {
        
        //reached end of row, hence start from top
        if ( $rowStartPoint + $numStacked > $totalRows)
            $rowPos = ($rowStartPoint + $numStacked) - $totalRows;
        else
            $rowPos = $rowStartPoint + $numStacked;
        
        //retrieve image to be stacked
        if (isset($images[$rowPos]) && count($images[$rowPos]) > 0)
            return $images[$rowPos][0];
        else
            return "";
    }


    /**
    * isUnzippedFilePathValid function
    * @param unzippedfilePath file path to be validated against accepted file path format
    * @return true if valid or an error message if invalid file path format
    */
    public function isUnzippedFilePathValid ($unzippedfilePath) {
        $explodes = explode('/', $unzippedfilePath);

        if(!isset($explodes[0]) || (isset($explodes[0]) && $explodes[0] != "layers")) {
            return "Zip file Error, the zip file must have a root folder named layers";
        }

        if (!isset($explodes[1]) || 
                ( isset($explodes[1]) 
                    && !empty($explodes[1])
                        && $explodes[1] !== '.' 
                            && $explodes[1] != '..' &&
                                ( (int)$explodes[1] < 1 || (int)$explodes[1] > 4 ))) {
           return "Zip file Error, the layers folder must only contain foldernames with numbers from 1 to 4";
        }

        if (isset($explodes[2]) && !empty($explodes[2])) {
            $extExplodes = explode('.', $explodes[2]);
            if (!isset($extExplodes[1]) || $extExplodes[1] != "png") {
                return "Zip file Error, must only contain png files";
            }
        }
        
        return true;
    }

}
