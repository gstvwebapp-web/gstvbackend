<style>
.modal-lg {
    max-width: 80% !important;
}
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="modal fade" id="videoModalCenters" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Video Gallery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="height: 500px; overflow: auto;">
      <input type="hidden" id="hiddenvideo" value="0">
      <input type="hidden" id="editorvideoid" value="">
      <div class="col-lg-12">
                    
                    <form action="{{ url('/mediagallery/media-items') }}" method="post" enctype="multipart/form-data" class="dropzone" id="my-dropzones">
                        @csrf
                    
                    </form>
                    
                    <script>
                        Dropzone.options.myDropzones = {
                            paramName: "images", // The name that will be used to transfer the file
                            maxFilesize: 100, // MB
                            acceptedFiles: "video/*",
                            dictDefaultMessage: "Drop videos here or click to upload",
                            addRemoveLinks: true,
                            init: function () {
                                this.on("success", function (file, response) {
                                    file.previewElement.setAttribute("data-image-id", response.imageId);
                                    videoloadMorePostNews(1);
                                    $('#videonewsloadMoreBtn').show();
                                });
                    
                                this.on("removedfile", function (file) {
                                   var imageId = file.previewElement.getAttribute("data-image-id");
                                    $.ajax({
                                        url: "{{ url('/mediagallery/delete-image/') }}/"+imageId,
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function (response) {
                                          videoloadMorePostNews(1);
                                        },
                                        error: function (xhr, status, error) {
                                          console.error(xhr.responseText);
                                        }
                                    });
                                });
                            }
                        };
    function videoloadMorePostNews(nextPage) {
     
    if(nextPage!=1)
    {
        if(nextPage=='')
        {
            var nextPageUrl = document.getElementById('videonewsloadMoreBtn').getAttribute('data-next-page');
            var pageNum = nextPageUrl.split('=').pop(); 
            nextPage = pageNum;
        }
        
    }
    var inputValue = $("#videoSearch").val();
    var optionSearch = $("#videoSearch").data('id');
    if($("#videoSearch").val() != ""){
      var inputValue = $("#videoSearch").val();
      var optionSearch = $("#videoSearch").data('id');
    }
    else {
      var inputValue = "";
      var optionSearch = "";
    }
    $.ajax({
        url: "{{ url('/news/loadmorevideodata?page=') }}"+nextPage+"&nid="+$('#nid').val()+"&inputValue="+inputValue+"&optionSearch="+optionSearch,
        type: 'GET',
        dataType: 'html',
        success: function (response) {
            // Append the new posts to the container
            var responsedata = JSON.parse(response);
            console.log(responsedata);
            var html = '';
            $.each(responsedata.data, function(index, item) {
              var extension = item.photos.split('.').pop();
              
              var pdfval = '';
              var imgpath = item.fullpath;
              
              if(extension=='pdf')
              {
                pdfval = "{{asset('public/media').'/pdficon.jpg'}}";
                imgpath = pdfval;
              } else if(extension=='webp')
              {
                pdfval = imgpath;
                // imgpath = imgpath;
                imgpath = imgpath.replace(/\.[^/.]+$/, '_small$&');
              } else {
                var dotIndex = imgpath.lastIndexOf('.');
                var filePathWithoutExtension;
                if (dotIndex !== -1 && dotIndex > 0) {
                    filePathWithoutExtension = imgpath.slice(0, dotIndex);
                    var extension = imgpath.slice(dotIndex);
                    imgpath = filePathWithoutExtension + '_video_small.gif';
                    var img = new Image();
                    img.onload = function() {
                    };
                    img.onerror = function() {
                        imgpath = filePathWithoutExtension + '_video.gif';
                    };
                    img.src = imgpath;
                }
                pdfval = imgpath;
                
              }
              
              html +='<div class="col-lg-2 mediacls">'
              html +='<img style="cursor:pointer" id="photo_'+item.id+'" src="'+imgpath+'" onclick="videoSelect(\'' + item.fullpath + '\', \'' + pdfval + '\', \'photo_' + item.id + '\')" class="img-fluid rounded" />'
              html +='</div>'
            });
            if(nextPage!=1)
            {
                $('#videomediaContainer').append(html);
            } else {
                $('#videoList').html(html);
            }
            
            var nextPageUrl = responsedata.next_page_url;
            $('#videonewsloadMoreBtn').show();
            if(nextPageUrl==null)
            {
              $('#videonewsloadMoreBtn').hide();
            }
            $('#videonewsloadMoreBtn').attr('data-next-page', nextPageUrl);
        },
        error: function (xhr, status, error) {
            console.error('Error loading more posts:', error);
        }
    });
}


                    </script>
                    
      
      </div>
      <div class="row pt-2 pb-2">
      <div class="col-lg-8"></div>
        <div class="col-lg-4"><input type="text" class="form-control" name="videoSearch" placeholder="Enter Search Keyword" data-id="video" id="videoSearch" value="" /></div>
      </div>
      <div class="row" id="videoList">
       @foreach($data['selectedvideoMedia'] as $item)
              @php
                $extension = pathinfo($item->photos, PATHINFO_EXTENSION);
                $filePathWithoutExtension = pathinfo($item->fullpath, PATHINFO_DIRNAME) . '/' . pathinfo($item->fullpath, PATHINFO_FILENAME);
                $filePathWithoutExtension = $filePathWithoutExtension.'_video.gif';
                $editURL = dirname(request()->path());
                $imgselected = [];
                if($editURL=='news/edit') {
                  $imgselected = $data['news']->videoURL;
                }
                $selectimgCls = '';
              @endphp
              @if ($item->fullpath==$imgselected)
                @php
                  $selectimgCls = 'green-border';
                @endphp
              @endif
                  <div  class="col-lg-2 mediacls">
                    @if($extension=='pdf')
                    <img style="cursor:pointer" id="photo_{{$item->id}}" onclick="videoSelect('{{$item->fullpath}}','{{asset('public/media').'/pdficon.jpg'}}','photo_{{$item->id}}')" src="{{asset('public/media').'/pdficon.jpg'}}" class="img-fluid rounded {{ $selectimgCls }}" />
                    @elseif($extension=='webp')
                    <img style="cursor:pointer" id="photo_{{$item->id}}" onclick="videoSelect('{{$item->fullpath}}','','photo_{{$item->id}}')" src="{{$item->fullpath}}" class="img-fluid rounded {{ $selectimgCls }}" />
                    @else
                    <img style="cursor:pointer" id="photo_{{$item->id}}" onclick="videoSelect('{{$item->fullpath}}','{{$filePathWithoutExtension}}','photo_{{$item->id}}')" src="{{$filePathWithoutExtension}}" class="img-fluid rounded {{ $selectimgCls }}" />
                    @endif
                  </div>
                
                @endforeach

                {{--  @foreach($data['videomedia'] as $item)
              @php
                $extension = pathinfo($item->photos, PATHINFO_EXTENSION);
                $filePathWithoutExtension = pathinfo($item->fullpath, PATHINFO_DIRNAME) . '/' . pathinfo($item->fullpath, PATHINFO_FILENAME);
                $filePathWithoutExtension = $filePathWithoutExtension.'_video.gif';
                $editURL = dirname(request()->path());
                $imgselected = [];
                if($editURL=='news/edit') {
                  $imgselected = explode(',', $data['news']->featureImage);
                }
                $selectimgCls = '';
              @endphp
              @if (in_array($item->fullpath, $imgselected))
                @php
                  $selectimgCls = 'green-border';
                @endphp
              @endif
                  <div  class="col-lg-2 mediacls">
                    @if($extension=='pdf')
                    <img style="cursor:pointer" id="photo_{{$item->id}}" onclick="videoSelect('{{$item->fullpath}}','{{asset('public/media').'/pdficon.jpg'}}','photo_{{$item->id}}')" src="{{asset('public/media').'/pdficon.jpg'}}" class="img-fluid rounded {{ $selectimgCls }}" />
                    @elseif($extension=='webp')
                    <img style="cursor:pointer" id="photo_{{$item->id}}" onclick="videoSelect('{{$item->fullpath}}','','photo_{{$item->id}}')" src="{{$item->fullpath}}" class="img-fluid rounded {{ $selectimgCls }}" />
                    @else
                    <img style="cursor:pointer" id="photo_{{$item->id}}" onclick="videoSelect('{{$item->fullpath}}','{{$filePathWithoutExtension}}','photo_{{$item->id}}')" src="{{$filePathWithoutExtension}}" class="img-fluid rounded {{ $selectimgCls }}" />
                    @endif
                  </div>
                
                @endforeach
              --}}
                
            </div>
            <div class="row" id="videomediaContainer"></div>
            <div class="row">
                <div class="col-lg-12">
                <button id="videonewsloadMoreBtn" style="display:none;" class="loadmorecls" data-next-page="{{ $data['videomedia']->nextPageUrl() }}">Load More</button>
              </div>
            </div>
              </div>
      
    </div>
  </div>
</div>
