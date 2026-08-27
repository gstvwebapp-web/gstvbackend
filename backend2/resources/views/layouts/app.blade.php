<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - Admin</title>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta content="" name="description">
  <meta content="" name="keywords">
  <meta name="base-url" content="{{ config('app.backend_url') }}">
  <!-- Favicons -->
  <link href="{{ URL::asset('public/assets/img/favicon.png') }}" rel="icon">
  <link href="{{ URL::asset('public/assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Example using Google Fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;700&display=swap" />

  <!-- Vendor CSS Files -->
  <link href="{{ URL::asset('public/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ URL::asset('public/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

  <link href="{{ URL::asset('public/assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{ URL::asset('public/assets/css/bootstrap-tagsinput.css') }}" rel="stylesheet">
  <link href="{{ URL::asset('public/assets/css/style.css') }}" rel="stylesheet">
  <style>
  #loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #000; 
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999; 
    opacity: 0.8;
}

.loader-content {
  text-align: center;
  padding: 20% 25%;
  background-color: #000; 
  color:#fff;
  font-size:15px;
}
</style>
</head>

<body>
<div id="loader" style="display: none;">
    <div class="loader-content">
        Loading...
    </div>
</div>
<!-- ======= Header ======= -->
@include('include.header')
<!-- ======= Sidebar ======= -->
 @include('include.sidebar')
<!-- Main Part -->
@yield('content')
<!-- ======= Footer ======= -->
@include('include.footer')
@php
    $currentPath = request()->path();
    $trimmedUrl = dirname($currentPath); 
    $currentDateTime = now();
@endphp
<!-- Vendor JS Files -->
<script src="{{ URL::asset('public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ URL::asset('public/assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
<script src="{{ URL::asset('public/assets/vendor/tinymce/tinymce.min.js') }}"></script>
<script src="{{ URL::asset('public/assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ URL::asset('public/assets/js/bootstrap-tagsinput.min.js') }}"></script>
<script src="{{ URL::asset('public/assets/js/Sortable.min.js') }}"></script>
<link href="{{ URL::asset('public/assets/css/select2.min.css') }}" rel="stylesheet" />
<script src="{{ URL::asset('public/assets/js/select2.min.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ URL::asset('public/assets/css/example-styles.css') }}">

<script type="text/javascript" src="{{ URL::asset('public/assets/js/jquery.multi-select.js') }}"></script>
@if($currentPath=='general'  || $currentPath=='adsimpression/graph')
<script type="text/javascript" src="{{ URL::asset('public/assets/js/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('public/assets/js/dataTables.buttons.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('public/assets/js/buttons.html5.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('public/assets/js/jszip.min.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ URL::asset('public/assets/css/jquery.dataTables.min.css') }}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
@endif
<!-- Template Main JS File -->
 <!-- DataTables CSS -->
@if($currentPath=='news' || $currentPath=='sidemenu' || $currentPath=='webstory' || $currentPath=='usernews' || $currentPath =="frontend-users"  || $currentPath =="category" || $currentPath =="pages" || $currentPath =="user" || $currentPath =="poll" || $currentPath=="viewexport" || $currentPath =="module" || $currentPath =="roles" || $currentPath =="tags" || $currentPath == "votegame" || $currentPath == "adsimpression" || $currentPath == "ads" || $currentPath =="graphic" || $currentPath =="iframe" || $currentPath =="feedback"  || $currentPath =="electionresults" || $currentPath="electionresults/electionstar" || $currentPath =="gift" || $currentPath =="giftreedem" || $currentPath =="electionresultmodule")
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="{{ URL::asset('public/assets/js/tables.js') }}?v={{ time() }}"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
@endif
<script src="{{ URL::asset('public/assets/js/main.js') }}"></script>
<script>
  
  @if($trimmedUrl=='user/profile' || $currentPath=='viewexport' || $currentPath=='adsimpression'  || $currentPath=='adsimpression/graph')
    var dynamicTitle = '';
    var dynamicFileName = "{{ 'Reports_'.$currentDateTime->format('d-m-Y') }}";
   @if($trimmedUrl=='user/profile')
      var dynamicTitle = "{{ $data['user'][0]->name }}";
      var dynamicFileName = "{{ str_replace(' ', '_', trim($data['user'][0]->name)).'_'.$currentDateTime->format('d-m-Y') }}";
   @endif
   $(document).ready(function() {
   /*var userprofiletable = $('#userprofile').DataTable({
        dom: 'lBfrtip', 
        lengthMenu: [10, 25, 50, 100], 
        pageLength: 25,
        buttons: [
            {
                extend: 'excelHtml5',
                title: dynamicTitle, 
                text: 'Export to Excel',
                filename: function() { 
                    return dynamicFileName;
                }
            }
        ]
    });*/
  	

var table = $('#adsimpression').DataTable({
            dom: 'lBfrtip',
            lengthMenu: [10, 25, 50, 100],
            pageLength: 25,
            // searching: false, // Disable DataTables default search feature
            buttons: [{
                extend: 'pdfHtml5',
                text: 'Export to PDF',
                title: 'Ads Report', 
                customize: function(doc) {
                    // PDF customization code
                    doc.content[1].table.widths = ['5%', '45%', '15%', '15%', '20%']; // Adjust the widths as per your requirement
                    doc.styles.tableHeader.alignment = 'center';
                    doc.styles.tableHeader.fontSize = 10;
                    var objLayout = {};
                    objLayout['hLineWidth'] = function(i) {
                        return .5;
                    };
                    objLayout['vLineWidth'] = function(i) {
                        return .5;
                    };
                    objLayout['hLineColor'] = function(i) {
                        return '#aaa';
                    };
                    objLayout['vLineColor'] = function(i) {
                        return '#aaa';
                    };
                    objLayout['paddingLeft'] = function(i) {
                        return 4;
                    };
                    objLayout['paddingRight'] = function(i) {
                        return 4;
                    };
                    objLayout['paddingTop'] = function(i) {
                        return 2;
                    };
                    objLayout['paddingBottom'] = function(i) {
                        return 2;
                    };
                    doc.content[1].layout = objLayout;
                }
            }]
        });

    // Date range filtering
    $('#startDate, #endDate').change(function() {
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();

        // Redraw the DataTable with the new filters
        table.draw();
    });

    // Add custom filtering function
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();
            var dateStr = data[4]; // Assuming 'Date' column is the 5th column (index 4)
            
            // Convert date strings to Date objects for comparison
            var startDateObj = startDate ? new Date(startDate) : null;
            var endDateObj = endDate ? new Date(endDate) : null;
            var dateObj = dateStr ? new Date(dateStr.split('-').reverse().join('-')) : null; // Convert 'd-m-Y' to 'Y-m-d' format

            // Perform date comparison
            if ((!startDateObj || dateObj >= startDateObj) && (!endDateObj || dateObj <= endDateObj)) {
                return true;
            }
            return false;
        }
    );

    // Apply date range filter on table draw
    $('#startDate, #endDate').change(function() {
        table.draw();
    });
  });
@endif
@if($currentPath=='general')
  var dynamicTitle = "Election";
  var dynamicFileName = "{{ 'election'.$currentDateTime->format('d-m-Y') }}";
  $(document).ready(function() {
    $('#electiontabledata').DataTable({
      dom: 'lBfrtip', 
      lengthMenu: [10, 25, 50, 100], 
      pageLength: 25,
      buttons: [
        {
              extend: 'excelHtml5',
              title: dynamicTitle,
              text: 'Export to Excel',
              filename: function() {
                  return dynamicFileName;
              }
        }
      ]
    });
  });
@endif

    // Attach input event listener to the search input
    $('#mediaSearch, #videoSearch').on('input', function() {
        // Get the input value
        var inputValue = $(this).val();
      	var optionSearch = $(this).data('id');
        // Check if the input has at least 3 characters
        if (inputValue.length >= 3) { $('#imageList').html('');
            // Make an AJAX call
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: "{{ url('/mediagallery/mediaSearch/') }}/", // Replace with your actual backend endpoint
                method: 'POST', // or 'GET', depending on your backend
                data: {
                    _token: csrfToken,
                    txtval:inputValue,
                  	optionSearch:optionSearch
                },
                success: function(response) {
                 $('#imageList').html('');
                  var html = '';
                  if(response!='') {
                    $.each(response.data, function(index, item) {
                       if (!item.photos) {
                          console.error('Missing "photos" property in response:', item);
                          return; // Skip this item if "photos" is missing
                      }

                      var extension = item.photos.includes('.') ? item.photos.split('.').pop().toLowerCase() : '';  
                      // var extension = item.photos.split('.').pop();
                      var pdfval = '';
                      var imgpath = item.fullpath;
                      if(extension=='pdf')
                      {
                        pdfval = "{{asset('public/media').'/pdficon.jpg'}}";
                        imgpath = pdfval;
                      } else if(extension == 'jpg' || extension == 'jpeg' || extension == 'png' || extension == 'gif' || extension =='webp')
                      {
                        pdfval = imgpath;
                        imgpath = imgpath;
                      } else {
                        var dotIndex  = imgpath.lastIndexOf('.');
                        var filePathWithoutExtension;
                        if (dotIndex !== -1 && dotIndex > 0) {
                          filePathWithoutExtension = imgpath.slice(0, dotIndex);
                        } 
                        pdfval = filePathWithoutExtension+'_video.gif';
                        imgpath = pdfval;
                      }
                      
                      html +='<div class="col-lg-2 mediacls">'
                      html +='<img style="cursor:pointer" id="photo_'+item.id+'" src="'+imgpath+'" onclick="ImageSelect(\'' + item.fullpath + '\', \'' + pdfval + '\', \'photo_' + item.id + '\')" class="img-fluid rounded" />'
                      html +='</div>'
                    });
                   
                    // $('#imageList').html(html);
                    $('#newsloadMoreBtn').show();
                    if(optionSearch == "video"){
                      $("#videoList").hide();
                      $("#videomediaContainer").show();
                      $('#videomediaContainer').html(html);
                    } else {
                      $("#mediaList").hide();
                      $("#mediaContainer").show();
                      $('#mediaContainer').html(html);
                    }
                } else {
                  html +='<div class="col-lg-12 mediacls">'
                  html +='No record founds!'
                  html +='</div>'
                  $('#imageList').html(html);
                }
                  
              },
                error: function(error) {
                    console.error('Error:', error);
                }
            });
        } else {
            if(optionSearch=='video') {
              $("#videoList").show();
              $("#videomediaContainer").html('');
              videoloadMorePostNews(1);
            } else {
              $("#mediaList").show();
              $("#mediaContainer").html('');
              loadMorePostNews(1);
            }
        }
    });
   
 

</script>
@php
    $currentPath = request()->path();
    $trimmedUrl = dirname($currentPath); 
@endphp
@if($trimmedUrl=='news/edit')
@php
  $imagefunArray = explode(',', $data['news']->featureImage);
  foreach($imagefunArray as $imagePath) {
    $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
    if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif' || $extension =='webp')
    {
      $filePathWithoutExtension = $imagePath; 
    } else {
      $filePathWithoutExtension = pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . pathinfo($imagePath, PATHINFO_FILENAME);
      $filePathWithoutExtension = $filePathWithoutExtension.'_video.gif';
    }
@endphp
<script>
  ImageSelect('{{$imagePath}}','{{$filePathWithoutExtension}}','3');
</script>
@php
  }
@endphp
@endif

<script>
    function handleFileSelect(event) {
        const files = event.target.files;
        const container = document.getElementById('fileContainer');

        for (const file of files) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const fileItem = document.createElement('div');
                fileItem.classList.add('file-item');
                fileItem.classList.add('col-md-6');
                
                const image = document.createElement('img');
                image.src = e.target.result;

                const titleInput = document.createElement('textarea');
                titleInput.name = 'titles[]'; // Set the name attribute if needed
                titleInput.placeholder = 'Enter the title...'; // Set a placeholder if needed
                titleInput.classList.add('form-control'); 

                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.innerHTML = '<i class="bi bi-x-circle"></i>';
                deleteButton.classList.add('webbtn');
                deleteButton.onclick = function () {
                    container.removeChild(fileItem);
                };
                fileItem.appendChild(image);
                fileItem.appendChild(titleInput);
                fileItem.appendChild(deleteButton);
                container.appendChild(fileItem);
            };
            reader.readAsDataURL(file);
        }
    }
    function removeFileSelect(rowid) {
      $("#"+rowid).remove();
    }
    function handleFileEpaperSelect(event) {
        const files = event.target.files;
        const container = document.getElementById('fileContainer');

        for (const file of files) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const fileItem = document.createElement('div');
                fileItem.classList.add('file-item');
                fileItem.classList.add('col-md-2');
                
                const image = document.createElement('img');
                image.src = e.target.result;

                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.innerHTML = '<i class="bi bi-x-circle"></i>';
                deleteButton.classList.add('epaperbtn');
                deleteButton.onclick = function () {
                    container.removeChild(fileItem);
                };
                fileItem.appendChild(image);
                fileItem.appendChild(deleteButton);
                container.appendChild(fileItem);
            };
            reader.readAsDataURL(file);
        }
    }
    function removeFileEpaperSelect(rowid) {
      $("#"+rowid).remove();
    }
    
</script>
    <script>
    $(document).ready(function () {
        // Make the file container sortable
        new Sortable(document.getElementById('fileContainer'), {
            handle: '.file-item',
            animation: 150,
            onEnd: function (evt) {
                // Add any additional logic here
                console.log('New Order:', evt.from.children);
            }
        });
        
    });
</script>


</body>

</html>