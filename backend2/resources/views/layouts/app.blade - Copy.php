<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - Admin</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

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
@if($trimmedUrl=='user/profile' || $currentPath=='viewexport' || $currentPath=='usernews' || $currentPath=='general'  || $currentPath=='adsimpression'  || $currentPath=='adsimpression/graph')
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
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="{{ URL::asset('public/assets/js/main.js') }}"></script>

<script>
  @if($currentPath=='news')
    
    $(document).ready(function() {
        
      $('#newstabledata1').DataTable({
        processing: true,   // Show processing indicator
        serverSide: true,   // Enable server-side processing
        lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ], // Define dropdown values
        pageLength: 25,
        order: [], // Disable default sorting
        ajax: {
            url: "{{ url('/news/fetchData/') }}",
            type: 'GET', // HTTP method
            data: function(d) {
                // Pass search parameters to server-side
                d.title = $('#title').val();        // Search by title
                d.userID = $('select[name="userID"]').val(); // Search by userID
                d.catID = $('select[name="catID"]').val();   // Search by catID
            },
            error: function(xhr, error, code) {
                console.error("AJAX Error:", error);
                console.error("XHR Response:", xhr.responseText);
            }
        },
        columns: [
            { data: 'rowNumber'},
            { data: 'title' },
            { data: 'categoryNames' },
            { data: 'viewer' },
            { data: 'authorName' },
            { data: 'created_at' },
            { data: 'action', orderable: false, searchable: false } // Disable sorting/searching for 'action'
        ],
        createdRow: function(row, data, dataIndex) {
             const status = $("#statustype").val();
             const table = $('#newstabledata1').DataTable();
             const pageInfo = table.page.info(); 
             const currentPage = pageInfo.page + 1; 
            
             if(status == "Active"){
              $(row).attr('data-id', data.id);
              $(row).attr('data-top', data.topnewsOrder); // Correctly set data-top attribute
              if(currentPage==1) {
                if($('select[name="catID"]').val()!='') {
                  if (dataIndex < 4) {
                      $(row).attr('style', 'background:#dad7d7;');
                  }
                } else {
                  if (dataIndex < 10) {
                      $(row).attr('style', 'background:#dad7d7;');
                  }
                }
              }
             }
             if(status == "video"){
              $(row).attr('data-id', data.id);
              $(row).attr('data-top', data.topnewsOrder); // Correctly set data-top attribute
                if(currentPage==1) {
                  if (dataIndex < 4) {
                      $(row).attr('style', 'background:#dad7d7;');
                  }
                }
             }
        },
        drawCallback: function(settings) {
            // Remove 'odd' and 'even' classes from all rows
            $('#newstabledata1 tbody tr').removeClass('odd even');
        },
        initComplete: function() {
          const tableBody = document.querySelector('#newstabledata1 tbody');
          
            let oldOrder = Array.from(tableBody.children).map(row => row.dataset.id);
            let topnewsOrder = Array.from(tableBody.children).map(row => row.dataset.top);
            
            let tableId = 'newstabledata1'; // Table ID
            let activePageValue = $('#newstabledata1').DataTable().page.info().page + 1; // Current page
            let entriesPerPage = $('#newstabledata1').DataTable().page.info().length; // Page length
            if (tableBody) {
                new Sortable(tableBody, {
                    animation: 150,
                    handle: 'td:first-child',
                    onUpdate: function(evt) {
                        const newOrder = Array.from(tableBody.children).map(row => row.dataset.id);
                        let topnewsOrder = $('select[name="catID"]').val();
                        updateOrderInDatabase1(newOrder, tableId, oldOrder, topnewsOrder, activePageValue, entriesPerPage);
                    }
                });
            } else {
                console.error("tableBody not found!");
            }
        }
    });

    function updateOrderInDatabase1(newOrder, tableId, oldOrder, tabletop, activePageValue, entriesPerPage) {
      
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
          var urls = '';
          if(tableId=='categorytabledata') {
            urls = '//'+window.location.hostname+'/backend2/category/catorder';
          } else if(tableId=='newstabledata1') {
            urls = 'http://localhost/gstvnews/backend2/news/newsorder';
          } else if(tableId=='webstorytabledata') {
            urls = '//'+window.location.hostname+'/backend2/webstory/webstoryorder';
          }
          // Make an Ajax request to your Laravel backend
          $.ajax({
            type: 'POST',
            url: urls, // Replace with your Laravel route
            headers: {
              'X-CSRF-TOKEN': csrfToken,
            },
            data: {
              newOrder: newOrder,
              oldOrder: oldOrder,
              tabletop: tabletop,
              pageNum: activePageValue,
              entriesPerPage:entriesPerPage,
            },
            success: function (response) {
              console.log(response);
            },
            error: function (error) {
              console.error(error);
            },
          });
        }

    $('#newsformsearch').on('submit', function(e) {
        e.preventDefault();
        $('#newstabledata1').DataTable().ajax.reload(); // Reload DataTable
    });

    $('#unpublishButton').on('click', function () {
      $("#statustype").val('Inactive');
      $(".publishnewsdata").addClass('btn-secondary').removeClass('btn-success');
      $("#unpublishButton").removeClass('btn-secondary').addClass('btn-success');
        const table = $('#newstabledata1').DataTable();
        table.ajax.url('{{ url("/news/fetchData") }}?status=Inactive').load();
    });
    $('#scheduleButton').on('click', function () {
      $("#statustype").val('Schedule');
      $(".publishnewsdata").addClass('btn-secondary').removeClass('btn-success');
      $("#scheduleButton").removeClass('btn-secondary').addClass('btn-success');
        const table = $('#newstabledata1').DataTable();
        table.ajax.url('{{ url("/news/fetchData") }}?status=Schedule').load();
    });
    $('#activeButton').on('click', function () {
      $("#statustype").val('video');
      $(".publishnewsdata").addClass('btn-secondary').removeClass('btn-success');
      $("#activeButton").removeClass('btn-secondary').addClass('btn-success');
        const table = $('#newstabledata1').DataTable();
        table.ajax.url('{{ url("/news/fetchData") }}?status=Active').load();
    });
    $('#videoButton').on('click', function () {
      $("#selectcatID").val('9');
      $(".publishnewsdata").addClass('btn-secondary').removeClass('btn-success');
      $("#videoButton").removeClass('btn-secondary').addClass('btn-success');
        const table = $('#newstabledata1').DataTable();
        table.ajax.url('{{ url("/news/fetchData") }}?status=video').load();
    });
  @endif
  @if($trimmedUrl=='user/profile' || $currentPath=='viewexport' || $currentPath=='adsimpression'  || $currentPath=='adsimpression/graph')
    var dynamicTitle = '';
    var dynamicFileName = "{{ 'Reports_'.$currentDateTime->format('d-m-Y') }}";
   @if($trimmedUrl=='user/profile')
      var dynamicTitle = "{{ $data['user'][0]->name }}";
      var dynamicFileName = "{{ str_replace(' ', '_', trim($data['user'][0]->name)).'_'.$currentDateTime->format('d-m-Y') }}";
   @endif
  
   var userprofiletable = $('#userprofile').DataTable({
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
@endif
@if($currentPath=='general')
  var dynamicTitle = "Election";
  var dynamicFileName = "{{ 'election'.$currentDateTime->format('d-m-Y') }}";
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
@endif

  @if($currentPath=='usernews')
  var dynamicTitle = "Underreview Users News";
  var dynamicFileName = "{{ 'underreview_users_'.$currentDateTime->format('d-m-Y') }}";
  $('#usernews').DataTable({
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
  $('#userActivenews').DataTable({
      dom: 'lBfrtip', 
      lengthMenu: [10, 25, 50, 100], 
      pageLength: 25,
      buttons: [
        {
              extend: 'excelHtml5',
              title: "Active Users News",
              text: 'Export to Excel',
              filename: function() {
                  return "{{ 'active_users_'.$currentDateTime->format('d-m-Y') }}";
              }
        }
      ]
   });
  $('#userRejectednews').DataTable({
      dom: 'lBfrtip', 
      lengthMenu: [10, 25, 50, 100], 
      pageLength: 25,
      buttons: [
        {
              extend: 'excelHtml5',
              title: "Rejectd Users News",
              text: 'Export to Excel',
              filename: function() {
                  return "{{ 'rejected_users_'.$currentDateTime->format('d-m-Y') }}";
              }
        }
      ]
   });
  @endif
     
    // Attach input event listener to the search input
    $('#mediaSearch, #videoSearch').on('input', function() {
        // Get the input value
        var inputValue = $(this).val();
      	var optionSearch = $(this).data('id');
        // Check if the input has at least 3 characters
        if (inputValue.length >= 3) {
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
              
            var html = '';
            if(response!='') {
            $.each(response, function(index, item) {
              var extension = item.photos.split('.').pop();
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
            $('#imageList').html(html);
            $('#newsloadMoreBtn').hide();
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
            // Clear the results if the input has less than 3 characters
            if(optionSearch=='video') {
              videoloadMorePostNews(1);
            } else {
              loadMorePostNews(1);
            }
        }
    });
   
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