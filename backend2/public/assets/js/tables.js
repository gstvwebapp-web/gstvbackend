$(document).ready(function() {
    var bsurl = $('meta[name="base-url"]').attr('content');
//   var bsurl = 'http://localhost/gstvnews/backend2';

  function initializeDataTable(tableId, fetchUrl, columns, additionalData = {}, statusType = "Active") {
        let livenews = 0;
        let livenewsct= 0;
        $(`#${tableId}`).DataTable({
            processing: true,   // Show processing indicator
            serverSide: true,   // Enable server-side processing
            lengthMenu: [[25, 50, 100, 150, 200, 500], [25, 50, 100, 150, 200, 500]], // Define dropdown values
            pageLength: 25,
            order: [], // Disable default sorting
            ajax: {
                url: fetchUrl,
                type: 'GET', // HTTP method
                data: function (d) {
                    // Merge additional data for filtering
                    Object.assign(d, additionalData());
                },
                error: function (xhr, error, code) {
                    console.error("AJAX Error:", error);
                    console.error("XHR Response:", xhr.responseText);
                }
            },
            columns: columns,            
            createdRow: function (row, data, dataIndex) {
              	if (data.rowClass) {
                    $(row).addClass(data.rowClass);
                }
                const table = $(`#${tableId}`).DataTable();
                const pageInfo = table.page.info();
                const currentPage = pageInfo.page + 1;
                $(row).attr('data-id', data.id);
                if(data.is_live_news==1)
                {
                    livenews = 1;
                } 
                $(row).attr('data-item-id', data.is_live_news);  
                $(row).attr('data-top', data.topnewsOrder);  
                if (tableId === "newstabledata1" && statusType === "Active") {
                   
                    if (currentPage === 1) { 
                        const catID = $('select[name="catID"]').val();
                        $(row).attr('data-cat-id', catID);  
                        if (livenews == 1) {  
                            // When is_live_news is 1, check if dataIndex is between 1 and 10 (1-based index)
                            if (!catID && data.is_live_news == 1) {
                                livenewsct++;
                                $(row).attr('style', 'background:#fff;');
                            } else if (catID && data.is_live_news == 1) {
                                livenewsct++;
                                $(row).attr('style', 'background:#fff;');
                            } 
                            else if (!catID && data.is_live_news != 1 && dataIndex < (10 + livenewsct)) { 
                                $(row).attr('style', 'background:#dad7d7;');
                            } else if (catID && dataIndex < (5 + livenewsct)) { 
                                $(row).attr('style', 'background:#dad7d7;');
                            }

                        } else { 
                            if (catID && dataIndex < 5) { 
                                $(row).attr('style', 'background:#dad7d7;');
                            } else if (!catID && dataIndex < 10) { 
                                $(row).attr('style', 'background:#dad7d7;');
                            }
                        }
                    }
                } else if (statusType === "video") {
                    const catID = $('select[name="catID"]').val();
                    $(row).attr('data-cat-id', catID);  
                    $(row).attr('data-id', data.id);
                    $(row).attr('data-top', data.topnewsOrder);
                    if (currentPage === 1 && dataIndex < 4) {
                        $(row).attr('style', '');
                    }
                }
            },
            drawCallback: function () {
                // Remove 'odd' and 'even' classes from all rows
                $(`#${tableId} tbody tr`).removeClass('odd even');
            },
            initComplete: function () {
                const tableBody = document.querySelector(`#${tableId} tbody`);

                if (tableBody) {
                    let oldOrder = Array.from(tableBody.children).map(row => row.dataset.id);
                    let entriesPerPage = $(`#${tableId}`).DataTable().page.info().length;
                    let activePageValue = $(`#${tableId}`).DataTable().page.info().page + 1;
                    new Sortable(tableBody, {
                        animation: 150,
                        handle: 'td:first-child',
                        onUpdate: function (evt) {
                            const newOrder = Array.from(tableBody.children).map(row => row.dataset.id);
                            const newliveOrder = Array.from(tableBody.children).map(row => row.dataset.itemId);
                            const catID1 = Array.from(tableBody.children).map(row => row.dataset.catId);
                            let topnewsOrder = $('select[name="catID"]').val();
                            updateOrderInDatabase1(newOrder, tableId, oldOrder, topnewsOrder, activePageValue, entriesPerPage, newliveOrder,catID1);
                        }
                    });
                } else {
                    console.error("tableBody not found for table ID:", tableId);
                }
            },
            ...(tableId != "" && {
                // dom: 'Bfrtip',
                dom: '<"top"lBf>rt<"bottom"ip><"clear">', // Ensure 'l' is included
 
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Export CSV',
                        className: 'btn btn-primary',
                        // bom: true,  // ✅ Ensure UTF-8 BOM is included
                        ...(tableId !== 'usercointabledata' && {
                            exportOptions: {
                                columns: ':not(:last-child)', // Exclude the last column if needed
                                modifier: {
                                    page: 'all'  // ✅ Export ALL pages, not just current page
                                },
                              	format: {
                                    body: function (data, row, column, node) {
                                        // ✅ Strip HTML tags from export data
                                        let cleanData = data;
                                        if (typeof data === 'string') {
                                            cleanData = data
                                                .replace(/<img[^>]*>/g, '') // Remove img tags
                                                .replace(/<br\s*\/?>/gi, ' ') // Replace br with space
                                                .replace(/<[^>]+>/g, '') // Remove all other HTML tags
                                                .replace(/&nbsp;/g, ' ') // Replace nbsp with space
                                                .replace(/&amp;/g, '&') // Decode &amp;
                                                .replace(/&lt;/g, '<') // Decode &lt;
                                                .replace(/&gt;/g, '>') // Decode &gt;
                                                .replace(/&quot;/g, '"') // Decode &quot;
                                                .trim();
                                        }

                                        // Capitalize export ONLY for magazine review table
                                        if (
                                            tableId === 'magazinereviewtabledata' &&
                                            typeof cleanData === 'string'
                                        ) {
                                            return cleanData
                                                .toLowerCase()
                                                .replace(/\b\w/g, function (char) {
                                                    return char.toUpperCase();
                                                });
                                        }

                                        return cleanData;
                                    }
                                }
                            }
                        })
                        // customize: function (csv) {
                        //     return '\ufeff' + csv; // ✅ Add BOM manually
                        // }
                    }
                ]
            })
        });
  
  }

  initializeDataTable(
      'newstabledata1', 
      bsurl + '/news/fetchData/', 
      [
          { data: 'rowNumber' },
          { data: 'title' },
          { data: 'categoryNames' },
          { data: 'viewer' },
          { data: 'newssource', orderable: false },
          { data: 'authorName' },
          { data: 'created_at' },
          { data: 'action', orderable: false, searchable: false }
      ],
      function () {
          return {
              title: $('#title').val(),
              userID: $('select[name="userID"]').val(),
              catID: $('select[name="catID"]').val(),
              start_date: $('input[name="start_date"]').val(),
              end_date: $('input[name="end_date"]').val(),
              newssource: $('select[name="newssource"]').val(),
          };
      },
      'Active'
  );

  initializeDataTable(
      'webstorytabledata', 
      bsurl + '/webstory/webstoryfetchData/', 
      [
          { data: 'rowNumber' },
          { data: 'title' },
          { data: 'viewer' },
          { data: 'authorName' },
          { data: 'created_at' },
          { data: 'action', orderable: false, searchable: false }
      ],
      function () {
          return {
              start_date: document.getElementById('ws_start_date') ? document.getElementById('ws_start_date').value : '',
              end_date:   document.getElementById('ws_end_date')   ? document.getElementById('ws_end_date').value   : ''
          };
      },
      'Active'
  );


initializeDataTable(
      'forntendusertabledata', 
      bsurl + '/frontend-users/frontendusersfetchData/',
      [
          { data: 'rowNumber' },
          { data: 'firstname' },
          { data: 'email' },
          { data: 'mobile' },
          { data: 'total_points' },
          { data: 'spentpoints' },
          { data: 'remain_points' },
          { data: 'status' },
          { data: 'action', orderable: false, searchable: false }
      ],
      function () {
          null
      },
      'Active'
  );

  initializeDataTable(
      'usercointabledata', 
      bsurl + '/users-point/usercoinfetchData/',
      [
          { data: 'rowNumber' },
          { data: 'firstname' },
          { data: 'email' },
          { data: 'mobile' },
          { data: 'total_duration' },
        //  { data: 'total_points' },
        //   { data: 'action', orderable: false, searchable: false }
      ],
        function () {
           return {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            };
        },
      'Active'
  );

  initializeDataTable(
      'paymenttabledata', 
      bsurl + '/payment-history/paymentfetchData/',
      [
            { data: 'rowNumber' },
            { data: 'authorName' },
            { data: 'mobile' },
            { data: 'plan' },
            { data: 'plan_amount' },
            { data: 'plan_order_id' },
            { data: 'plan_payment_id' },
            { data: 'plan_expire' },
            // { data: 'action', orderable: false, searchable: false }
      ],
        function () {
          null
        },
      'Active'
  );


  initializeDataTable(
      'categorytabledata', 
      bsurl + '/category/categoryfetchData/', 
      [
          { data: 'rowNumber' },
          { data: 'icon' },
          { data: 'title' },
          { data: 'parentID' },
          { data: 'author_name' },
          { data: 'action', orderable: false, searchable: false }
      ],
      function () {
          null
      },
      'Active'
  );

  initializeDataTable(
      'pagestabledata', 
      bsurl + '/pages/pagesfetchData/', 
      [
          { data: 'rowNumber' },
          { data: 'title' },
          { data: 'author_name' },
          { data: 'action', orderable: false, searchable: false }
      ],
      function () {
          null
      },
      'Active'
  );
  initializeDataTable(
      'userstabledata', 
      bsurl + '/user/userfetchData/', 
      [
          { data: 'rowNumber' },
          { data: 'name' },
          { data: 'email' },
          { data: 'title' },
          { data: 'today_news_count' },
          { data: 'total_news_count' },
          { data: 'action', orderable: false, searchable: false }
      ],
      function () {
          null
      },
      'Active'
  );
  initializeDataTable(
      'polltabledata', 
      bsurl + '/poll/pollfetchData/', 
      [
          { data: 'rowNumber' },
          { data: 'question' },
          { data: 'result' },
          { data: 'viewer' },
          { data: 'created_at' },
          { data: 'authorName' },
          { data: 'action', orderable: false, searchable: false }
      ],
      function () {
          null
      },
      'Active'
  );
  initializeDataTable(
      'userpollcountfetchData', 
      bsurl + '/poll/userpollcountfetchData/', 
      [
         { data: 'rowNumber' },
          { data: 'mobile' },
          { data: 'authorName' },
          { data: 'total_polls' },
          { data: 'total_coins' },
      ],
      function () {
            return {
                startDate: $('#startDate').val(), // Get the value of the Start Date input
                endDate: $('#endDate').val()     // Get the value of the End Date input
            };
        },
      'Active'
  );
  initializeDataTable(
      'pollusertabledata', 
      bsurl + '/poll/userpollfetchData/', 
      [
          { data: 'rowNumber' },
          { data: 'authorName' },
          { data: 'selectedAnswer' },
          { data: 'mobile' },
          { data: 'created_at' },
      ],
      function () {
            return {
                pollid: $('#pollid').val() 
            };
        },
      'Active'
  );
  initializeDataTable(
        'moduletabledata', 
        bsurl + '/module/modulefetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'title' },
            { data: 'moduleLink' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
  	initializeDataTable(
        'podcastauthortabledata', 
        bsurl + '/podcastauthor/podcastauthorfetchData/', 
        [
          	{ data: 'rowNumber' },
            { data: 'authorname' },
            { data: 'authorengname' },
            { data: 'authorimg' },
            { data: 'author_name' },
            { data: 'viewer' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'viewexporttabledata', 
        bsurl + '/viewexport/viewexportfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'authorName' },
            { data: 'title' },
            { data: 'categoryNames' },
            { data: 'viewer' },
            { data: 'date'}
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'rolestabledata', 
        bsurl + '/roles/rolesfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'title' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'tagstabledata', 
        bsurl + '/tags/tagsfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'title' },
            { data: 'authorName' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'votegametabledata', 
        bsurl + '/votegame/votegamefetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'question' },
            { data: 'authorName' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'generaltabledata', 
        bsurl + '/general/generalfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'question' },
            { data: 'authorName' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'adsimpressiontabledata', 
        bsurl + '/adsimpression/adsimpressionfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'image' },
            { data: 'total_views' },
            { data: 'total_clicks' },
            { data: 'date' }
        ],
        function () {
            return {
                startDate: $('#startDate').val(), // Get the value of the Start Date input
                endDate: $('#endDate').val()     // Get the value of the End Date input
            };
        },
        'Active'
    );
    initializeDataTable(
        'adstabledata', 
        bsurl + '/ads/adsfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'adsdata', render: $.fn.dataTable.render.text() },
            { data: 'created_at' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'adsgraphicstabledata', 
        bsurl + '/graphic/adsgraphicsfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'images'},
            { data: 'title' },
            { data: 'sitelink' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'iframetabledata', 
        bsurl + '/iframe/iframefetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'title' },
            { data: 'url' },
            { data: 'authorName' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'feedbacktabledata', 
        bsurl + '/feedback/feedbackfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'comment' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'electionresultstabledata', 
        bsurl + '/electionresults/electionresultsfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'stateName' },
            { data: 'electiontitle' },
            { data: 'totalseats' },
            { data: 'partyname' },
            { data: 'authorName' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'electionstartabledata', 
        bsurl + '/electionresults/electionstarfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'image' },
            { data: 'title' },
            { data: 'city' },
            { data: 'partytype' },
            { data: 'party_status' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'epapercategorytabledata', 
        bsurl + '/epapercategory/epapercategoryfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'cattype' },
            { data: 'category_name' },
            { data: 'authorName' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'epapertabledata', 
        bsurl + '/epaper/epaperfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'etitle' },
            { data: 'categoryNames' },
            { data: 'viewer' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'gifttabledata', 
        bsurl + '/gift/giftfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'giftimage' },
            { data: 'gifttitle' },
            { data: 'coins' },
            { data: 'qty' },
            { data: 'expiredate' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'giftreedemtabledata', 
        bsurl + '/giftreedem/giftreedemfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'gifttitle' },
            { data: 'giftimage' },
            { data: 'username' },
            { data: 'drawcode' },
            { data: 'spentcoins' },
            { data: 'address' },
            { data: 'mobile' },
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'userActivenews', 
        bsurl + '/usernews/userActivenewsfetchdata/', 
        [
            { data: 'rowNumber' },
            { data: 'featureImage' },
            { data: 'title' },
            { data: 'authorName' },
            { data: 'status' },
            // { data: 'expiredate' },
            // { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'userRejectednews', 
        bsurl + '/usernews/userRejectednewsfetchdata/', 
        [
            { data: 'rowNumber' },
            { data: 'featureImage' },
            { data: 'title' },
            { data: 'authorName' },
            { data: 'rejectnews' },
            { data: 'status' },
            // { data: 'expiredate' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'usernews', 
        bsurl + '/usernews/usernewsfetchdata/', 
        [
            { data: 'rowNumber' },
            { data: 'featureImage' },
            { data: 'title' },
            { data: 'authorName' },
            { data: 'status' },
            // { data: 'expiredate' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'userprofiletabledata', 
        bsurl + '/user/userprofilefetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'title' },
            { data: 'categoryNames' },
            { data: 'viewer' },
            { data: 'created_at' }
        ],
        function () {
            return {
                startDate: $('#userstart_date').val(), // Get the value of the Start Date input
                endDate: $('#userend_date').val(),     // Get the value of the End Date input
                userid: $('#userid').val() 
            };
        },
        'Active'
    );
    initializeDataTable(
        'sidemenutabledata', 
        bsurl + '/sidemenu/sidemenufetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'icon' },
            { data: 'menu' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
  	initializeDataTable(
        'newsflashtabledata', 
        bsurl + '/newsflash/newsflashfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'title' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'languagetabledata', 
        bsurl + '/language/languagefetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'title_eng' },
            { data: 'title_guj' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );

    initializeDataTable(
        'rashitabledata', 
        bsurl + '/rashi/rashifetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'rashiicon' },
            { data: 'title' },
            { data: 'engtitle' },
            { data: 'rashiword' },
            { data: 'author_name' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );

    initializeDataTable(
        'rashidatatabledata', 
        bsurl + '/rashidata/rashidatafetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'rashifal_id' },
            { data: 'authorname' },
            { data: 'authoricon' },
            { data: 'rashidescription' },
            { data: 'author_name' },
            { data: 'created_at' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
  
  	initializeDataTable(
        'podcastdatatabledata', 
        bsurl + '/podcast/podcastfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'authorname' },
            { data: 'podcastTitle' },
            { data: 'audioclipimg' },
            { data: 'authorName' },
            { data: 'viewer' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'crickettabledata', 
        bsurl + '/cricket/cricketfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'series' },
            { data: 'match_time' },
            { data: 'team_a' },
            { data: 'team_b' },
            { data: 'venue' },
            { data: 'match_type' },
            { data: 'date_wise' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
    initializeDataTable(
        'eventtabledata', 
        bsurl + '/events/eventfetchData/', 
        [
            { data: 'rowNumber' },
            // { data: 'firstname' },
            // { data: 'email' },
            // { data: 'mobile' },
            { data: 'event_title' },
            { data: 'event_city' },
            { data: 'event_location' },
            { data: 'event_start_date' },
            { data: 'event_end_date' },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );

    initializeDataTable(
        'careertabledata', 
        bsurl + '/career/careerfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'fullname' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'post' },
            { data: 'experience' },
            { data: 'message' },
            { data: 'cv' },
            { data: 'created_at' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
	
    initializeDataTable(
        'magazinereviewtabledata', 
        bsurl + '/magazinereview/magazinereviewfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'name' },
            { data: 'rating' },
            { data: 'phone' },
            { data: 'city' },
            { data: 'magazine' },
            { data: 'description' },
            { data: 'created_at' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            return {
                magazine: $('#magazineFilter').val(),
                startDate: $('#startDate').val(),
                endDate: $('#endDate').val()
            };
        },
        'Active'
    );


    initializeDataTable(
        'athaitaptabledata', 
        bsurl + '/athaitap/athaitapfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'name' },
            { data: 'days' },
            { data: 'mobile' },
            { data: 'featureImage' },
            { data: 'video' },
            { data: 'created_at' },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );

    initializeDataTable(
        'ganapatiutsavtabledata', 
        bsurl + '/ganapatiutsav/ganapatiutsavfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'name' },
            { data: 'address' },
            { data: 'featureImage' },
            { data: 'mobile' },
            { data: 'created_at' },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
 
    initializeDataTable(
        'gujarattabledata', 
        bsurl + '/marugujarat/marugujaratfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'name' },
            { data: 'mobile' },
            { data: 'title' },
            { data: 'city' },
            { data: 'author_name' },
            { data: 'created_at' },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );

    initializeDataTable(
        'campuscornertabledata', 
        bsurl + '/campuscorner/campuscornerfetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'name' },
            { data: 'title' },
            { data: 'city' },
            { data: 'author_name' },
            { data: 'created_at' },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );

    initializeDataTable(
        'adspricedatatabledata', 
        bsurl + '/adsprice/adspricefetchData/', 
        [
            { data: 'rowNumber' },
            { data: 'ads_position' },
            { data: 'price' },
            { data: 'authorName' },
            { data: 'action', orderable: false, searchable: false }
        ],
        function () {
            null
        },
        'Active'
    );
  
  initializeDataTable(
        'newsordertabledata',
        bsurl + '/newsorderlog/fetch',
        [
            { data: 'rowNumber' },
            { data: 'title' },
            { data: 'view', orderable: false, searchable: false }
        ],
        function () {
            return {
                logdate: $('#logdate').val()
            };
        },
        'Active'
    );
    
});
$('#filterBtncoin').on('click', function () {
    $('#usercointabledata').DataTable().ajax.reload();
});

// Reset button
$('#resetBtncoin').on('click', function () {
    $('#start_date').val('');
    $('#end_date').val('');
    $('#usercointabledata').DataTable().ajax.reload();
});
$('#userstart_date, #userend_date').on('change', function () {
    $('#userprofiletabledata').DataTable().ajax.reload();
});
$('#startDate, #endDate').on('change', function () {
    $('#adsimpressiontabledata').DataTable().ajax.reload();
});
$('#filterBtn').on('click', function () {
    $('#userpollcountfetchData').DataTable().ajax.reload();
});
$('#resetBtn').on('click', function () {
    $('#startDate').val('');
    $('#endDate').val('');
    $('#userpollcountfetchData').DataTable().ajax.reload();
});
$('#filtermagazineBtn').on('click', function () {
    $('#magazinereviewtabledata').DataTable().ajax.reload();
});
$('#resetmagazineBtn').on('click', function () {
    $('#magazineFilter').val('');

    // Clear date inputs
    $('#startDate').val('');
    $('#endDate').val('');

    // Clear global search box (optional but recommended)
    let table = $('#magazinereviewtabledata').DataTable();
    table.search('').columns().search('');

    // Reload table with cleared filters
    table.ajax.reload();
});
function updateOrderInDatabase1(newOrder, tableId, oldOrder, tabletop, activePageValue, entriesPerPage, newliveOrder,catID) {
  
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      var urls = '';
      if(tableId=='categorytabledata') {
        urls = '//'+window.location.hostname+'/backend2/category/catorder';
      } else if(tableId=='newstabledata1') {
        urls = '//'+window.location.hostname+'/backend2/news/newsorder';
      } else if(tableId=='webstorytabledata') {
        urls = '//'+window.location.hostname+'/backend2/webstory/webstoryorder';
      }
      $("#"+tableId+"_processing").show();
      // Make an Ajax request to your Laravel backend
      $.ajax({
        type: 'POST',
        async:false,
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
          newliveOrder: newliveOrder,
          catID:catID
        },
        success: function (response) {
          console.log(response);
          $("#"+tableId+"_processing").hide();
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
var bsurl = $('meta[name="base-url"]').attr('content');
$('#unpublishButton').on('click', function () {
  $("#statustype").val('Inactive');
  $(".publishnewsdata").addClass('btn-secondary').removeClass('btn-success');
  $("#unpublishButton").removeClass('btn-secondary').addClass('btn-success');
    const table = $('#newstabledata1').DataTable();
    table.ajax.url(bsurl+'/news/fetchData?status=Inactive').load();
});
$('#scheduleButton').on('click', function () {
  $("#statustype").val('Schedule');
  $(".publishnewsdata").addClass('btn-secondary').removeClass('btn-success');
  $("#scheduleButton").removeClass('btn-secondary').addClass('btn-success');
    const table = $('#newstabledata1').DataTable();
    table.ajax.url(bsurl+'/news/fetchData?status=Schedule').load();
});
$('#activeButton').on('click', function () {
  $("#statustype").val('video');
  $(".publishnewsdata").addClass('btn-secondary').removeClass('btn-success');
  $("#activeButton").removeClass('btn-secondary').addClass('btn-success');
    const table = $('#newstabledata1').DataTable();
    table.ajax.url(bsurl+'/news/fetchData?status=Active').load();
});
$('#videoButton').on('click', function () {
  $("#selectcatID").val('9');
  $(".publishnewsdata").addClass('btn-secondary').removeClass('btn-success');
  $("#videoButton").removeClass('btn-secondary').addClass('btn-success');
    const table = $('#newstabledata1').DataTable();
    table.ajax.url(bsurl+'/news/fetchData?status=video').load();
});

let rotationDegrees = {}; // store rotation per image

$(document).on('click', '.rotate-btn', function () {
    let id = $(this).data('id');
    let file = $(this).data('file');
    let img = $('#img_' + id);

    // initialize rotation if not exists
    if (!rotationDegrees[id]) rotationDegrees[id] = 0;

    // rotate 90 degrees
    rotationDegrees[id] = (rotationDegrees[id] + 90) % 360;
    img.css('transform', 'rotate(' + rotationDegrees[id] + 'deg)');

    // send to server after rotate
    $.ajax({
        url: bsurl + '/ganapatiutsav/rotateImage',
        type: 'GET',
        data: {
            
            id: id,
            file: file,
            degrees: rotationDegrees[id]
        },
        success: function (res) {
            console.log("Image rotated & saved:", res);
        },
        error: function (xhr) {
            console.log("Error:", xhr.responseText);
        }
    });
});


