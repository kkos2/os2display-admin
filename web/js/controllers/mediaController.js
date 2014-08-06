/**
 * @file
 * Images controller handles the display, selection and upload of image.
 */

ikApp.controller('MediaController', function ($scope, $fileUploader, imageFactory, $location) {
  // Setup some default configuration.
  $scope.images = [];
  $scope.steps = 2;
  $scope.currentStep = 1;

  // Setup default search options.
  $scope.search = {
    "fields": 'title',
    "text": '',
    "sort": {
      "created_at" : {
        "order": "desc"
      }
    }
  };

  /**
   * Updates the images array by sending a search request.
   */
  var updateImages = function() {
    imageFactory.searchImages($scope.search).then(
      function(data) {
        console.log(data);
        $scope.images = data;
      }
    );
  };

  // Send the default search query.
  updateImages();

  /**
   * Changes the sort order and updated the images.
   *
   * @param sort
   *   Field to sort on.
   * @param sortOrder
   *   The order to sort in 'desc' or 'asc'.
   */
  $scope.setSort = function(sort, sortOrder) {
    $scope.search.sort = {};
    $scope.search.sort[sort] = {
      "order": sortOrder
    };


    updateImages();
  };

  // Hook into the search field.
  $('.js-text-field').off("keyup").on("keyup", function() {
    updateImages();
  });

  // Creates a uploader
  var uploader = $scope.uploader = $fileUploader.create({
    scope: $scope,
    url: '/api/media'
  });

  $scope.selectFiles = function() {
      angular.element( document.querySelector( '#select-files' )).click();
  };

  $scope.isImage = function(item) {
    var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
    return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
  };

  // Images only
  uploader.filters.push(function(item /*{File|HTMLInputElement}*/) {
    var type = uploader.isHTML5 ? item.type : '/' + item.value.slice(item.value.lastIndexOf('.') + 1);
    type = '|' + type.toLowerCase().slice(type.lastIndexOf('/') + 1) + '|';
    return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
  });


  // REGISTER HANDLERS

  uploader.bind('afteraddingfile', function (event, item) {
    console.info('After adding a file', item);
  });

  uploader.bind('whenaddingfilefailed', function (event, item) {
    console.info('When adding a file failed', item);
  });

  uploader.bind('afteraddingall', function (event, items) {
    console.info('After adding all files', items);
      $scope.currentStep++;
  });

  uploader.bind('beforeupload', function (event, item) {
    console.info('Before upload', item);
  });

  uploader.bind('progress', function (event, item, progress) {
    console.info('Progress: ' + progress, item);
  });

  uploader.bind('success', function (event, xhr, item, response) {
    console.info('Success', xhr, item, response);
  });

  uploader.bind('cancel', function (event, xhr, item) {
    console.info('Cancel', xhr, item);
  });

  uploader.bind('error', function (event, xhr, item, response) {
    console.info('Error', xhr, item, response);
  });

  uploader.bind('complete', function (event, xhr, item, response) {
    console.info('Complete', xhr, item, response);
  });

  uploader.bind('progressall', function (event, progress) {
    console.info('Total progress: ' + progress);
  });

  uploader.bind('completeall', function (event, items) {
    console.info('Complete all', items);

    //updateImages();
    $location.path('/media');
    $scope.$apply();

    console.info("Testing2");

  });
});
