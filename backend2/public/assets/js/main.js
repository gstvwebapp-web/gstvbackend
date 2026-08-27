/**
* Template Name: NiceAdmin - v2.2.2
* Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/
(function() {
  "use strict";
  
  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    if (all) {
      select(el, all).forEach(e => e.addEventListener(type, listener))
    } else {
      select(el, all).addEventListener(type, listener)
    }
  }

  /**
   * Easy on scroll event listener 
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }

  /**
   * Sidebar toggle
   */
  if (select('.toggle-sidebar-btn')) {
    on('click', '.toggle-sidebar-btn', function(e) {
      select('body').classList.toggle('toggle-sidebar')
    })
  }

  /**
   * Search bar toggle
   */
  if (select('.search-bar-toggle')) {
    on('click', '.search-bar-toggle', function(e) {
      select('.search-bar').classList.toggle('search-bar-show')
    })
  }

  /**
   * Navbar links active state on scroll
   */
  let navbarlinks = select('#navbar .scrollto', true)
  const navbarlinksActive = () => {
    let position = window.scrollY + 200
    navbarlinks.forEach(navbarlink => {
      if (!navbarlink.hash) return
      let section = select(navbarlink.hash)
      if (!section) return
      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        navbarlink.classList.add('active')
      } else {
        navbarlink.classList.remove('active')
      }
    })
  }
  window.addEventListener('load', navbarlinksActive)
  onscroll(document, navbarlinksActive)

  /**
   * Toggle .header-scrolled class to #header when page is scrolled
   */
  let selectHeader = select('#header')
  if (selectHeader) {
    const headerScrolled = () => {
      if (window.scrollY > 100) {
        selectHeader.classList.add('header-scrolled')
      } else {
        selectHeader.classList.remove('header-scrolled')
      }
    }
    window.addEventListener('load', headerScrolled)
    onscroll(document, headerScrolled)
  }
  
  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add('active')
      } else {
        backtotop.classList.remove('active')
      }
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }

  /**
   * Initiate tooltips
   */
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })

  /**
   * Initiate quill editors
   */
  if (select('.quill-editor-default')) {
    new Quill('.quill-editor-default', {
      theme: 'snow'
    });
  }

  if (select('.quill-editor-bubble')) {
    new Quill('.quill-editor-bubble', {
      theme: 'bubble'
    });
  }

  if (select('.quill-editor-full')) {
    new Quill(".quill-editor-full", {
      modules: {
        toolbar: [
          [{
            font: []
          }, {
            size: []
          }],
          ["bold", "italic", "underline", "strike"],
          [{
              color: []
            },
            {
              background: []
            }
          ],
          [{
              script: "super"
            },
            {
              script: "sub"
            }
          ],
          [{
              list: "ordered"
            },
            {
              list: "bullet"
            },
            {
              indent: "-1"
            },
            {
              indent: "+1"
            }
          ],
          ["direction", {
            align: []
          }],
          ["link", "image", "video"],
          ["clean"]
        ]
      },
      theme: "snow"
    });
  }

  /**
   * Initiate TinyMCE Editor
   */

  var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

  tinymce.init({
    selector: 'textarea.tinymce-editor',
    plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
    imagetools_cors_hosts: ['picsum.photos'],
    menubar: 'file edit view insert format tools table help',
    menubar: false,
    toolbar: 'code | undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
    toolbar_sticky: true,
    autosave_ask_before_unload: true,
    autosave_interval: '30s',
    autosave_prefix: '{path}{query}-{id}-',
    autosave_restore_when_empty: false,
    autosave_retention: '2m',
    image_advtab: true,
    link_list: [],
    image_list: [],
    image_class_list: [],
    importcss_append: true,
    file_picker_callback: function(callback, value, meta) {
     if (meta.filetype === 'media') {
      $('#videoModalCenters').modal('show');

      $('#videoModalCenters').on('click', 'button.select-media', function() {
        var mediaUrl = $('#mediaUrlInput').val();
        callback(mediaUrl);
        $('#videoModalCenters').modal('hide');
      });
    }
    },
    templates: [],
    template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
    template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
    height: 600,
    image_caption: true,
    quickbars_selection_toolbar: 'bold italic | h1 h2 h3 h4 h5 h6 blockquote quickimage quicktable',
    noneditable_noneditable_class: 'mceNonEditable',
    toolbar_mode: 'sliding',
    contextmenu: 'link image imagetools table',
    skin: useDarkMode ? 'oxide-dark' : 'oxide',
    content_css: useDarkMode ? 'dark' : 'default',
    relative_urls: false,            // Use absolute URLs
    remove_script_host: false,       // Retain the full URL including the host
    convert_urls: true,              // Convert URLs to absolute
    setup: function(editor) {
      editor.on('change', function() {
          tinymce.triggerSave();
      });
      /*editor.on('BeforeSetContent', function(event) {
        event.content = event.content.replace(/<a.*?>(.*?)<\/a>/gi, '$1');
      });
      editor.on('init', function() {
        editor.parser.removeRule('auto_link');
      });
      editor.ui.registry.addButton('twitter', {
          text: 'Social Media',
          icon: 'embed-page',
          context: 'twitter',
          onAction: function(_) {
              $('#twitterModal').modal('show');
          }
      });*/
      editor.ui.registry.addIcon('custom-media-icon', '<svg height="24" width="24" ><path d="M4 3h16c.6 0 1 .4 1 1v16c0 .6-.4 1-1 1H4a1 1 0 01-1-1V4c0-.6.4-1 1-1zm1 2v14h14V5H5zm4.8 2.6l5.6 4a.5.5 0 010 .8l-5.6 4A.5.5 0 019 16V8a.5.5 0 01.8-.4z"></path></svg>');
    editor.ui.registry.addButton('openmedia', {
      icon: 'custom-media-icon',
      tooltip: 'Insert media',
      onAction: function() {
       var currentEditorId = tinymce.activeEditor.id;
        $('#videoModalCenters').modal('show');
        $('#hiddenvideo').val(1);
        $("#editorvideoid").val(currentEditorId);
      }
    });
  },
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px } img { max-width: 100%; height: auto; }'
  });

  tinymce.init({
    selector: 'textarea.tinymce-editor-live',
    plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
    imagetools_cors_hosts: ['picsum.photos'],
    menubar: 'file edit view insert format tools table help',
    menubar: false,
    toolbar: 'code | undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
    toolbar_sticky: true,
    autosave_ask_before_unload: true,
    autosave_interval: '30s',
    autosave_prefix: '{path}{query}-{id}-',
    autosave_restore_when_empty: false,
    autosave_retention: '2m',
    image_advtab: true,
    link_list: [],
    image_list: [],
    image_class_list: [],
    importcss_append: true,
    file_picker_callback: function(callback, value, meta) {
     if (meta.filetype === 'media') {
      $('#videoModalCenters').modal('show');

      $('#videoModalCenters').on('click', 'button.select-media', function() {
        var mediaUrl = $('#mediaUrlInput').val();
        callback(mediaUrl);
        $('#videoModalCenters').modal('hide');
      });
    }
    },
    templates: [],
    template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
    template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
    height: 200,
    image_caption: true,
    quickbars_selection_toolbar: 'bold italic | h1 h2 h3 h4 h5 h6 blockquote quickimage quicktable',
    noneditable_noneditable_class: 'mceNonEditable',
    toolbar_mode: 'sliding',
    contextmenu: 'link image imagetools table',
    skin: useDarkMode ? 'oxide-dark' : 'oxide',
    content_css: useDarkMode ? 'dark' : 'default',
    relative_urls: false,            // Use absolute URLs
    remove_script_host: false,       // Retain the full URL including the host
    convert_urls: true,              // Convert URLs to absolute
    setup: function(editor) {
      editor.on('change', function() {
          tinymce.triggerSave();
      });
      /*editor.on('BeforeSetContent', function(event) {
        event.content = event.content.replace(/<a.*?>(.*?)<\/a>/gi, '$1');
      });
      editor.on('init', function() {
        editor.parser.removeRule('auto_link');
      });
      editor.ui.registry.addButton('twitter', {
          text: 'Social Media',
          icon: 'embed-page',
          context: 'twitter',
          onAction: function(_) {
              $('#twitterModal').modal('show');
          }
      });*/
      editor.ui.registry.addIcon('custom-media-icon', '<svg height="24" width="24" ><path d="M4 3h16c.6 0 1 .4 1 1v16c0 .6-.4 1-1 1H4a1 1 0 01-1-1V4c0-.6.4-1 1-1zm1 2v14h14V5H5zm4.8 2.6l5.6 4a.5.5 0 010 .8l-5.6 4A.5.5 0 019 16V8a.5.5 0 01.8-.4z"></path></svg>');
    editor.ui.registry.addButton('openmedia', {
      icon: 'custom-media-icon',
      tooltip: 'Insert media',
      onAction: function() {
        var currentEditorId = tinymce.activeEditor.id;
        $('#videoModalCenters').modal('show');
        $('#hiddenvideo').val(1);
        $("#editorvideoid").val(currentEditorId);
      }
    });
  },
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px } img { max-width: 100%; height: auto; }'
  });

 
tinymce.init({
  selector: 'textarea.newfeatures',
  plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
  imagetools_cors_hosts: ['picsum.photos'],
  menubar: 'file edit view insert format tools table help',
  menubar: false,
  toolbar: 'code | undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
  toolbar_sticky: true,
  autosave_ask_before_unload: true,
  autosave_interval: '30s',
  autosave_prefix: '{path}{query}-{id}-',
  autosave_restore_when_empty: false,
  autosave_retention: '2m',
  image_advtab: true,
  link_list: [],
  image_list: [],
  image_class_list: [],
  importcss_append: true, 
  templates: [],
  template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
  template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
  height: 200,
  image_caption: true,
  quickbars_selection_toolbar: 'bold italic | h1 h2 h3 h4 h5 h6 blockquote quickimage quicktable',
  noneditable_noneditable_class: 'mceNonEditable',
  toolbar_mode: 'sliding',
  contextmenu: 'link image imagetools table',
  skin: useDarkMode ? 'oxide-dark' : 'oxide',
  content_css: useDarkMode ? 'dark' : 'default',
  relative_urls: false,            // Use absolute URLs
  remove_script_host: false,       // Retain the full URL including the host
  convert_urls: true,              // Convert URLs to absolute
  setup: function(editor) {
    editor.on('change', function() {
        tinymce.triggerSave();
    }); 
    
},
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px } img { max-width: 100%; height: auto; }'
});

 $('#addtwittercontent').click(function() {
    var twitterpostId = $('#twitterpost').val();
    var facebookpostId = $('#facebookpost').val();
    var instagrampostId = $('#instagrampost').val();
    var youtubepostId = $('#youtubepost').val();

    var twitterhtml = (twitterpostId != '') ? '<p><blockquote class="twitter-tweet"><a href="https://twitter.com/username/status/' + twitterpostId + '">' + twitterpostId + '</a></blockquote></p>' : '';
    var facebookhtml = (facebookpostId != '') ? '<p><iframe src="https://www.facebook.com/plugins/post.php?href=' + facebookpostId + '" style="width:100%; height:500px; border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allow="encrypted-media"></iframe></p>' : '';
    var instagramhtml = (instagrampostId != '') ? '<p><blockquote class="instagram-media" data-instgrm-permalink="' + instagrampostId + '" data-instgrm-version="14"> Instagram Post : '+instagrampostId+'</blockquote></p>' : '';
    var youtubehtml = (youtubepostId != '') ? '<p><iframe src="https://www.youtube.com/embed/' + youtubepostId + '" style="width:100%; height:400px; border:none;overflow:hidden" frameborder="0" allowfullscreen></iframe></p>' : '';

    var combinedHtml = twitterhtml + facebookhtml + instagramhtml + youtubehtml;

    var editorId = 'descriptioneditor'; // replace with the correct ID of your TinyMCE editor

    // Check if the TinyMCE editor instance exists
    var editor = tinymce.get(editorId);

    if (editor) {
        // Execute mceInsertContent with the combined HTML
        editor.execCommand('mceInsertContent', false, combinedHtml);
    }

    // Clear input values and hide modal
    $('#twitterpost').val('');
    $('#facebookpost').val('');
    $('#instagrampost').val('');
    $('#youtubepost').val('');
    $('.modal').modal('hide');
});



  
  /**
   * Initiate Bootstrap validation check
   */
  var needsValidation = document.querySelectorAll('.needs-validation')

  Array.prototype.slice.call(needsValidation)
    .forEach(function(form) {
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })

    
  /**
   * Initiate Datatables
   */
  const datatables = select('.datatable', true)

  
  document.addEventListener('DOMContentLoaded', function() {
  datatables.forEach(datatable => {
    const myTable = new simpleDatatables.DataTable(datatable, {
      perPage: 20, // Number of items per page
      perPageSelect: [5, 10, 20, 50, 100], // Options for items per page dropdown
      nextText: 'Next', // Text for the "Next" button
      prevText: 'Previous', // Text for the "Previous" button
      firstText: 'First', // Text for the "First" button
      lastText: 'Last', // Text for the "Last" button
      headings: true, // Display column headings
      columns: [
        { select: 0, width: '100px' }, 
        { select: 1, width: '100px' }, 
        { select: 2, width: '100px' }, 
        { select: 3, width: '100px' }, 
      ],
    });
    
    const tableBody = datatable.querySelector('tbody'); // Use the specific datatable's tbody
    const tableBodyID = datatable; // Use the specific datatable
    let oldOrder = Array.from(tableBody.children).map(row => row.dataset.id);
    let topnewsOrder = Array.from(tableBody.children).map(row => row.dataset.top);
   
    new Sortable(tableBody, {
      animation: 150,
      handle: 'th:first-child', // Make the first td the handle
      onUpdate: function (evt) {
        const tableId = tableBodyID.getAttribute('id');
        var activePageValue = $('.dataTable-pagination-list .active').text();
        if(tableId=='categorytabledata' || tableId=='newstabledata' || tableId == 'webstorytabledata') {
          const newOrder = Array.from(tableBody.children).map(row => row.dataset.id);
          const entriesPerPage = myTable.options.perPage; // Get entries per page
          console.log("topnewsOrder Order:", oldOrder);
          console.log("topnewsOrder Order:", newOrder);
          updateOrderInDatabase(newOrder, tableId, oldOrder, topnewsOrder, activePageValue, entriesPerPage);
        }
      },
    });

    function updateOrderInDatabase(newOrder, tableId, oldOrder, tabletop, activePageValue, entriesPerPage) {
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      var urls = '';
      if(tableId=='categorytabledata') {
        urls = '//'+window.location.hostname+'/backend2/category/catorder';
      } else if(tableId=='newstabledata') {
        urls = '//'+window.location.hostname+'/backend2/news/newsorder';
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
  });
})
  $('#catID').select2({
    placeholder: 'Search for categories...',
  })
  $('#subcatID').select2({
    placeholder: 'Search for Sub categories...',
  })
  $('.catidcls').change(function() {
    var catid = $(this).val();
    if ($('#cat'+catid).prop('checked')) {
      $('#subcat_'+catid).show();
      console.log('Checkbox is now checked');
    } else {
      $('#subcat_'+catid).hide();
      console.log('Checkbox is now unchecked');
    }
  })

 

  $('#newstatus').change(function () {
    var statusval = $(this).val();
    $('#scheduledatetxt').hide();
    $('#scheduleDate').prop('required', false);
    if(statusval=='Schedule')
    {
      $('#scheduledatetxt').show();
      $('#scheduleDate').prop('required', true);
    }
  })
  
  $('.homenewscls').click(function() {
      var ishome = $(this).is(':checked');
      var idsValue = $(this).data('ids');
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        $.ajax({
            url: '//'+window.location.hostname+'/backend2/news/tophome',
            method: 'POST', // or 'GET' or any other HTTP method
            headers: {
              'X-CSRF-TOKEN': csrfToken,
            },
            data: {
              newID: idsValue,
              is_home: ishome,
            },
            success: function(response) {
                // Handle the AJAX response
                console.log(response);
            },
            error: function(xhr, status, error) {
                // Handle errors
                console.error(xhr.responseText);
            }
        });   
  })

  
  
  // Store the user's assigned sub-category for auto-selection after AJAX
  var defaultSubcatID = $('#catID').data('default-subcat') || '';
  var defaultCatID = $('#catID').val(); // Get the selected category value

  // Auto-trigger subcategory load on page load if roleID 7 and has default category
  if (defaultCatID && defaultSubcatID) {
    callAjaxFunction(defaultCatID, defaultSubcatID);
  }

  $('#catID').on('select2:select', function (e) {
    var selectedOption = e.params.data.id;
    callAjaxFunction(selectedOption, defaultSubcatID);
  })

  $('.tags-select').select2({
    placeholder: 'Select or type tags',
    tags: true, 
    multiple: true, 
    minimumInputLength: 3,
    ajax: {
        url: '//'+window.location.hostname+'/backend2/news/tagSuggestions', 
        dataType: 'json',
        delay: 250,
        data: function (params) {
            if (params.term && params.term.length < 2) {
                return null; // Prevent sending request if less than 3 characters
            }
            return { q: params.term };
        },
        processResults: function (data) {
            return {
                results: data.map(function(tag) {
                    return {
                        id: tag.id,
                        text: tag.text
                    };
                })
            };
        },
        cache: true
    },
    createTag: function (params) {
        var term = params.term.trim();
        // var term = params.term;
        if (term.length > 2) {
          $.ajax({
              url: '//'+window.location.hostname+'/backend2/news/taginserts', 
              method: 'POST', 
              data: {
                  q: term, 
                  tag:'tags',
                  _token: $('meta[name="csrf-token"]').attr('content') 
              },
              success: function(response) {
                  console.log('Tag created successfully:', response);
              },
              error: function(error) {
                  console.log('Error creating tag:', error);
              }
          });
        }
        return term.length > 2 ? { id: term, text: term } : null;
    }
});



  /**
   * Autoresize echart charts
   */
  const mainContainer = select('#main');
  if (mainContainer) {
    setTimeout(() => {
      new ResizeObserver(function() {
        select('.echart', true).forEach(getEchart => {
          echarts.getInstanceByDom(getEchart).resize();
        })
      }).observe(mainContainer);
    }, 200);
  }

  function getCurrentDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
  window.onload = function() {
    var scheduleDateInput = document.getElementById('scheduleDate');
    if (scheduleDateInput) {
        scheduleDateInput.setAttribute('min', getCurrentDate());
    }
  },
  $('.removeImg').click(function(){
    var imageId = $(this).data('id');
    $.ajax({
        url: '//'+window.location.hostname+'/backend2/mediagallery/delete-image/'+imageId,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            // Handle success
            console.log(response);
            location.reload();
        },
        error: function (xhr, status, error) {
            // Handle error
            console.error(xhr.responseText);
        }
    });
  }),


  $('#loadMoreBtn').click(function() {
    loadMorePosts();
  })

  $('#newsloadMoreBtn').click(function() {
    loadMorePostNews('');
  })
  
  $('#videonewsloadMoreBtn').click(function() {
    videoloadMorePostNews('');
  })
  
  function loadMorePosts() {
      var nextPageUrl = document.getElementById('loadMoreBtn').getAttribute('data-next-page');
      $.ajax({
          url: nextPageUrl,
          type: 'GET',
          dataType: 'html',
          success: function (response) {
              // Append the new posts to the container
              var responsedata = JSON.parse(response);
              var html = '';
              $.each(responsedata.data, function(index, item) {
                var photoTitle = '';
                if(item.photoTitle !== null) { photoTitle = item.photoTitle; }
                var photoAlt = '';
                if(item.photoAlt !== null) { photoAlt = item.photoAlt; }
                var extension = item.photos.split('.').pop();
                var pdfval = '';
                var imgpath = item.fullpath;
                if(extension=='pdf')
                {
                  pdfval = '//'+window.location.hostname+'/backend/public/media/pdficon.jpg';
                  imgpath = pdfval;
                } else if(extension == 'jpg' || extension == 'jpeg' || extension == 'png' || extension == 'gif' || extension =='webp')
                {
                  pdfval = imgpath.replace('.' + extension, '_small.' + extension);
                  imgpath = imgpath.replace('.' + extension, '_small.' + extension);
                } else {
                  var dotIndex  = imgpath.lastIndexOf('.');
                  var filePathWithoutExtension;
                  if (dotIndex !== -1 && dotIndex > 0) {
                    filePathWithoutExtension = imgpath.slice(0, dotIndex);
                  } 
                  pdfval = filePathWithoutExtension+'_video.gif';
                  imgpath = pdfval;
                } 
                html +='<div class="col-lg-2 mediacls mediacls'+item.id+'"><span onclick="mediaimgremove('+item.id+');" class="imgclosecls">X</span><img style="cursor:pointer" src="'+pdfval+'" onclick="toggleDrawer('+item.id+')" class="img-fluid rounded border" /></div>'
                html +='<div class="drawer mediacls'+item.id+'" id="drawer_'+item.id+'">'
                html +='<div class="close-button" onclick="CloseDrawer('+item.id+')">✖</div>'
                html +='<form action="{{ url("/mediagallery/media-update/") }}" method="post" enctype="multipart/form-data">'
                html +='<input type="hidden" name="_token" value="{{ csrf_token() }}" />'
                html +='<div class="drawer-content"><h4>Image Detail</h4>'
                html +='<p><img style="cursor:pointer; max-height: 150px;" src="'+pdfval+'" class="img-fluid rounded border" /></p>'
                //html +='<p><a style="cursor:pointer; color:#840d00" class="removeImg" data-id="'+item.id+'">Delete Image</a></p>'
                html +='<p>Image Title<br/><input type="text" name="photoTitle" class="form-control" value="'+photoTitle+'" /></p>'
                html +='<p>Image Atl<br/><input type="text" name="photoAlt" class="form-control" value="'+photoAlt+'" /><input type="hidden" name="photoid" class="form-control" value="'+item.id+'" /></p>'
                html +='<p>Image Path<br/><input type="text" name="fullpath" class="form-control" value="'+item.fullpath+'" /></p><p><button class="btn btn-primary" type="submit">Update</button></p>'
                html +='</div>'
                html +='</form>'
                html +='</div>'
              });
              $('#mediaContainer').append(html);
              var nextPageUrl = responsedata.next_page_url;
              $('#loadMoreBtn').show();
              if(nextPageUrl==null)
              {
                $('#loadMoreBtn').hide();
              }
              $('#loadMoreBtn').attr('data-next-page', nextPageUrl);
          },
          error: function (xhr, status, error) {
              console.error('Error loading more posts:', error);
          }
      });
  }
    
  // const autoSaveInterval = 3000; // 1 minute
  // const form = $('#newsform');
  // var currentUrl = window.location.href;
  // var substringToCheck = "news/add";

    //  var autoSaveIntervalId;

    // if (currentUrl.includes(substringToCheck)) {
    //     var newssubmitflag = 0;

    //     $("#newsSubmit").click(function(){
    //         newssubmitflag = $(this).val();
    //         clearInterval(autoSaveIntervalId); // Clear the interval when the submit button is clicked
    //     });

    //     function saveDraft() {
    //         var editor = tinymce.get('descriptioneditor').getContent({ format: 'text' }).trim();
    //         if (editor != "" && editor != null && editor != 'undefined') {
    //             $.ajax({
    //                 type: 'POST',
    //                 url: '../news/autosaveDraft',
    //                 data: form.serialize(),
    //                 success: function(response) {
    //                     $('#autosaveid').val(response);
    //                 }
    //             });
    //         }
    //     }

    //     if (newssubmitflag == 0) {
    //         autoSaveIntervalId = setInterval(saveDraft, autoSaveInterval);
    //     }
    // }

})();
var selectedImages = [];
var selectedImagespath = [];
function ImageSelect(paths,extensions,imgid) {
  
  var hiddenimage = $("#hiddenimage").val();
  var editorid = $("#editorid").val();
  if(hiddenimage == 1){
    var editor = tinymce.get(editorid);
    // var editor = getEditorByDataAttribute('data-editor-type', 'description');
    if (editor) {
        $('#imagevideoModalCenter').modal('hide');
        var paths, imageList;
        var imgHtml = '<img src="' + paths + '">';
        editor.insertContent(imgHtml);
    } else {
        return;
    }
  } else {
    if ($.fn.modal) {
        $('#imagevideoModalCenter').modal('hide');
    } else {
        return;
    }
    var selectedImageContainer = $('#selectedImageContainer');
    var pathurl, imageList;
    
    imgborderSelect(imgid);
    if(extensions!='')
    { 
      pathurl = extensions.replace('.'+extensions, '_small.'+extensions);
      imageList = paths;
    } else {
      imageList = paths;
      pathurl = paths.replace('.'+extensions, '_small.'+extensions);
    }
    $('#imgclose').show();
    selectedImageContainer.html('<img src="'+pathurl+'" class="oneselectimggallery" />');
    document.getElementById("featureImage").value = imageList;
  }
}
function imgremove() {
  $('#imagevideoModalCenter').modal('hide');
  $('.oneselectimggallery').remove();
  $('#featureImage').val('');
  $('#selectedImageContainer').html('Image');
  $('#imgclose').hide();
}
function imgborderSelect(imgid)
{
  $('.mediacls img').removeClass("green-border");
  $("#" + imgid).addClass("green-border");
}

// Helper function to initialize TinyMCE for live news editors (both static and dynamic)
function initializeLiveNewsEditor(uniqueId) {
  var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  // Remove any existing instance
  if (uniqueId) {
    tinymce.EditorManager.remove('#' + uniqueId);
  }
  
  tinymce.init({
      selector: uniqueId ? '#' + uniqueId : 'textarea.tinymce-editor-live',
      plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
      imagetools_cors_hosts: ['picsum.photos'],
      menubar: 'file edit view insert format tools table help',
      menubar: false,
      toolbar: 'code | undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | openimage openmedia | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
      toolbar_sticky: true,
      autosave_ask_before_unload: true,
      autosave_interval: '30s',
      autosave_prefix: '{path}{query}-{id}-',
      autosave_restore_when_empty: false,
      autosave_retention: '2m',
      image_advtab: true,
      link_list: [],
      image_list: [],
      image_class_list: [],
      importcss_append: true,
      file_picker_callback: function(callback, value, meta) {
          if (meta.filetype === 'media') {
              $('#videoModalCenters').modal('show');

              $('#videoModalCenters').on('click', 'button.select-media', function() {
                  var mediaUrl = $('#mediaUrlInput').val();
                  callback(mediaUrl);
                  $('#videoModalCenters').modal('hide');
              });
          }
      },
      templates: [],
      template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
      template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
      height: 200,
      image_caption: true,
      quickbars_selection_toolbar: 'bold italic | h1 h2 h3 h4 h5 h6 blockquote quickimage quicktable',
      noneditable_noneditable_class: 'mceNonEditable',
      toolbar_mode: 'sliding',
      contextmenu: 'link image imagetools table',
      skin: useDarkMode ? 'oxide-dark' : 'oxide',
      content_css: useDarkMode ? 'dark' : 'default',
      relative_urls: false,
      remove_script_host: false,
      convert_urls: true,
      setup: function(editor) {
          editor.on('change', function() {
              tinymce.triggerSave();
          });
          // Track focus to ensure activeEditor is set correctly for multiple editors
          editor.on('focus', function() {
              tinymce.activeEditor = editor;
              currentActiveEditorId = editor.id;  // Store in global variable on focus
          });
          
          editor.ui.registry.addIcon('custom-media-icon', '<svg height="24" width="24" ><path d="M4 3h16c.6 0 1 .4 1 1v16c0 .6-.4 1-1 1H4a1 1 0 01-1-1V4c0-.6.4-1 1-1zm1 2v14h14V5H5zm4.8 2.6l5.6 4a.5.5 0 010 .8l-5.6 4A.5.5 0 019 16V8a.5.5 0 01.8-.4z"></path></svg>');
          editor.ui.registry.addButton('openmedia', {
              icon: 'custom-media-icon',
              tooltip: 'Insert media',
              onAction: function() {
                  // IMPORTANT: Must explicitly set for THIS editor
                  activeEditorForModal = editor;
                  activeEditorForModalBackup = editor;
                  currentActiveEditorId = editor.id;
                  $('#videoModalCenters').modal('show');
                  $('#hiddenvideo').val(1);
                  $("#editorvideoid").val(editor.id);
                  console.log('Opened video modal for editor:', editor.id);
              }
          });
          
          editor.ui.registry.addIcon('custom-image-icon', '<svg height="24" width="24"><path d="M4 3h16c.6 0 1 .4 1 1v16c0 .6-.4 1-1 1H4a1 1 0 01-1-1V4c0-.6.4-1 1-1zm1 2v14h14V5H5zm7 3c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2z"></path></svg>');
          editor.ui.registry.addButton('openimage', {
              icon: 'custom-image-icon',
              tooltip: 'Insert image',
              onAction: function() {
                  // IMPORTANT: Must explicitly set for THIS editor
                  activeEditorForModal = editor;
                  activeEditorForModalBackup = editor;
                  currentActiveEditorId = editor.id;
                  $('#imagevideoModalCenter').modal('show');
                  $('#hiddenimage').val(1);
                  $("#editorid").val(editor.id);
                  console.log('Opened image modal for editor:', editor.id);
              }
          });
      },
      content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px } img { max-width: 100%; height: auto; }'
  });
}

function videoSelect(paths,extensions,imgid) {
  var hiddenvideo = $("#hiddenvideo").val();
  var editorid = $("#editorvideoid").val();
  if(hiddenvideo == 1){
    var editor = tinymce.get(editorid);
    if (editor) {
        $('#videoModalCenters').modal('hide');
        var paths, imageList;
        var imgHtml = '<video controls="controls" width="100%" height="350"><source src="'+paths+'" type="video/mp4" /></video>';
        editor.insertContent(imgHtml);
    } else {
        return;
    }
  } else {
    $('#videoModalCenters').modal('hide');
    var selectedImageContainer = $('#selectedVideoContainers');
    var pathurl, imageList; 
    videoborderSelect(imgid);
    if(extensions!='')
    { 
      pathurl = extensions;
      imageList = paths;
    } else {
      imageList = paths;
      pathurl = paths;
    }
    $('#videoclose').show();
    selectedImageContainer.html('<img src="'+pathurl+'" class="oneselectimggallery" />');
    document.getElementById("featureVideo").value = imageList;
    var newId = imgid.replace("photo_", "");

    $.ajax({
      url: "/backend2/mediagallery/getImgTime",
      type: "POST",
      data: {
        id: newId,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if(response.img_time){
          document.getElementById("featureVideoid").value = response.img_time;
        }
      }
    });
  }
}
function videoremove() {
  $('#selectedVideoContainers').modal('hide');
  //$('.oneselectimggallery').remove();
  $('#featureVideo').val('');
  $('#selectedVideoContainers').html('Video');
  $('#videoclose').hide();
}
function videoborderSelect(imgid)
{
  $('.mediacls img').removeClass("green-border");
  $("#" + imgid).addClass("green-border");
}

function toggleDrawer(id) {
  document.querySelectorAll('.drawer').forEach(function (drawer) {
      drawer.style.right = '-300px';
  });
  var drawer = document.getElementById('drawer_'+id);
  if(drawer.style.right === '0px')
  {
    drawer.style.right = '-300px';
  } else {
    drawer.style.right = '0px';
  }
}
function CloseDrawer(id)
{
  var drawer = document.getElementById('drawer_'+id);
  if(drawer.style.right === '0px')
  {
    drawer.style.right = '-300px';
  } else {
    drawer.style.right = '0px';
  }
}

function callAjaxFunction(selectedOption, defaultSubcatID) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
  var domainName = window.location.hostname;
  
  $.ajax({
      url: '//'+domainName+'/backend2/news/subCategory',
      type: 'POST', // or 'GET'
      headers: {
        'X-CSRF-TOKEN': csrfToken,
      },
      data: { option: selectedOption },
      success: function (response) {
        //var responsedata = JSON.parse(response);
        if(response!='') {
          var html = '<option value="">Select SubCategory</option>';
          $.each(response, function(index, item) {
            html +='<option value="'+item.id+'"' + (defaultSubcatID && item.id == defaultSubcatID ? ' selected' : '') + '>'+item.title +' ('+item.category_name_guj+')'+'</option>'
          });
          
          //$('#subcategories').show();
        } else {
           html += '<option value="">Select SubCategory</option>';
          //$('#subcategories').hide();
        }
        $('#subcatID').html(html);
      },
      error: function (error) {
          // Handle AJAX error
          console.error(error);
      }
  });
}
$(document).ready(function(){
  var selectedIds = $("#relatednewsidexist").val().split(','); // Initialize selected IDs as an array
  var domainName1 = window.location.hostname;

  $('#relatednewsid').select2({
      placeholder: 'Select Related News',
      minimumInputLength: 3, // Minimum 3 characters to trigger the search
      ajax: {
          url: '//' + domainName1 + '/backend2/news/searchRelatedNews',
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
              return {
                  results: data
                      .filter(function(item) {
                          // Exclude the selected IDs from the search results
                          return !selectedIds.includes(item.id.toString());
                      })
                      .map(function (item) {
                          return {
                              id: item.id,
                              text: item.title
                          };
                      })
              };
          },
          cache: true
      }
  });
//   $('#relatednewsid').on('select2:select', function (e) {
//     let selectedValue = e.params.data.id;
//     selectedIds.push(selectedValue);

//     // Check if selection exceeds limit
//     if (selectedIds.length > 3) {
//         // Remove the last selected item
//         selectedIds.pop();
//         alert('You can select a maximum of 3 items.');
//         // Remove from Select2 dropdown
//         let selectedItem = $('#relatednewsid').find('option[value="' + selectedValue + '"]');
//         selectedItem.prop('selected', false); // Unselect the option
//         $('#relatednewsid').trigger('change'); // Trigger change to update Select2
//     }
// });


  // Handle the unselect event
  $('#relatednewsid').on('select2:unselect', function(e) {
      var unselectedId = e.params.data.id; // Get the ID of the unselected item
      console.log('Unselected ID:', unselectedId);

      // Remove the unselected ID from the selectedIds array
      selectedIds = selectedIds.filter(function(id) {
          return id !== unselectedId.toString();
      });

      // Update the hidden input or any other element if needed
      $("#relatednewsidexist").val(selectedIds.join(',')); // Update the hidden input with the updated list

      console.log('Updated selected IDs:', selectedIds);
  });
  
  $('#imagevideoModalCenter').on('shown.bs.modal', function (e) {
    loadMorePostNews(1);
  });
  $('#imagevideoModalCenter').on('hidden.bs.modal', function (e) {
   $("#hiddenimage").val(0);
  });
  $('#videoModalCenters').on('shown.bs.modal', function (e) {
    videoloadMorePostNews(1);
  });
  $('#videoModalCenters').on('hidden.bs.modal', function (e) {
    $("#hiddenvideo").val(0);
  });
});
function mediaimgremove(id) {
  if (!confirm('Are you sure you want to delete this image?')) {
      return;
  }
  var domainName = window.location.hostname;
  $.ajax({
      url: '//' + domainName + '/backend2/mediagallery/delete-image/' + id,
      type: 'GET', // Use 'DELETE' method
      success: function (response) {
          $('.mediacls'+id).hide();
      },
      error: function (error) {
          // Handle AJAX error
          console.error('Error:', error);
      }
  });  
}

$('#add-more').click(function () {
    // Generate unique ID for the new textarea
    const uniqueId = 'live-textarea-' + Date.now();
    const fieldGroup = `<input type="hidden" name="live_lid[]" value=""/>
    <div class="field-group mb-3">
    <label for="title">Title</label>
    <input type="text" name="live_titles[]" class="form-control mb-2" placeholder="Enter Title">

    <label for="content">Content</label>
    <textarea name="live_contents[]" id="${uniqueId}" class="tinymce-editor-live form-control" data-editor-type="description" placeholder="Enter Content"></textarea>

    <div class="form-check mt-2">
      <input class="form-check-input create-story-checkbox" type="checkbox" name="live_new_story[]" value="1" id="createStoryCheckbox-${Date.now()}">
      <label class="form-check-label" for="createStoryCheckbox-${Date.now()}">Create a new story</label>
    </div>
  
    <button type="button" class="btn btn-danger delete-field mt-2">Delete</button>
    </div>
    `;
    $('#dynamic-fields').prepend(fieldGroup);
    
    // Use the helper function to initialize the new editor
    initializeLiveNewsEditor(uniqueId);
});

$(document).on('click', '.delete-field', function () {
  $(this).closest('.field-group').remove();
});

$('#is_live_news').change(function () {
  if ($(this).is(':checked')) {
      $('#livenewsdiv').show();
  } else {
      $('#livenewsdiv').hide();
  }
});

$(document).on('change', '.create-story-checkbox', function () {
    if ($(this).is(':checked')) {
        const fieldGroup = $(this).closest('.field-group');
        const title = fieldGroup.find('input[name="live_titles[]"]').val();
        const content = fieldGroup.find('textarea[name="live_contents[]"]').val();
        const catID = $('#catID').val();
        const subcatID = $('#subcatID').val();
        const featureImage = $('#featureImage').val();
        const featureVideo = $('#featureVideo').val();
           
        // Save to localStorage
        localStorage.setItem('storyTitle', title);
        localStorage.setItem('storyContent', content);
        localStorage.setItem('storyContentcatID', catID);
        localStorage.setItem('storyContentsubcatID', subcatID);
        localStorage.setItem('storyfeatureImage', featureImage);
        localStorage.setItem('storyfeatureVideo', featureVideo);
        urls = '//'+window.location.hostname+'/backend2/news/add';
        // Open the new tab
        window.open(urls, '_blank');
    }
});