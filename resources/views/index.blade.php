<html>
<head>
    <title>Laravel Tunga Image Layers Test</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        .stackedImage {
            
        }
        .imageHolder {
            position: relative;
            width: 200px;
            height: 200px;
        }
        .childImage1 {
            background-color: #ccc;
            width: 100px;
            height: 100px;
            position: absolute;
            top: 0;
            left: 0;
            border: 1px red solid;
        }
        .childImage2 {
            background-color: #ccc;
            width: 100px;
            height: 100px;
            position: absolute;
            top: 10px;
            left: 10px;
            border: 1px green solid;
        }
        .childImage3 {
            background-color: #ccc;
            width: 100px;
            height: 100px;
            position: absolute;
            top: 20px;
            left: 20px;
            border: 1px blue solid;
        }
        .childImage4 {
            background-color: #ccc;
            width: 100px;
            height: 100px;
            position: absolute;
            top: 30px;
            left: 30px;
            border: 1px yellow solid;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-primary">
        <a class="navbar-brand" style="color:white" href="/">Home</a>
    </nav>
    <div class="jumbotron jumbotron-fluid">
        <div class="container">
            <h1 class="display-2 text-center">Stacked Images Test</h1>
            <p class="lead text-center">This is a Laravel Stacked Image Layers Test from Tunga</p>

            <div class="panel-body"> 

                <!-- Success alert -->
                @if (!empty($success))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>{{ $success }}</strong>
                </div>
                @endif
            
                <!-- Error alert -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <!-- Div to display stacked images -->
                <div class="row">

                    
                    
                    @foreach($images as $imageStack)
                        <div class="col-md-4">
                            <div class="imageHolder">
                                @foreach($imageStack as $key => $image)
                                    <img class="childImage{{$key+1}}" src="{{asset('images')}}/layers/{{$image}}" />
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                </div>

                <hr/>

                <form action="/regenerate/image" method="POST">
                @csrf
                    <br>
                    <div class="row">
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success">Regenerate</button>
                        </div>

                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadImageModal">Upload Image</button>
                        </div>

                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadZipfileModal">Upload Zip file</button>
                        </div>
                    </div>
                </form>

                <!-- Upload Image Modal -->
                <div class="modal fade" id="uploadImageModal" tabindex="-1" role="dialog" aria-labelledby="uploadImageModal" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Upload Image</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="/upload/image/store" method="POST" enctype="multipart/form-data">
                                @csrf

                                    <br>  
                                    <div class="row mt-4 py-15">
                                        <div class="col-md-12">
                                            <input type="number" 
                                                name="rowIndex" 
                                                min="1" 
                                                max="4" 
                                                placeholder="Row Index starting from 1 and max of 4" 
                                                style="width: 100%">
                                        </div>
                                    </div>

                                    <br>
                                    <div class="row"> 
                                        <div class="col-md-12">
                                            <input type="file" name="image" class="form-control" accept="image/png">
                                        </div>
                                    </div>

                                    <br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-success">Upload</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Zipfile Modal -->
                <div class="modal fade" id="uploadZipfileModal" tabindex="-1" role="dialog" aria-labelledby="uploadZipfileModal" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Upload Zipfile</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="/upload/zip/store" method="POST" enctype="multipart/form-data">
                                @csrf

                                    <br>
                                    <div class="row"> 
                                        <div class="col-md-12">
                                            <input type="file" name="zip" class="form-control" accept="application/octet-stream">
                                        </div>
                                    </div>
                                    
                                    <br>
                                    <label for="" style="color:red">Please Note: the current layers folder will be replaced with the content of the zipfile</label>

                                    <br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-success">Upload</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                
            </div> 
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>  
</html>